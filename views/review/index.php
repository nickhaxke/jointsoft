<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Admin Review Queue</h2>
            <p class="text-sm text-slate-500 mt-1">Review pending sales records and approve or reject uploaded receipts.</p>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Total Records</p>
            <p class="text-xl font-bold text-slate-800"><?= number_format($stats['total_sales']) ?></p>
        </div>
        <div class="bg-amber-50 p-4 rounded-2xl border border-amber-100 shadow-sm relative overflow-hidden">
            <p class="text-xs font-medium text-amber-600 uppercase tracking-wider mb-1">Pending Review</p>
            <p class="text-xl font-bold text-amber-800"><?= number_format($stats['pending_reviews']) ?></p>
            <?php if ($stats['pending_reviews'] > 0): ?>
            <div class="absolute right-0 top-0 h-full w-1 bg-amber-500 animate-pulse"></div>
            <?php endif; ?>
        </div>
        <div class="bg-emerald-50 p-4 rounded-2xl border border-emerald-100 shadow-sm">
            <p class="text-xs font-medium text-emerald-600 uppercase tracking-wider mb-1">Approved</p>
            <p class="text-xl font-bold text-emerald-800"><?= number_format($stats['approved_records']) ?></p>
        </div>
        <div class="bg-red-50 p-4 rounded-2xl border border-red-100 shadow-sm">
            <p class="text-xs font-medium text-red-600 uppercase tracking-wider mb-1">Action Req.</p>
            <p class="text-xl font-bold text-red-800"><?= number_format($stats['rejected_records'] + $stats['missing_receipts']) ?></p>
        </div>
    </div>

    <!-- Filters & Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        
        <!-- Toolbar -->
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="relative max-w-sm w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" id="searchInput" placeholder="Search by name, ID, or amount..." class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white">
            </div>

            <form method="GET" action="<?= url('/review') ?>" class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                <select name="month" onchange="this.form.submit()" class="block w-full sm:w-40 py-2 pl-3 pr-10 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white">
                    <option value="all" <?= $filters['month'] === 'all' ? 'selected' : '' ?>>All Time</option>
                    <option value="current_month" <?= $filters['month'] === 'current_month' ? 'selected' : '' ?>>This Month</option>
                    <option value="last_month" <?= $filters['month'] === 'last_month' ? 'selected' : '' ?>>Last Month</option>
                </select>

                <select name="status" onchange="this.form.submit()" class="block w-full sm:w-40 py-2 pl-3 pr-10 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white">
                    <option value="all" <?= $filters['status'] === 'all' ? 'selected' : '' ?>>All Statuses</option>
                    <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= $filters['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected" <?= $filters['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    <option value="missing_receipt" <?= $filters['status'] === 'missing_receipt' ? 'selected' : '' ?>>No Receipt</option>
                </select>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200" id="recordsTable">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Record / Staff</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Contribution</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    <?php if (empty($records)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-10 h-10 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-sm font-medium">No records found matching your filters.</p>
                                <?php if ($filters['status'] !== 'all' || $filters['month'] !== 'all'): ?>
                                    <a href="<?= url('/review') ?>" class="text-brand-600 hover:text-brand-700 text-xs font-medium mt-2">Clear Filters</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($records as $record): ?>
                        <tr class="hover:bg-slate-50 transition-colors group search-row">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600 shrink-0">
                                        <?= e(mb_strtoupper(mb_substr($record['owner_name'], 0, 1))) ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900 searchable-text"><?= e($record['owner_name']) ?></p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-xs text-slate-500 searchable-text">#<?= str_pad((string)$record['id'], 5, '0', STR_PAD_LEFT) ?></span>
                                            <?php if ($record['receipt_count'] > 0): ?>
                                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Has receipt"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 searchable-text">
                                <?= formatDate($record['created_at']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 searchable-text">
                                <?= formatMoney($record['sale_amount']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-brand-600 font-semibold searchable-text">
                                <?= formatMoney($record['contribution_amount']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?= statusBadge($record['status']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <?php if ($record['status'] === 'pending'): ?>
                                    <a href="<?= url('/sales/' . $record['id']) ?>" class="text-emerald-600 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition-colors inline-flex items-center gap-1">
                                        Review
                                    </a>
                                <?php else: ?>
                                    <a href="<?= url('/sales/' . $record['id']) ?>" class="text-slate-600 hover:text-slate-900 bg-slate-50 hover:bg-slate-100 px-3 py-1.5 rounded-lg transition-colors inline-flex items-center gap-1">
                                        View
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Client-side quick search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const query = this.value.toLowerCase();
        const rows = document.querySelectorAll('.search-row');
        
        rows.forEach(row => {
            let text = '';
            row.querySelectorAll('.searchable-text').forEach(el => {
                text += el.textContent.toLowerCase() + ' ';
            });
            
            if (text.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>
