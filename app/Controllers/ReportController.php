<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Sale;

/**
 * Report Controller
 * 
 * Handles generating and exporting reports.
 */
class ReportController extends Controller
{
    private Sale $saleModel;

    public function __construct()
    {
        $this->saleModel = new Sale();
    }

    /**
     * Display the reports dashboard.
     */
    public function index(): void
    {
        Auth::requireAuth();
        if (!Auth::isAdmin()) {
            $this->sendError(403, 'Unauthorized access.');
            return;
        }

        $reports = $this->getReportData();

        $this->view('reports.index', [
            'pageTitle' => 'Financial Reports',
            'monthlyStats' => $reports['monthly'],
            'userStats' => $reports['users'],
            'summary' => $reports['summary']
        ]);
    }

    /**
     * Export report data as Excel (CSV).
     */
    public function exportExcel(): void
    {
        Auth::requireAuth();
        if (!Auth::isAdmin()) {
            $this->sendError(403, 'Unauthorized access.');
            return;
        }

        $reports = $this->getReportData();
        $users = $reports['users'];

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=jointasoft_report_' . date('Y_m_d') . '.csv');

        $output = fopen('php://output', 'w');

        // Headers
        fputcsv($output, ['Staff Member', 'Total Sales (TZS)', 'Contribution (TZS)', 'Compliance Rate (%)', 'Approved Receipts', 'Non-Compliant Records']);

        foreach ($users as $user) {
            $total = (int) $user['total_sales_count'];
            $approved = (int) $user['approved_receipts'];
            $rate = $total > 0 ? round(($approved / $total) * 100) : 100;

            fputcsv($output, [
                $user['name'],
                $user['total_sales'],
                $user['total_contribution'],
                $rate,
                $user['approved_receipts'],
                $user['non_compliant']
            ]);
        }

        // Summary row
        fputcsv($output, []);
        fputcsv($output, ['GRAND TOTALS']);
        fputcsv($output, [
            '--',
            $reports['summary']['total_sales'],
            $reports['summary']['total_contribution'],
            '--',
            $reports['summary']['approved_records'],
            $reports['summary']['rejected_records'] + $reports['summary']['missing_receipts']
        ]);

        fclose($output);
        exit;
    }

    /**
     * Export report data as Printable PDF view.
     */
    public function exportPdf(): void
    {
        Auth::requireAuth();
        if (!Auth::isAdmin()) {
            $this->sendError(403, 'Unauthorized access.');
            return;
        }

        $reports = $this->getReportData();
        
        // Use a clean print-optimized view
        $this->view('reports.print', [
            'pageTitle' => 'JointaSoft System Report',
            'reports' => $reports
        ]);
    }

    /**
     * Helper to fetch aggregated report data.
     */
    private function getReportData(): array
    {
        $db = $this->saleModel->getDb();

        // 1. Monthly aggregation for the current year
        $monthlySql = "SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(id) as sales_count,
            SUM(sale_amount) as total_sales,
            SUM(contribution_amount) as total_contribution
            FROM sales
            WHERE YEAR(created_at) = YEAR(CURRENT_DATE())
            GROUP BY month
            ORDER BY month ASC";
        
        $monthlyStats = $db->fetchAll($monthlySql);

        // 2. User aggregation
        $userSql = "SELECT u.name,
            COUNT(s.id) as total_sales_count,
            COALESCE(SUM(s.sale_amount), 0) as total_sales,
            COALESCE(SUM(s.contribution_amount), 0) as total_contribution,
            SUM(CASE WHEN s.status = 'approved' THEN 1 ELSE 0 END) as approved_receipts,
            SUM(CASE WHEN s.status != 'approved' THEN 1 ELSE 0 END) as non_compliant
            FROM users u
            LEFT JOIN sales s ON u.id = s.user_id
            GROUP BY u.id
            ORDER BY total_contribution DESC";
        
        $userStats = $db->fetchAll($userSql);

        // 3. Overall summary
        $summary = $this->saleModel->getDashboardStats(null);

        return [
            'monthly' => $monthlyStats,
            'users' => $userStats,
            'summary' => $summary
        ];
    }
}
