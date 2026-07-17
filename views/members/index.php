<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Staff Members</h2>
            <p class="text-sm text-slate-500 mt-1">Directory of staff members and their contribution compliance metrics.</p>
        </div>
        <?php if ($isAdmin): ?>
        <a href="<?= url('/settings/users') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-semibold transition-all duration-200 shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            Manage Users
        </a>
        <?php endif; ?>
    </div>

    <!-- Toolbar -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex flex-col sm:flex-row gap-4 justify-between items-center">
        <form method="GET" action="<?= url('/members') ?>" class="relative w-full sm:max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by name or email..." class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white transition-all">
            <?php if (!empty($search)): ?>
            <a href="<?= url('/members') ?>" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Members Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <?php if (empty($members)): ?>
            <div class="col-span-full py-12 flex flex-col items-center justify-center text-center bg-white rounded-2xl border border-slate-100 border-dashed">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <h3 class="text-base font-semibold text-slate-800">No members found</h3>
                <p class="text-sm text-slate-500 mt-1">Try adjusting your search criteria.</p>
            </div>
        <?php else: ?>
            <?php foreach ($members as $member): ?>
                <?php 
                $rate = $member['compliance_rate'];
                $rateColor = $rate >= 90 ? 'text-emerald-600' : ($rate >= 50 ? 'text-amber-500' : 'text-red-500');
                $barColor = $rate >= 90 ? 'bg-emerald-500' : ($rate >= 50 ? 'bg-amber-400' : 'bg-red-500');
                ?>
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden flex flex-col group">
                    <div class="p-6 border-b border-slate-50 flex gap-4 items-start relative">
                        <div class="absolute top-4 right-4">
                            <?php if ($member['is_active']): ?>
                            <span class="inline-flex w-2.5 h-2.5 bg-emerald-500 rounded-full" title="Active Account"></span>
                            <?php else: ?>
                            <span class="inline-flex w-2.5 h-2.5 bg-slate-300 rounded-full" title="Inactive Account"></span>
                            <?php endif; ?>
                        </div>
                        <div class="w-14 h-14 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center text-xl font-bold border-2 border-white shadow-sm shrink-0 group-hover:scale-105 transition-transform">
                            <?= e(mb_strtoupper(mb_substr($member['name'], 0, 1))) ?>
                        </div>
                        <div class="flex-1 min-w-0 pr-6">
                            <h3 class="text-lg font-bold text-slate-900 truncate"><?= e($member['name']) ?></h3>
                            <p class="text-sm text-slate-500 truncate"><?= e($member['email']) ?></p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-600 mt-2 border border-slate-200">
                                <?= ucfirst($member['role']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="p-6 bg-slate-50/50 flex-1 flex flex-col justify-between space-y-5">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-medium text-slate-500 mb-1">Total Sales</p>
                                <p class="text-lg font-bold text-slate-800"><?= number_format($member['total_sales']) ?></p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-500 mb-1">Total Contrib.</p>
                                <p class="text-lg font-bold text-brand-700"><?= formatMoney($member['total_contribution']) ?></p>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-end mb-2">
                                <span class="text-xs font-medium text-slate-500">Compliance Rate</span>
                                <span class="text-lg font-bold <?= $rateColor ?>"><?= $rate ?>%</span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                                <div class="<?= $barColor ?> h-2 rounded-full transition-all duration-1000" style="width: <?= $rate ?>%"></div>
                            </div>
                            <div class="flex justify-between mt-2 text-xs text-slate-500">
                                <span><?= number_format($member['approved_receipts']) ?> Approved</span>
                                <span><?= number_format($member['non_compliant_sales']) ?> Non-compliant</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
