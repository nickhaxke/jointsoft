<div class="space-y-6 lg:space-y-8 animate-fade-in">
    <!-- Hero / Welcome Section -->
    <div class="relative bg-slate-900 rounded-3xl p-6 sm:p-10 text-white shadow-xl overflow-hidden group">
        <!-- Abstract Background Elements -->
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-72 h-72 rounded-full bg-brand-500/20 blur-3xl group-hover:bg-brand-500/30 transition-all duration-700"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 rounded-full bg-emerald-500/20 blur-3xl group-hover:bg-emerald-500/30 transition-all duration-700"></div>
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wNSkiLz48L3N2Zz4=')] opacity-50"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/10 backdrop-blur-md mb-4 text-xs font-semibold uppercase tracking-wider text-brand-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <?= $isAdmin ? 'Admin Console' : 'Member Portal' ?>
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-2">Welcome back, <?= e(auth()['name'] ?? 'User') ?> 👋</h1>
                <p class="text-slate-300 text-sm sm:text-base max-w-lg">
                    Manage your office finances, track contributions, and stay on top of your financial health.
                </p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <a href="<?= url('/sales/create') ?>" class="inline-flex justify-center items-center gap-2 px-6 py-3 bg-brand-500 hover:bg-brand-400 text-white rounded-xl text-sm font-bold transition-all duration-300 shadow-lg shadow-brand-500/25 hover:shadow-brand-500/40 hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Receipt
                </a>
                <?php if (!$isAdmin): ?>
                <a href="<?= url('/ledger') ?>" class="inline-flex justify-center items-center gap-2 px-6 py-3 bg-white/10 hover:bg-white/20 text-white backdrop-blur-md border border-white/10 rounded-xl text-sm font-bold transition-all duration-300 hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    My Ledger
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Financial Pulse -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        
        <?php if ($isAdmin): ?>
            <!-- Admin: Total Liquidity -->
            <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-6 text-white shadow-lg shadow-emerald-500/20 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -mr-10 -mt-10"></div>
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-4 opacity-90">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="font-semibold text-sm uppercase tracking-wider">Office Liquidity</h3>
                    </div>
                    <p class="text-3xl font-bold tracking-tight"><?= formatMoney($totalLiquidity) ?></p>
                    <a href="<?= url('/funds') ?>" class="inline-block mt-4 text-sm font-medium text-white/80 hover:text-white flex items-center gap-1 group-hover:gap-2 transition-all">
                        Manage Funds <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Member: Personal Ledger Balance -->
            <div x-data="{ showDebtModal: false }" class="bg-gradient-to-br <?= $myLedgerBalance > 0 ? 'from-red-500 to-rose-600 shadow-red-500/20' : ($myLedgerBalance < 0 ? 'from-emerald-500 to-emerald-600 shadow-emerald-500/20' : 'from-slate-700 to-slate-800 shadow-slate-500/20') ?> rounded-2xl p-6 text-white shadow-lg relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -mr-10 -mt-10"></div>
                <div class="relative z-10 flex justify-between items-start">
                    <div>
                        <div class="flex items-center gap-3 mb-4 opacity-90">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </div>
                            <h3 class="font-semibold text-sm uppercase tracking-wider">My Ledger Balance</h3>
                        </div>
                        <?php if ($myLedgerBalance > 0): ?>
                            <p class="text-xs text-white/80 mb-1 uppercase font-semibold">You Owe</p>
                            <p class="text-3xl font-bold tracking-tight"><?= formatMoney($myLedgerBalance) ?></p>
                        <?php elseif ($myLedgerBalance < 0): ?>
                            <p class="text-xs text-white/80 mb-1 uppercase font-semibold">Office Owes You</p>
                            <p class="text-3xl font-bold tracking-tight"><?= formatMoney(abs($myLedgerBalance)) ?></p>
                        <?php else: ?>
                            <p class="text-xs text-white/80 mb-1 uppercase font-semibold">Status</p>
                            <p class="text-3xl font-bold tracking-tight">Cleared</p>
                        <?php endif; ?>
                    </div>
                    <?php if ($myLedgerBalance > 0 || $traDebt > 0 || $campaignDebt > 0): ?>
                    <button @click="showDebtModal = true" class="bg-white/20 hover:bg-white/30 transition-colors px-3 py-1.5 rounded-lg text-xs font-semibold backdrop-blur-sm flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Breakdown
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Debt Modal -->
                <template x-teleport="body">
                    <div x-show="showDebtModal" class="fixed inset-0 z-[100] overflow-y-auto" style="display:none">
                        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                            <div x-show="showDebtModal" @click="showDebtModal = false" x-transition.opacity class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" aria-hidden="true"></div>

                            <div x-show="showDebtModal" x-transition.scale.origin.bottom class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl relative z-10 text-slate-800">
                                <div class="flex items-center justify-between mb-5">
                                    <h3 class="text-lg font-bold text-slate-900">Financial Breakdown</h3>
                                    <button @click="showDebtModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div class="bg-rose-50 p-4 rounded-xl border border-rose-100">
                                        <p class="text-xs font-semibold text-rose-500 uppercase tracking-wider mb-1">TRA Debt</p>
                                        <p class="text-lg font-bold text-slate-800"><?= formatMoney($traDebt) ?></p>
                                    </div>
                                    <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-100">
                                        <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wider mb-1">Campaign Debt</p>
                                        <p class="text-lg font-bold text-slate-800"><?= formatMoney($campaignDebt) ?></p>
                                    </div>
                                    <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-100 col-span-2">
                                        <p class="text-xs font-semibold text-emerald-500 uppercase tracking-wider mb-1">Total Payments Made</p>
                                        <p class="text-xl font-bold text-slate-800"><?= formatMoney($totalPayments) ?></p>
                                    </div>
                                </div>

                                <h4 class="text-sm font-bold text-slate-700 mb-3 border-b border-slate-100 pb-2">Recent Debt Charges</h4>
                                <div class="space-y-3 max-h-60 overflow-y-auto pr-2">
                                    <?php if (empty($myDebtsList)): ?>
                                        <p class="text-sm text-slate-500 italic">No recent debt charges.</p>
                                    <?php else: ?>
                                        <?php foreach ($myDebtsList as $debt): ?>
                                        <div class="flex justify-between items-center py-2 border-b border-slate-50 last:border-0">
                                            <div>
                                                <p class="text-sm font-medium text-slate-800"><?= e($debt['description']) ?></p>
                                                <p class="text-xs text-slate-400"><?= formatDateTime($debt['created_at'], 'd M Y') ?></p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-bold text-rose-600"><?= formatMoney($debt['debit']) ?></p>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        <?php endif; ?>

        <!-- Active Campaigns -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 group">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:scale-110 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <h3 class="font-semibold text-sm text-slate-500 uppercase tracking-wider">Active Campaigns</h3>
            </div>
            <div class="flex items-end justify-between">
                <p class="text-3xl font-bold text-slate-800 tracking-tight"><?= $activeCampaigns ?></p>
                <a href="<?= url('/contributions') ?>" class="text-sm font-medium text-brand-600 hover:text-brand-800">View <span aria-hidden="true">&rarr;</span></a>
            </div>
        </div>

        <!-- Pending Items / Actions -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 group">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:scale-110 group-hover:bg-amber-600 group-hover:text-white transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="font-semibold text-sm text-slate-500 uppercase tracking-wider">
                    <?= $isAdmin ? 'Pending Action' : 'My Submissions' ?>
                </h3>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <p class="text-sm text-slate-600 font-medium">
                        <?= $isAdmin ? 'Receipt Reviews' : 'Pending Approval' ?>
                    </p>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold <?= $stats['pending_reviews'] > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-500' ?>"><?= $stats['pending_reviews'] ?></span>
                </div>
                <?php if ($isAdmin): ?>
                <div class="flex justify-between items-center">
                    <p class="text-sm text-slate-600 font-medium">Expense Approvals</p>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold <?= $pendingExpenses > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-500' ?>"><?= $pendingExpenses ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Main Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column: Quick Actions & Policy -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
                <h3 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Quick Actions
                </h3>
                <div class="space-y-3">
                    <a href="<?= url('/sales/create') ?>" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-colors group">
                        <div class="flex items-center justify-center w-10 h-10 bg-brand-50 text-brand-600 rounded-lg group-hover:bg-brand-600 group-hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-800">Submit Receipt</p>
                            <p class="text-xs text-slate-500">Record a new EFD sale</p>
                        </div>
                    </a>

                    <a href="<?= url('/ledger') ?>" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-colors group">
                        <div class="flex items-center justify-center w-10 h-10 bg-indigo-50 text-indigo-600 rounded-lg group-hover:bg-indigo-600 group-hover:text-white transition-colors relative">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-800">My Ledger</p>
                            <p class="text-xs text-slate-500">View statement and debts</p>
                        </div>
                    </a>
                    <?php if ($isAdmin): ?>
                    <a href="<?= url('/review') ?>" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-colors group">
                        <div class="flex items-center justify-center w-10 h-10 bg-emerald-50 text-emerald-600 rounded-lg group-hover:bg-emerald-600 group-hover:text-white transition-colors relative">
                            <?php if ($stats['pending_reviews'] > 0): ?>
                            <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full border-2 border-white animate-pulse"></span>
                            <?php endif; ?>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-800">Review Receipts</p>
                            <p class="text-xs text-slate-500">Approve or reject submissions</p>
                        </div>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="bg-slate-900 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden group hover:shadow-xl transition-all">
                <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-brand-500/10 rounded-full blur-3xl group-hover:bg-brand-500/20 transition-all"></div>
                <h3 class="text-base font-semibold text-white/90 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Contribution Policy
                </h3>
                <ul class="space-y-3 text-sm text-slate-300">
                    <li class="flex items-start gap-2 bg-white/5 p-2 rounded-lg border border-white/5">
                        <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span><strong class="text-white block mb-0.5">3% Contribution</strong> When a valid purchase receipt is uploaded and approved, you only pay a 3% contribution.</span>
                    </li>
                    <li class="flex items-start gap-2 bg-white/5 p-2 rounded-lg border border-white/5">
                        <svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span><strong class="text-white block mb-0.5">10% Contribution</strong> If no receipt is uploaded or it is rejected, you must pay a 10% contribution.</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Right Column: Recent Activities or Savings Metric -->
        <div class="lg:col-span-2 space-y-6">
            
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex flex-col justify-between group hover:border-brand-200 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center mb-4 group-hover:bg-brand-50 transition-colors">
                        <svg class="w-5 h-5 text-slate-500 group-hover:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Records Submitted</p>
                        <p class="text-2xl font-bold text-slate-800"><?= number_format($stats['total_sales']) ?></p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex flex-col justify-between group hover:border-brand-200 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center mb-4 group-hover:bg-brand-50 transition-colors">
                        <svg class="w-5 h-5 text-slate-500 group-hover:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Approved Receipts</p>
                        <p class="text-2xl font-bold text-slate-800"><?= number_format($stats['approved_records']) ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-base font-semibold text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Recent Activity
                    </h3>
                    <a href="<?= url('/reports') ?>" class="text-sm font-medium text-brand-600 hover:text-brand-700">View All Log</a>
                </div>
                
                <?php if (empty($recentActivities)): ?>
                    <div class="flex flex-col items-center justify-center py-10 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200">
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mb-3 shadow-sm">
                            <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h4 class="text-sm font-medium text-slate-800">No activity yet</h4>
                    </div>
                <?php else: ?>
                    <div class="space-y-5 relative before:absolute before:inset-y-0 before:left-[19px] before:w-[2px] before:bg-slate-100">
                        <?php foreach ($recentActivities as $activity): ?>
                            <div class="relative flex gap-4 group">
                                <div class="absolute left-0 w-10 h-10 flex items-center justify-center bg-white">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-brand-700 bg-brand-50 ring-4 ring-white z-10 group-hover:bg-brand-600 group-hover:text-white transition-colors">
                                        <?= e(mb_strtoupper(mb_substr($activity['user_name'] ?? 'U', 0, 1))) ?>
                                    </div>
                                </div>
                                <div class="pl-14 pt-1.5 flex-1">
                                    <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-1 mb-0.5">
                                        <p class="text-sm text-slate-800">
                                            <span class="font-semibold"><?= e($activity['user_name'] ?? 'System') ?></span> 
                                            <?= e(str_replace('_', ' ', $activity['action'])) ?>
                                        </p>
                                        <span class="text-xs text-slate-400 font-medium shrink-0"><?= timeAgo($activity['created_at']) ?></span>
                                    </div>
                                    <?php if (!empty($activity['details'])): ?>
                                        <p class="text-sm text-slate-500 bg-slate-50 p-2 rounded-lg mt-1 border border-slate-100"><?= e($activity['details']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<style>
@keyframes fade-in {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fade-in 0.5s ease-out forwards;
}
</style>
