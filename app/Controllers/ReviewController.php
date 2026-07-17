<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Sale;
use App\Models\AuditLog;
use App\Models\Comment;

/**
 * Review Controller
 * 
 * Handles administrative review of all sales records.
 */
class ReviewController extends Controller
{
    private Sale $saleModel;
    private AuditLog $auditLogModel;
    private Comment $commentModel;

    public function __construct()
    {
        $this->saleModel = new Sale();
        $this->auditLogModel = new AuditLog();
        $this->commentModel = new Comment();
    }

    /**
     * Display a list of all records with optional filtering (Admin only).
     */
    public function index(): void
    {
        Auth::requireAuth();
        
        if (!Auth::isAdmin()) {
            $this->sendError(403, 'Unauthorized access.');
            return;
        }

        $statusFilter = $_GET['status'] ?? 'pending';
        $dateFilter = $_GET['month'] ?? 'all';

        // Build the query to fetch all users' records
        $sql = "SELECT s.*, u.name as owner_name, u.email as owner_email,
                (SELECT COUNT(id) FROM receipts WHERE sale_id = s.id) as receipt_count,
                (SELECT COUNT(id) FROM comments WHERE sale_id = s.id) as comment_count
                FROM sales s
                JOIN users u ON s.user_id = u.id
                WHERE 1=1";
        
        $params = [];

        // Apply status filter
        if ($statusFilter !== 'all' && in_array($statusFilter, ['pending', 'approved', 'rejected', 'missing_receipt'])) {
            $sql .= " AND s.status = :status";
            $params['status'] = $statusFilter;
        }

        // Apply date filter
        if ($dateFilter === 'current_month') {
            $sql .= " AND MONTH(s.created_at) = MONTH(CURRENT_DATE()) AND YEAR(s.created_at) = YEAR(CURRENT_DATE())";
        } elseif ($dateFilter === 'last_month') {
            $sql .= " AND MONTH(s.created_at) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(s.created_at) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)";
        }

        $sql .= " ORDER BY s.created_at DESC";

        $records = $this->saleModel->getDb()->fetchAll($sql, $params);

        // Fetch aggregate stats for the header
        $stats = $this->saleModel->getDashboardStats(null); // null = all users

        $this->view('review.index', [
            'pageTitle' => 'Admin Review Queue',
            'records' => $records,
            'stats' => $stats,
            'filters' => [
                'status' => $statusFilter,
                'month' => $dateFilter
            ]
        ]);
    }

    /**
     * Redirect to the sale details view.
     */
    public function show(string $id): void
    {
        $this->redirect('/sales/' . $id);
    }

    /**
     * Approve a sale receipt.
     */
    public function approve(string $id): void
    {
        Auth::requireAuth();
        $this->validateCsrf();

        if (!Auth::isAdmin()) {
            $this->sendError(403, 'Unauthorized access.');
            return;
        }

        $saleId = (int) $id;
        $sale = $this->saleModel->find($saleId);

        if (!$sale) {
            Session::flash('error', 'Record not found.');
            $this->redirect('/review');
            return;
        }

        // Calculate new contribution based on approved rate (3%)
        $commissionRate = 0.03;
        $officeRate = 0.10;
        
        $commissionAmount = $sale['sale_amount'] * $commissionRate;
        $officeAmount = $sale['sale_amount'] * $officeRate;

        try {
            $this->saleModel->getDb()->beginTransaction();

            $this->saleModel->update($saleId, [
                'status' => 'approved',
                'contribution_amount' => $commissionAmount
            ]);
            
            // 3% Reward applied to Campaign Debt
            $db = $this->saleModel->getDb();
            $campaign = $db->fetch(
                "SELECT id, expected_amount, paid_amount FROM member_contributions WHERE user_id = :uid AND status IN ('pending', 'partial') ORDER BY id ASC LIMIT 1",
                ['uid' => $sale['user_id']]
            );

            if ($campaign) {
                $newPaid = $campaign['paid_amount'] + $commissionAmount;
                $status = 'pending';
                if ($newPaid > 0 && $newPaid < $campaign['expected_amount']) {
                    $status = 'partial';
                } elseif ($newPaid >= $campaign['expected_amount']) {
                    $status = 'paid'; // simplify overpaid logic
                }
                
                $db->query(
                    "UPDATE member_contributions SET paid_amount = :paid, status = :status WHERE id = :id",
                    ['paid' => $newPaid, 'status' => $status, 'id' => $campaign['id']]
                );
            }

            $ledger = new \App\Models\Ledger();
            $ledger->recordTransaction(
                $sale['user_id'],
                'receipt_commission',
                "3% Receipt Commission applied to Campaign for Sale #{$saleId}",
                0.00, // debit
                $commissionAmount, // credit
                $saleId,
                Auth::id()
            );

            $this->auditLogModel->create([
                'user_id' => Auth::id(),
                'action' => 'approved_receipt',
                'entity_type' => 'sale',
                'entity_id' => $saleId,
                'details' => "Receipt approved. 3% Reward (TZS {$commissionAmount}) applied to campaign debt."
            ]);

            $this->saleModel->getDb()->commit();
            Session::flash('success', 'Receipt approved and contributions distributed automatically.');
        } catch (\Exception $e) {
            $this->saleModel->getDb()->rollBack();
            Session::flash('error', 'Error approving receipt: ' . $e->getMessage());
        }

        $this->redirect('/sales/' . $saleId);
    }

    /**
     * Reject a sale receipt.
     */
    public function reject(string $id): void
    {
        Auth::requireAuth();
        $this->validateCsrf();

        if (!Auth::isAdmin()) {
            $this->sendError(403, 'Unauthorized access.');
            return;
        }

        $saleId = (int) $id;
        $sale = $this->saleModel->find($saleId);

        if (!$sale) {
            Session::flash('error', 'Record not found.');
            $this->redirect('/review');
            return;
        }

        // Calculate new contribution based on rejected rate (10%)
        $newContribution = $sale['sale_amount'] * 0.10;

        try {
            $this->saleModel->getDb()->beginTransaction();

            $this->saleModel->update($saleId, [
                'status' => 'rejected',
                'contribution_amount' => $newContribution
            ]);

            // 10% Contribution Due (Debt)
            $ledger = new \App\Models\Ledger();
            $ledger->recordTransaction(
                $sale['user_id'],
                'contribution_due',
                "10% Contribution Due for Rejected Receipt #{$saleId}",
                $newContribution, // debit
                0.00, // credit
                $saleId,
                Auth::id()
            );

            $this->auditLogModel->create([
                'user_id' => Auth::id(),
                'action' => 'rejected_receipt',
                'entity_type' => 'sale',
                'entity_id' => $saleId,
                'details' => "Receipt rejected. 10% Contribution (TZS {$newContribution}) added as debt."
            ]);

            $this->saleModel->getDb()->commit();
            Session::flash('success', 'Receipt rejected.');
        } catch (\Exception $e) {
            $this->saleModel->getDb()->rollBack();
            Session::flash('error', 'Error rejecting receipt.');
        }

        $this->redirect('/sales/' . $saleId);
    }

    /**
     * Add a comment to a sale record.
     */
    public function comment(string $id): void
    {
        Auth::requireAuth();
        $this->validateCsrf();

        $saleId = (int) $id;
        $sale = $this->saleModel->find($saleId);

        if (!$sale) {
            $this->sendError(404, 'Record not found.');
            return;
        }

        // Security check: Only admins or the owner can comment
        if (!Auth::isAdmin() && $sale['user_id'] != Auth::id()) {
            $this->sendError(403, 'Unauthorized access.');
            return;
        }

        $rules = [
            'content' => 'required|string'
        ];

        $data = Validator::validate($_POST, $rules);

        if (Validator::hasErrors()) {
            Session::flash('error', 'Comment cannot be empty.');
            $this->redirect('/sales/' . $saleId);
            return;
        }

        $this->commentModel->create([
            'sale_id' => $saleId,
            'user_id' => Auth::id(),
            'content' => $data['content']
        ]);

        Session::flash('success', 'Comment added.');
        $this->redirect('/sales/' . $saleId);
    }
}
