<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Report') ?></title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; font-size: 14px; line-height: 1.5; margin: 0; padding: 40px; }
        .header { text-align: center; margin-bottom: 40px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .header h1 { margin: 0 0 10px 0; font-size: 24px; }
        .header p { margin: 0; color: #666; }
        .section { margin-bottom: 40px; }
        .section h2 { font-size: 18px; border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f9f9f9; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary-grid { display: flex; justify-content: space-between; margin-bottom: 40px; }
        .summary-box { flex: 1; border: 1px solid #ddd; padding: 20px; margin: 0 10px; text-align: center; background: #f9f9f9; }
        .summary-box:first-child { margin-left: 0; }
        .summary-box:last-child { margin-right: 0; }
        .summary-value { font-size: 24px; font-weight: bold; margin-top: 10px; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #4f46e5; color: white; border: none; border-radius: 4px; cursor: pointer;">Print Report</button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #ccc; color: #333; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">Close</button>
    </div>

    <div class="header">
        <h1>JointaSoft Financial Report</h1>
        <p>Generated on <?= date('F d, Y h:i A') ?></p>
    </div>

    <div class="summary-grid">
        <div class="summary-box">
            <div>Total Sales</div>
            <div class="summary-value"><?= number_format($reports['summary']['total_sales']) ?></div>
        </div>
        <div class="summary-box">
            <div>Total Contribution</div>
            <div class="summary-value"><?= formatMoney($reports['summary']['total_contribution']) ?></div>
        </div>
        <div class="summary-box">
            <div>Approved Receipts</div>
            <div class="summary-value"><?= number_format($reports['summary']['approved_records']) ?></div>
        </div>
        <div class="summary-box">
            <div>Non-compliant Records</div>
            <div class="summary-value"><?= number_format($reports['summary']['missing_receipts'] + $reports['summary']['rejected_records']) ?></div>
        </div>
    </div>

    <div class="section">
        <h2>Staff Breakdown</h2>
        <table>
            <thead>
                <tr>
                    <th>Staff Member</th>
                    <th class="text-right">Total Records</th>
                    <th class="text-right">Total Sales</th>
                    <th class="text-right">Total Contribution</th>
                    <th class="text-center">Compliance Rate</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reports['users'])): ?>
                <tr>
                    <td colspan="5" class="text-center">No data available.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($reports['users'] as $user): ?>
                    <?php 
                        $total = (int) $user['total_sales_count'];
                        $approved = (int) $user['approved_receipts'];
                        $rate = $total > 0 ? round(($approved / $total) * 100) : 100;
                    ?>
                    <tr>
                        <td><?= e($user['name']) ?></td>
                        <td class="text-right"><?= number_format($total) ?></td>
                        <td class="text-right"><?= formatMoney($user['total_sales']) ?></td>
                        <td class="text-right"><?= formatMoney($user['total_contribution']) ?></td>
                        <td class="text-center"><?= $rate ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div style="text-align: center; color: #999; font-size: 12px; margin-top: 50px;">
        <p>JointaSoft Office Receipt & Contribution Management System</p>
    </div>

</body>
</html>
