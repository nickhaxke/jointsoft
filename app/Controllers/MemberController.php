<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\User;

/**
 * Member Controller
 * 
 * Handles the display of the staff directory and compliance metrics.
 */
class MemberController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Display the members list with compliance metrics.
     */
    public function index(): void
    {
        Auth::requireAuth();

        // Get search query if any
        $search = $_GET['search'] ?? '';

        $sql = "SELECT u.id, u.name, u.email, u.role, u.is_active, u.last_login_at,
                COUNT(s.id) as total_sales,
                SUM(CASE WHEN s.status = 'approved' THEN 1 ELSE 0 END) as approved_receipts,
                SUM(CASE WHEN s.status = 'missing_receipt' OR s.status = 'rejected' THEN 1 ELSE 0 END) as non_compliant_sales,
                COALESCE(SUM(s.sale_amount), 0) as total_sale_amount,
                COALESCE(SUM(s.contribution_amount), 0) as total_contribution
                FROM users u
                LEFT JOIN sales s ON u.id = s.user_id
                WHERE (u.name LIKE :search1 OR u.email LIKE :search2)
                GROUP BY u.id
                ORDER BY u.name ASC";

        $members = $this->userModel->getDb()->fetchAll($sql, [
            'search1' => "%$search%",
            'search2' => "%$search%"
        ]);

        // Calculate compliance rate for each member
        foreach ($members as &$member) {
            $total = (int) $member['total_sales'];
            $approved = (int) $member['approved_receipts'];
            
            $member['compliance_rate'] = $total > 0 ? round(($approved / $total) * 100) : 100;
        }

        $this->view('members.index', [
            'pageTitle' => 'Staff Members',
            'members' => $members,
            'search' => $search,
            'isAdmin' => Auth::isAdmin()
        ]);
    }
}
