<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Sale;
use App\Models\AuditLog;
use App\Models\Ledger;
use App\Models\Account;

/**
 * Dashboard Controller
 * 
 * Displays the main dashboard with statistics and recent activity.
 */
class DashboardController extends Controller
{
    private Sale $saleModel;
    private AuditLog $auditLogModel;

    public function __construct()
    {
        $this->saleModel = new Sale();
        $this->auditLogModel = new AuditLog();
    }

    /**
     * Display the dashboard.
     */
    public function index(): void
    {
        Auth::requireAuth();

        $isAdmin = Auth::isAdmin();
        $userId = $isAdmin ? null : Auth::id(); // Admins see all stats, staff see their own
        
        $stats = $this->saleModel->getDashboardStats($userId);
        
        // --- TRANSPARENCY: All users can see recent activities ---
        $recentActivities = $this->auditLogModel->getRecentActivities(5);

        // Calculate potential savings (difference between 10% and 3% on approved sales)
        $savings = 0;
        if ($isAdmin) {
             $savingsSql = "SELECT SUM((sale_amount * 0.10) - contribution_amount) as savings FROM sales WHERE status = 'approved'";
             $savingsResult = $this->saleModel->rawOne($savingsSql);
             $savings = (float)($savingsResult['savings'] ?? 0);
        } else {
             $savingsSql = "SELECT SUM((sale_amount * 0.10) - contribution_amount) as savings FROM sales WHERE status = 'approved' AND user_id = ?";
             $savingsResult = $this->saleModel->rawOne($savingsSql, [Auth::id()]);
             $savings = (float)($savingsResult['savings'] ?? 0);
        }

        // --- NEW V2 METRICS & DEBT BREAKDOWN ---
        $db = $this->saleModel->getDb();
        
        // 1. Ledger Balance for Current User
        $ledgerBalSql = "SELECT running_balance FROM ledgers WHERE user_id = :uid ORDER BY id DESC LIMIT 1";
        $ledgerRes = $db->fetch($ledgerBalSql, ['uid' => Auth::id()]);
        $myLedgerBalance = $ledgerRes ? (float)$ledgerRes['running_balance'] : 0.00;

        // 1b. Debt Breakdown for Current User (OUTSTANDING BALANCES)
        $traDebtDueRes = $db->fetch("SELECT COALESCE(SUM(debit), 0) as total FROM ledgers WHERE user_id = :uid AND transaction_type = 'contribution_due'", ['uid' => Auth::id()]);
        $traDebtPaidRes = $db->fetch("SELECT COALESCE(SUM(credit), 0) as total FROM ledgers WHERE user_id = :uid AND transaction_type = 'receipt_commission'", ['uid' => Auth::id()]);
        $traDebt = max(0, (float)$traDebtDueRes['total'] - (float)$traDebtPaidRes['total']);

        $campaignDebtDueRes = $db->fetch("SELECT COALESCE(SUM(debit), 0) as total FROM ledgers WHERE user_id = :uid AND transaction_type = 'contribution_assigned'", ['uid' => Auth::id()]);
        $campaignDebtPaidRes = $db->fetch("SELECT COALESCE(SUM(credit), 0) as total FROM ledgers WHERE user_id = :uid AND transaction_type = 'contribution_payment'", ['uid' => Auth::id()]);
        $campaignDebt = max(0, (float)$campaignDebtDueRes['total'] - (float)$campaignDebtPaidRes['total']);

        $totalPayments = (float)$traDebtPaidRes['total'] + (float)$campaignDebtPaidRes['total'];

        // 1c. Recent Debts List for the Modal
        $myDebtsSql = "SELECT description, debit, created_at FROM ledgers WHERE user_id = :uid AND debit > 0 ORDER BY id DESC LIMIT 10";
        $myDebtsList = $db->fetchAll($myDebtsSql, ['uid' => Auth::id()]);



        // 3. Active Contribution Campaigns
        $campRes = $db->fetch("SELECT COUNT(*) as cnt FROM contributions WHERE due_date >= CURRENT_DATE()");
        $activeCampaigns = (int)($campRes['cnt'] ?? 0);

        // 4. Office Liquidity (Admin only)
        $totalLiquidity = 0;
        if ($isAdmin) {
            $accountModel = new Account();
            $totalLiquidity = $accountModel->getTotalFunds();
        }

        $this->view('dashboard.index', [
            'pageTitle' => 'Dashboard',
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'savings' => $savings,
            'isAdmin' => $isAdmin,
            'myLedgerBalance' => $myLedgerBalance,
            'traDebt' => $traDebt,
            'campaignDebt' => $campaignDebt,
            'totalPayments' => $totalPayments,
            'myDebtsList' => $myDebtsList,
            'activeCampaigns' => $activeCampaigns,
            'totalLiquidity' => $totalLiquidity
        ]);
    }
}
