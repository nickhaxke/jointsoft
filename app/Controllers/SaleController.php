<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Validator;
use App\Core\FileUpload;
use App\Core\Session;
use App\Models\Sale;
use App\Models\Receipt;
use App\Models\AuditLog;
use App\Models\Comment;

/**
 * Sale Controller
 * 
 * Handles the creation and viewing of sales records.
 */
class SaleController extends Controller
{
    private Sale $saleModel;
    private Receipt $receiptModel;
    private AuditLog $auditLogModel;
    private Comment $commentModel;

    public function __construct()
    {
        $this->saleModel = new Sale();
        $this->receiptModel = new Receipt();
        $this->auditLogModel = new AuditLog();
        $this->commentModel = new Comment();
    }

    /**
     * Show the new sale form.
     */
    public function create(): void
    {
        Auth::requireAuth();

        $this->view('sales.create', [
            'pageTitle' => 'Record New Sale',
        ]);
    }

    /**
     * Store a new sale record.
     */
    public function store(): void
    {
        Auth::requireAuth();
        $this->validateCsrf();

        // 1. Validate input
        $rules = [
            'sale_amount' => 'required|numeric',
            'purchase_amount' => 'numeric',
            'notes' => 'string'
        ];

        $data = Validator::validate($_POST, $rules);

        if (Validator::hasErrors()) {
            Session::flash('error', 'Please correct the errors in the form.');
            $this->redirect('/sales/create');
            return;
        }

        $saleAmount = (float) $data['sale_amount'];
        $purchaseAmount = isset($data['purchase_amount']) && $data['purchase_amount'] !== '' ? (float) $data['purchase_amount'] : 0.0;
        
        // 2. File Upload handling
        $hasReceipt = isset($_FILES['receipt']) && $_FILES['receipt']['error'] !== UPLOAD_ERR_NO_FILE;
        $uploadResult = null;
        
        if ($hasReceipt) {
            $uploader = new FileUpload();
            $uploadResult = $uploader->upload($_FILES['receipt'], 'receipts');
            
            if ($uploadResult === false) {
                Session::flash('error', $uploader->getFirstError());
                $this->redirect('/sales/create');
                return;
            }
        }

        // 3. Contribution Calculation
        // If they upload a receipt, it goes to pending. Rate is 3% eventually, but for now we calculate assuming it might be approved.
        // Actually, the policy says: if approved -> 3%. If missing/rejected -> 10%.
        // Initially, if they upload a receipt, it's pending. If no receipt, it's missing_receipt (10%).
        $status = $hasReceipt ? 'pending' : 'missing_receipt';
        $rate = $hasReceipt ? 0.03 : 0.10;
        $contribution = $saleAmount * $rate;

        // 4. Save Sale to Database
        try {
            $this->saleModel->getDb()->beginTransaction();

            $saleId = $this->saleModel->create([
                'user_id' => Auth::id(),
                'sale_amount' => $saleAmount,
                'purchase_amount' => $purchaseAmount,
                'contribution_amount' => $contribution,
                'status' => $status,
                'notes' => $data['notes'] ?? null
            ]);

            // 5. Save Receipt if uploaded
            if ($hasReceipt && $uploadResult) {
                $this->receiptModel->create([
                    'sale_id' => $saleId,
                    'filename' => $uploadResult,
                    'original_name' => $_FILES['receipt']['name'],
                    'mime_type' => $_FILES['receipt']['type'],
                    'file_size' => $_FILES['receipt']['size']
                ]);
            } elseif (!$hasReceipt) {
                // 10% Contribution Due (Debt) immediately
                $ledger = new \App\Models\Ledger();
                $ledger->recordTransaction(
                    Auth::id(),
                    'contribution_due',
                    "10% Contribution Due for Sale Record #{$saleId} (No Receipt)",
                    $contribution, // debit
                    0.00, // credit
                    (int)$saleId,
                    Auth::id()
                );
            }

            // 6. Audit Log
            $this->auditLogModel->create([
                'user_id' => Auth::id(),
                'action' => 'created_sale',
                'entity_type' => 'sale',
                'entity_id' => $saleId,
                'details' => "Sale recorded for TZS " . number_format($saleAmount, 2) . ($hasReceipt ? " with receipt." : " without receipt.")
            ]);

            $this->saleModel->getDb()->commit();

            Session::flash('success', 'Sale recorded successfully.');
            $this->redirect('/sales/' . $saleId);

        } catch (\Exception $e) {
            $this->saleModel->getDb()->rollBack();
            error_log($e->getMessage());
            Session::flash('error', 'A system error occurred while saving the record.');
            $this->redirect('/sales/create');
        }
    }

    /**
     * Show the sale details.
     */
    public function show(string $id): void
    {
        Auth::requireAuth();
        
        $saleId = (int) $id;
        $sale = $this->saleModel->find($saleId);

        if (!$sale) {
            $this->sendError(404, 'Sale record not found.');
            return;
        }

        // Security check: Only admins or the owner can view
        if (!Auth::isAdmin() && $sale['user_id'] != Auth::id()) {
            $this->sendError(403, 'Unauthorized access.');
            return;
        }

        // Fetch related data
        $receipts = $this->receiptModel->getBySaleId($saleId);
        $comments = $this->commentModel->getBySaleId($saleId);

        // Fetch owner details
        $owner = $this->saleModel->getDb()->fetch("SELECT name FROM users WHERE id = ?", [$sale['user_id']]);
        $sale['owner_name'] = $owner['name'] ?? 'Unknown';

        $this->view('sales.show', [
            'pageTitle' => 'Sale Details #' . $saleId,
            'sale' => $sale,
            'receipts' => $receipts,
            'comments' => $comments,
            'isAdmin' => Auth::isAdmin()
        ]);
    }
}
