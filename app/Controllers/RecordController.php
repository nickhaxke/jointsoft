<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Sale;

/**
 * Record Controller
 * 
 * Handles listing of personal sales records.
 */
class RecordController extends Controller
{
    private Sale $saleModel;
    private \App\Models\Receipt $receiptModel;

    public function __construct()
    {
        $this->saleModel = new Sale();
        $this->receiptModel = new \App\Models\Receipt();
    }

    /**
     * View a receipt file securely.
     */
    public function viewReceipt(string $id): void
    {
        Auth::requireAuth();
        
        $receipt = $this->receiptModel->findOneBy('sale_id', (int) $id);
        if (!$receipt) {
            $this->sendError(404, 'Receipt not found.');
            return;
        }

        // Add receipt to the base upload path
        $filepath = dirname(__DIR__, 2) . '/storage/uploads/' . $receipt['filename'];
        if (!file_exists($filepath)) {
            $this->sendError(404, 'File not found on disk.');
            return;
        }

        // Security check: only allow admins or the sale owner to view
        $sale = $this->saleModel->find($receipt['sale_id']);
        if (!Auth::isAdmin() && $sale['user_id'] !== Auth::id()) {
            $this->sendError(403, 'Unauthorized access to this receipt.');
            return;
        }

        header('Content-Type: ' . $receipt['mime_type']);
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }

    /**
     * Display a list of the user's records with optional filtering.
     */
    public function index(): void
    {
        Auth::requireAuth();

        $userId = Auth::id();
        $statusFilter = $_GET['status'] ?? 'all';
        $dateFilter = $_GET['month'] ?? 'all';

        // Build the query
        $sql = "SELECT s.*, 
                (SELECT COUNT(id) FROM receipts WHERE sale_id = s.id) as receipt_count,
                (SELECT COUNT(id) FROM comments WHERE sale_id = s.id) as comment_count
                FROM sales s
                WHERE s.user_id = :user_id";
        
        $params = ['user_id' => $userId];

        // Apply status filter
        if ($statusFilter !== 'all' && in_array($statusFilter, ['pending', 'approved', 'rejected', 'missing_receipt'])) {
            $sql .= " AND s.status = :status";
            $params['status'] = $statusFilter;
        }

        // Apply date filter (Current month, Last month, etc.)
        if ($dateFilter === 'current_month') {
            $sql .= " AND MONTH(s.created_at) = MONTH(CURRENT_DATE()) AND YEAR(s.created_at) = YEAR(CURRENT_DATE())";
        } elseif ($dateFilter === 'last_month') {
            $sql .= " AND MONTH(s.created_at) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(s.created_at) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)";
        }

        $sql .= " ORDER BY s.created_at DESC";

        $records = $this->saleModel->getDb()->fetchAll($sql, $params);

        // Fetch aggregate stats for the header
        $stats = $this->saleModel->getDashboardStats($userId);

        $this->view('records.index', [
            'pageTitle' => 'My Records',
            'records' => $records,
            'stats' => $stats,
            'filters' => [
                'status' => $statusFilter,
                'month' => $dateFilter
            ]
        ]);
    }
}
