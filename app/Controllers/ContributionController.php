<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Validator;
use App\Core\Session;
use App\Models\Contribution;
use App\Models\MemberContribution;
use App\Models\User;
use App\Models\AuditLog;

/**
 * Contribution Controller
 * 
 * Manages office-wide monthly contributions.
 */
class ContributionController extends Controller
{
    private Contribution $contributionModel;
    private MemberContribution $memberContribModel;
    private User $userModel;
    private AuditLog $auditLogModel;

    public function __construct()
    {
        $this->contributionModel = new Contribution();
        $this->memberContribModel = new MemberContribution();
        $this->userModel = new User();
        $this->auditLogModel = new AuditLog();
    }

    /**
     * List all contributions.
     */
    public function index(): void
    {
        Auth::requireAuth();

        $sql = "SELECT c.*, u.name as created_by_name,
                (SELECT COUNT(*) FROM member_contributions WHERE contribution_id = c.id) as total_members,
                (SELECT SUM(paid_amount) FROM member_contributions WHERE contribution_id = c.id) as total_collected
                FROM contributions c
                JOIN users u ON c.created_by_id = u.id
                ORDER BY c.due_date DESC";
                
        $contributions = $this->contributionModel->getDb()->fetchAll($sql);
        $stats = $this->contributionModel->getSummaryStats();

        // Also get my personal contributions
        $mySql = "SELECT mc.*, c.title, c.due_date 
                  FROM member_contributions mc
                  JOIN contributions c ON mc.contribution_id = c.id
                  WHERE mc.user_id = :user_id
                  ORDER BY c.due_date DESC";
        $myContributions = $this->memberContribModel->getDb()->fetchAll($mySql, ['user_id' => Auth::id()]);
        $myStats = $this->memberContribModel->getMemberStats(Auth::id());

        $pendingPayments = [];
        if (Auth::isAdmin()) {
            $paymentModel = new \App\Models\ContributionPayment();
            $pendingPayments = $paymentModel->getAllPending();
        }

        $this->view('contributions.index', [
            'pageTitle' => 'Monthly Contributions',
            'contributions' => $contributions,
            'stats' => $stats,
            'myContributions' => $myContributions,
            'myStats' => $myStats,
            'pendingPayments' => $pendingPayments,
            'isAdmin' => Auth::isAdmin()
        ]);
    }

    /**
     * Show form to create a new contribution campaign.
     */
    public function create(): void
    {
        Auth::requireAuth();
        if (!Auth::isAdmin()) {
            $this->sendError(403, 'Unauthorized. Only admins can create contributions.');
            return;
        }

        $this->view('contributions.create', [
            'pageTitle' => 'Create Contribution Campaign'
        ]);
    }

    /**
     * Store new contribution and auto-assign to all active members.
     */
    public function store(): void
    {
        Auth::requireAuth();
        if (!Auth::isAdmin()) {
            $this->sendError(403, 'Unauthorized.');
            return;
        }
        
        $this->validateCsrf();

        $rules = [
            'title' => 'required',
            'total_expected_amount' => 'required|numeric|min:1',
            'due_date' => 'required'
        ];

        $data = Validator::validate($_POST, $rules);

        if (Validator::hasErrors()) {
            Session::flash('error', 'Please check the form for errors.');
            $this->redirect('/contributions/create');
            return;
        }

        $db = $this->contributionModel->getDb();
        try {
            $db->beginTransaction();

            // 1. Create the campaign
            $contribId = $this->contributionModel->create([
                'title' => $data['title'],
                'description' => $_POST['description'] ?? null,
                'total_expected_amount' => $data['total_expected_amount'],
                'due_date' => $data['due_date'],
                'created_by_id' => Auth::id()
            ]);

            // 2. Fetch active members
            $activeMembers = $this->userModel->findBy('is_active', 1, true);
            
            if (empty($activeMembers)) {
                throw new \Exception("No active members found to assign this contribution.");
            }

            // 3. Calculate split amount
            $splitAmount = round((float)$data['total_expected_amount'] / count($activeMembers), 2);

            // 4. Assign to every member and DEBIT their Ledger
            $ledger = new \App\Models\Ledger();
            foreach ($activeMembers as $member) {
                $mcId = $this->memberContribModel->create([
                    'contribution_id' => $contribId,
                    'user_id' => $member['id'],
                    'expected_amount' => $splitAmount,
                    'paid_amount' => 0.00,
                    'status' => 'pending'
                ]);
                
                // DEBIT the member's Ledger so they officially owe this amount
                $ledger->recordTransaction(
                    $member['id'],
                    'contribution_assigned',
                    "Assigned Contribution: {$data['title']}",
                    $splitAmount, // debit (increases their debt)
                    0.00, // credit
                    $mcId,
                    Auth::id()
                );
            }

            // 5. Audit Log
            $this->auditLogModel->create([
                'user_id' => Auth::id(),
                'action' => 'created_contribution',
                'entity_type' => 'contribution',
                'entity_id' => $contribId,
                'details' => "Created contribution: {$data['title']} for TZS " . number_format((float)$data['total_expected_amount'], 2) . " split among " . count($activeMembers) . " members."
            ]);

            $db->commit();
            Session::flash('success', 'Contribution campaign created and assigned to all members successfully!');
            $this->redirect('/contributions');

        } catch (\Exception $e) {
            $db->rollback();
            Session::flash('error', 'Failed to create contribution: ' . $e->getMessage());
            $this->redirect('/contributions/create');
        }
    }

    /**
     * Show contribution details and member payment status.
     */
    public function show(string $id): void
    {
        Auth::requireAuth();

        $campaign = $this->contributionModel->find($id);
        if (!$campaign) {
            $this->sendError(404, 'Campaign not found.');
            return;
        }

        $sql = "SELECT mc.*, u.name as member_name, u.email as member_email
                FROM member_contributions mc
                JOIN users u ON mc.user_id = u.id
                WHERE mc.contribution_id = :cid
                ORDER BY u.name ASC";
        
        $members = $this->memberContribModel->getDb()->fetchAll($sql, ['cid' => $id]);

        $this->view('contributions.show', [
            'pageTitle' => 'Contribution Details',
            'campaign' => $campaign,
            'members' => $members,
            'isAdmin' => Auth::isAdmin()
        ]);
    }

    /**
     * Update a member's paid amount for a contribution.
     */
    public function updatePayment(string $mcId): void
    {
        Auth::requireAuth();
        if (!Auth::isAdmin()) {
            $this->sendError(403, 'Unauthorized.');
            return;
        }

        $this->validateCsrf();

        $record = $this->memberContribModel->find($mcId);
        if (!$record) {
            $this->sendError(404, 'Record not found.');
            return;
        }

        $paidAmount = (float)($_POST['paid_amount'] ?? 0);
        
        $status = 'pending';
        if ($paidAmount > 0 && $paidAmount < $record['expected_amount']) {
            $status = 'partial';
        } elseif ($paidAmount == $record['expected_amount']) {
            $status = 'paid';
        } elseif ($paidAmount > $record['expected_amount']) {
            $status = 'overpaid';
        }

        $this->memberContribModel->update($mcId, [
            'paid_amount' => $paidAmount,
            'status' => $status
        ]);

        $this->auditLogModel->create([
            'user_id' => Auth::id(),
            'action' => 'updated_contribution_payment',
            'entity_type' => 'member_contribution',
            'entity_id' => $mcId,
            'details' => "Updated payment for member ID {$record['user_id']} on contribution {$record['contribution_id']} to TZS {$paidAmount}"
        ]);

        Session::flash('success', 'Payment recorded successfully.');
        $this->redirect('/contributions/' . $record['contribution_id']);
    }

    /**
     * Member submits a payment for their contribution.
     */
    public function submitPayment(string $mcId): void
    {
        Auth::requireAuth();
        $this->validateCsrf();

        $record = $this->memberContribModel->find((int)$mcId);
        if (!$record) {
            $this->sendError(404, 'Record not found.');
            return;
        }

        // Only the owner can submit payment (or admin, but usually owner)
        if ($record['user_id'] != Auth::id() && !Auth::isAdmin()) {
            $this->sendError(403, 'Unauthorized.');
            return;
        }

        $rules = [
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required',
            'reference_code' => 'required'
        ];

        $data = Validator::validate($_POST, $rules);

        if (Validator::hasErrors()) {
            Session::flash('error', 'Please fill all required fields correctly.');
            $this->redirect('/contributions/' . $record['contribution_id']);
            return;
        }

        $paymentModel = new \App\Models\ContributionPayment();
        $paymentModel->create([
            'member_contribution_id' => $record['id'],
            'user_id' => Auth::id(),
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
            'reference_code' => $data['reference_code'],
            'status' => 'pending'
        ]);

        $this->auditLogModel->create([
            'user_id' => Auth::id(),
            'action' => 'submitted_contribution_payment',
            'entity_type' => 'contribution_payment',
            'entity_id' => $record['id'],
            'details' => "Member submitted a payment of TZS {$data['amount']} via {$data['payment_method']} (Ref: {$data['reference_code']}) for review."
        ]);

        Session::flash('success', 'Payment submitted successfully and is pending Admin approval.');
        $this->redirect('/contributions/' . $record['contribution_id']);
    }

    /**
     * Admin approves or rejects a pending payment.
     */
    public function approvePayment(string $paymentId): void
    {
        Auth::requireAuth();
        if (!Auth::isAdmin()) {
            $this->sendError(403, 'Unauthorized.');
            return;
        }
        $this->validateCsrf();

        $paymentModel = new \App\Models\ContributionPayment();
        $payment = $paymentModel->find((int)$paymentId);
        if (!$payment || $payment['status'] !== 'pending') {
            Session::flash('error', 'Payment not found or already processed.');
            $this->redirect('/contributions');
            return;
        }

        $action = $_POST['action'] ?? '';
        
        $db = $paymentModel->getDb();
        try {
            $db->beginTransaction();

            if ($action === 'reject') {
                $paymentModel->update($payment['id'], [
                    'status' => 'rejected',
                    'reviewed_by_id' => Auth::id(),
                    'reviewed_at' => date('Y-m-d H:i:s')
                ]);
                $this->auditLogModel->create([
                    'user_id' => Auth::id(), 'action' => 'rejected_contribution_payment',
                    'entity_type' => 'contribution_payment', 'entity_id' => $payment['id'],
                    'details' => "Rejected payment of TZS {$payment['amount']}."
                ]);
                Session::flash('success', 'Payment rejected successfully.');
            } elseif ($action === 'approve') {
                $accountId = $_POST['account_id'] ?? null;
                if (!$accountId) {
                    throw new \Exception('Please select an Office Account to receive the funds.');
                }

                // 1. Update Payment Status
                $paymentModel->update($payment['id'], [
                    'status' => 'approved',
                    'reviewed_by_id' => Auth::id(),
                    'reviewed_at' => date('Y-m-d H:i:s')
                ]);

                // 2. Update Member Contribution record
                $record = $this->memberContribModel->find((int)$payment['member_contribution_id']);
                $newPaidAmount = $record['paid_amount'] + $payment['amount'];
                
                $status = 'pending';
                if ($newPaidAmount > 0 && $newPaidAmount < $record['expected_amount']) $status = 'partial';
                elseif ($newPaidAmount == $record['expected_amount']) $status = 'paid';
                elseif ($newPaidAmount > $record['expected_amount']) $status = 'overpaid';

                $this->memberContribModel->update($record['id'], [
                    'paid_amount' => $newPaidAmount,
                    'status' => $status
                ]);

                // 3. CREDIT the Ledger (Reduce debt)
                $ledger = new \App\Models\Ledger();
                $ledger->recordTransaction(
                    $record['user_id'],
                    'contribution_payment',
                    "Approved payment for Contribution (Ref: {$payment['reference_code']})",
                    0.00, // debit
                    (float)$payment['amount'], // credit
                    $payment['id'],
                    Auth::id()
                );

                // 4. Deposit into Office Account
                $transactionModel = new \App\Models\AccountTransaction();
                $transactionModel->recordTransaction(
                    (int)$accountId,
                    'deposit',
                    (float)$payment['amount'],
                    "Contribution Payment Received (User ID: {$record['user_id']})",
                    Auth::id()
                );

                $this->auditLogModel->create([
                    'user_id' => Auth::id(), 'action' => 'approved_contribution_payment',
                    'entity_type' => 'contribution_payment', 'entity_id' => $payment['id'],
                    'details' => "Approved payment of TZS {$payment['amount']}."
                ]);
                Session::flash('success', 'Payment approved! Ledger updated and funds deposited to Office Account.');
            } else {
                throw new \Exception('Invalid action.');
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            Session::flash('error', $e->getMessage());
        }

        $this->redirect('/contributions');
    }
}
