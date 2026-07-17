<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Financial Reports</h2>
            <p class="text-sm text-slate-500 mt-1">System-wide overview of sales and contributions.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= url('/reports/export/excel') ?>" class="inline-flex justify-center items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-sm font-semibold transition-colors shadow-sm">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                Export CSV
            </a>
            <a href="<?= url('/reports/export/pdf') ?>" target="_blank" class="inline-flex justify-center items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print / PDF
            </a>
        </div>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Sales</p>
            <p class="text-2xl font-bold text-slate-800"><?= number_format($summary['total_sales']) ?></p>
        </div>
        <div class="bg-brand-50 p-5 rounded-2xl border border-brand-100 shadow-sm">
            <p class="text-xs font-semibold text-brand-600 uppercase tracking-wider mb-1">Total Contribution</p>
            <p class="text-2xl font-bold text-brand-800"><?= formatMoney($summary['total_contribution']) ?></p>
        </div>
        <div class="bg-emerald-50 p-5 rounded-2xl border border-emerald-100 shadow-sm">
            <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-1">Approved Receipts</p>
            <p class="text-2xl font-bold text-emerald-800"><?= number_format($summary['approved_records']) ?></p>
        </div>
        <div class="bg-red-50 p-5 rounded-2xl border border-red-100 shadow-sm">
            <p class="text-xs font-semibold text-red-600 uppercase tracking-wider mb-1">Non-compliant</p>
            <p class="text-2xl font-bold text-red-800"><?= number_format($summary['missing_receipts'] + $summary['rejected_records']) ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Monthly Chart Placeholder -->
        <div class="lg:col-span-1 bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex flex-col">
            <h3 class="text-base font-semibold text-slate-800 mb-4">Monthly Trends (This Year)</h3>
            <?php if (empty($monthlyStats)): ?>
                <div class="flex-1 flex flex-col items-center justify-center text-slate-400">
                    <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                    <p class="text-sm">No data for chart</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($monthlyStats as $stat): 
                        // simple visual bar
                        $max = max(array_column($monthlyStats, 'total_contribution'));
                        $percent = $max > 0 ? ($stat['total_contribution'] / $max) * 100 : 0;
                    ?>
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="font-medium text-slate-700"><?= date('F Y', strtotime($stat['month'] . '-01')) ?></span>
                            <span class="text-brand-600 font-semibold"><?= formatMoney($stat['total_contribution']) ?></span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="bg-brand-500 h-2 rounded-full" style="width: <?= $percent ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Staff Breakdown -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-base font-semibold text-slate-800">Staff Contribution Breakdown</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Staff Member</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Records</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Sales</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Contribution</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Compliance</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-100">
                        <?php if (empty($userStats)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500 text-sm">No data available.</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($userStats as $user): ?>
                                <?php 
                                $total = (int) $user['total_sales_count'];
                                $approved = (int) $user['approved_receipts'];
                                $rate = $total > 0 ? round(($approved / $total) * 100) : 100;
                                $rateColor = $rate >= 90 ? 'text-emerald-600' : ($rate >= 50 ? 'text-amber-500' : 'text-red-500');
                                ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900"><?= e($user['name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 text-right"><?= number_format($total) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-700 text-right"><?= formatMoney($user['total_sales']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-brand-600 text-right"><?= formatMoney($user['total_contribution']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="font-semibold <?= $rateColor ?>"><?= $rate ?>%</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
