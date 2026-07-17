<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Validator;
use App\Core\Session;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\AuditLog;

/**
 * Fund Controller
 * 
 * Manages the physical office funds and accounts.
 */
class FundController extends Controller
{
    private Account $accountModel;
    private AccountTransaction $transactionModel;
    private AuditLog $auditLogModel;

    public function __construct()
    {
        $this->accountModel = new Account();
        $this->transactionModel = new AccountTransaction();
        $this->auditLogModel = new AuditLog();
    }

    /**
     * Display office funds dashboard.
     */
    public function index(): void
    {
        Auth::requireAuth();
        // Admin only for fund management
        if (!Auth::isAdmin()) {
            $this->sendError(403, 'Unauthorized access.');
            return;
        }

        $accounts = $this->accountModel->all();
        
        $sql = "SELECT t.*, a.name as account_name, u.name as created_by_name
                FROM account_transactions t
                JOIN accounts a ON t.account_id = a.id
                LEFT JOIN users u ON t.created_by_id = u.id
                ORDER BY t.created_at DESC LIMIT 50";
                
        $recentTransactions = $this->transactionModel->getDb()->fetchAll($sql);
        
        $userModel = new \App\Models\User();
        $members = $userModel->findBy('is_active', 1, true);
        
        $this->view('funds.index', [
            'pageTitle' => 'Office Funds',
            'accounts' => $accounts,
            'recentTransactions' => $recentTransactions,
            'totalFunds' => $this->accountModel->getTotalFunds(),
            'members' => $members
        ]);
    }

    /**
     * Process a manual fund transaction (deposit/withdrawal/transfer).
     */
    public function process(): void
    {
        Auth::requireAuth();
        if (!Auth::isAdmin()) {
            $this->sendError(403, 'Unauthorized access.');
            return;
        }

        $this->validateCsrf();

        $action = $_POST['action'] ?? '';
        $amount = (float)($_POST['amount'] ?? 0);
        $description = $_POST['description'] ?? 'Manual adjustment';
        $accountId = (int)($_POST['account_id'] ?? 0);

        if ($amount <= 0) {
            Session::flash('error', 'Amount must be greater than zero.');
            $this->redirect('/funds');
            return;
        }

        try {
            if ($action === 'deposit' || $action === 'withdrawal') {
                if (!$accountId) throw new \Exception('Please select an account.');
                
                $this->transactionModel->recordTransaction(
                    $accountId,
                    $action,
                    $amount,
                    $description,
                    null,
                    Auth::id()
                );
                
                $this->auditLogModel->create([
                    'user_id' => Auth::id(),
                    'action' => 'fund_' . $action,
                    'entity_type' => 'account',
                    'entity_id' => $accountId,
                    'details' => ucfirst($action) . " TZS {$amount} - {$description}"
                ]);
            } elseif ($action === 'transfer') {
                $fromId = (int)($_POST['from_account_id'] ?? 0);
                $toId = (int)($_POST['to_account_id'] ?? 0);
                
                if (!$fromId || !$toId || $fromId === $toId) {
                    throw new \Exception('Invalid transfer accounts selected.');
                }
                
                $db = $this->transactionModel->getDb();
                $db->beginTransaction();
                
                // Withdrawal from source
                $this->transactionModel->recordTransaction(
                    $fromId,
                    'withdrawal',
                    $amount,
                    'Transfer to Account ID ' . $toId . ' - ' . $description,
                    null,
                    Auth::id()
                );
                
                // Deposit to destination
                $this->transactionModel->recordTransaction(
                    $toId,
                    'deposit',
                    $amount,
                    'Transfer from Account ID ' . $fromId . ' - ' . $description,
                    null,
                    Auth::id()
                );
                
                $db->commit();
                
                $this->auditLogModel->create([
                    'user_id' => Auth::id(),
                    'action' => 'fund_transfer',
                    'entity_type' => 'account',
                    'entity_id' => $fromId,
                    'details' => "Transferred TZS {$amount} to Account ID {$toId} - {$description}"
                ]);
            } elseif ($action === 'reimburse') {
                // MODULE 5: ADVANCES - Reimburse member
                $userId = (int)($_POST['user_id'] ?? 0);
                if (!$accountId || !$userId) {
                    throw new \Exception('Please select both an account and a member to reimburse.');
                }
                
                $db = $this->transactionModel->getDb();
                $db->beginTransaction();
                
                // 1. Withdraw from office fund
                $this->transactionModel->recordTransaction(
                    $accountId,
                    'withdrawal',
                    $amount,
                    'Member Reimbursement - ' . $description,
                    null,
                    Auth::id()
                );
                
                // 2. Debit member ledger (so balance approaches zero)
                $ledger = new \App\Models\Ledger();
                $ledger->recordTransaction(
                    $userId,
                    'refund', // Member receives cash, debits their account
                    'Reimbursement Received: ' . $description,
                    $amount, // debit
                    0.00, // credit
                    null,
                    Auth::id()
                );
                
                $db->commit();
                
                $this->auditLogModel->create([
                    'user_id' => Auth::id(),
                    'action' => 'fund_reimburse',
                    'entity_type' => 'account',
                    'entity_id' => $accountId,
                    'details' => "Reimbursed Member ID {$userId} TZS {$amount} from Account ID {$accountId}"
                ]);
            } else {
                throw new \Exception('Invalid action.');
            }
            
            Session::flash('success', 'Transaction processed successfully.');
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
        }

        $this->redirect('/funds');
    }
}
