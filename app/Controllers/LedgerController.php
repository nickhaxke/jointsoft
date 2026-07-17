<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Ledger;
use App\Models\User;

/**
 * Ledger Controller
 * 
 * Manages the display of member ledgers (statements).
 */
class LedgerController extends Controller
{
    private Ledger $ledgerModel;
    private User $userModel;

    public function __construct()
    {
        $this->ledgerModel = new Ledger();
        $this->userModel = new User();
    }

    /**
     * Display the authenticated user's own ledger statement.
     */
    public function index(): void
    {
        Auth::requireAuth();
        $this->showLedger(Auth::id());
    }

    /**
     * Display a specific member's ledger (Admin only).
     */
    public function show(string $userId): void
    {
        Auth::requireAuth();
        if (!Auth::isAdmin() && (int)$userId !== Auth::id()) {
            $this->sendError(403, 'Unauthorized access to this ledger.');
            return;
        }
        $this->showLedger((int)$userId);
    }

    /**
     * Helper to render a ledger view.
     */
    private function showLedger(int $userId): void
    {
        $user = $this->userModel->find($userId);
        if (!$user) {
            $this->sendError(404, 'Member not found.');
            return;
        }

        $sql = "SELECT l.*, c.name as created_by_name
                FROM ledgers l
                LEFT JOIN users c ON l.created_by_id = c.id
                WHERE l.user_id = :uid
                ORDER BY l.created_at ASC, l.id ASC";
                
        $transactions = $this->ledgerModel->getDb()->fetchAll($sql, ['uid' => $userId]);

        $this->view('ledgers.show', [
            'pageTitle' => 'Member Statement',
            'member' => $user,
            'transactions' => $transactions,
            'isAdmin' => Auth::isAdmin()
        ]);
    }
}
