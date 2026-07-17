<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Monthly Contributions</h2>
            <p class="text-sm text-slate-500 mt-1">Manage office funds and member contributions.</p>
        </div>
        <?php if ($isAdmin): ?>
        <a href="<?= url('/contributions/create') ?>" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-semibold transition-all duration-200 shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Campaign
        </a>
        <?php endif; ?>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500">Active Campaigns</p>
                    <p class="text-xl font-bold text-slate-900"><?= number_format($stats['total_campaigns']) ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Expected</p>
                    <p class="text-xl font-bold text-slate-900"><?= formatMoney($stats['total_expected']) ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Collected</p>
                    <p class="text-xl font-bold text-slate-900"><?= formatMoney($stats['total_collected']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- My Balances -->
    <div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-6 bg-gradient-to-br from-brand-50 to-white">
        <h3 class="text-lg font-bold text-slate-800 mb-4">My Contribution Summary</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div>
                <p class="text-sm font-medium text-slate-500">My Total Expected</p>
                <p class="text-xl font-bold text-slate-900 mt-1"><?= formatMoney($myStats['total_expected']) ?></p>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">I Have Paid</p>
                <p class="text-xl font-bold text-emerald-600 mt-1"><?= formatMoney($myStats['total_paid']) ?></p>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">My Outstanding Balance</p>
                <p class="text-xl font-bold <?= $myStats['remaining_balance'] > 0 ? 'text-red-600' : 'text-slate-900' ?> mt-1"><?= formatMoney($myStats['remaining_balance']) ?></p>
            </div>
        </div>
    </div>

    <?php if ($isAdmin && !empty($pendingPayments)): ?>
    <div class="bg-white rounded-2xl border border-amber-200 shadow-sm p-6 mb-6">
        <h3 class="text-lg font-bold text-amber-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Pending Payments Awaiting Approval
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Member</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Campaign</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Method & Ref</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500">Amount</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($pendingPayments as $payment): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm font-semibold text-slate-900"><?= e($payment['member_name']) ?></td>
                        <td class="px-4 py-3 text-sm text-slate-700"><?= e($payment['contribution_title']) ?></td>
                        <td class="px-4 py-3 text-sm text-slate-700">
                            <span class="capitalize"><?= str_replace('_', ' ', $payment['payment_method']) ?></span>
                            <br>
                            <span class="text-xs text-slate-500 font-mono"><?= e($payment['reference_code']) ?></span>
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-emerald-600">
                            <?= formatMoney($payment['amount']) ?>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <form action="<?= url('/contributions/approve-payment/' . $payment['id']) ?>" method="POST" class="flex flex-col sm:flex-row items-end sm:items-center justify-end gap-2">
                                <?= csrf_field() ?>
                                <select name="account_id" required class="px-2 py-1 border border-slate-200 rounded text-xs">
                                    <option value="">Select Office Account...</option>
                                    <?php 
                                        $accountModel = new \App\Models\Account();
                                        $accounts = $accountModel->all();
                                        foreach($accounts as $acc):
                                    ?>
                                    <option value="<?= $acc['id'] ?>"><?= e($acc['name']) ?> (<?= formatMoney($acc['balance']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="action" value="approve" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-semibold transition-colors">Approve</button>
                                <button type="submit" name="action" value="reject" class="px-3 py-1 bg-rose-100 hover:bg-rose-200 text-rose-700 rounded text-xs font-semibold transition-colors" onclick="return confirm('Are you sure you want to reject this payment?')">Reject</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Campaign List -->
        <div class="space-y-4">
            <h3 class="text-lg font-bold text-slate-800 px-1">Active Campaigns</h3>
            <?php if (empty($contributions)): ?>
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-8 text-center">
                    <p class="text-slate-500">No campaigns have been created yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($contributions as $campaign): ?>
                <a href="<?= url('/contributions/' . $campaign['id']) ?>" class="block bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:border-brand-300 transition-all group">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h4 class="font-bold text-slate-900 group-hover:text-brand-600 transition-colors"><?= e($campaign['title']) ?></h4>
                            <p class="text-xs text-slate-500 mt-0.5">Due: <?= formatDateTime($campaign['due_date'], 'M j, Y') ?></p>
                        </div>
                        <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-semibold">
                            <?= $campaign['total_members'] ?> Members
                        </span>
                    </div>
                    <div class="flex justify-between items-end mt-4">
                        <div>
                            <p class="text-xs text-slate-500 font-medium">Target Amount</p>
                            <p class="font-bold text-slate-900"><?= formatMoney($campaign['total_expected_amount']) ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-slate-500 font-medium">Collected</p>
                            <p class="font-bold text-emerald-600"><?= formatMoney($campaign['total_collected'] ?? 0) ?></p>
                        </div>
                    </div>
                    
                    <?php 
                        $progress = $campaign['total_expected_amount'] > 0 ? min(100, round((($campaign['total_collected'] ?? 0) / $campaign['total_expected_amount']) * 100)) : 0;
                    ?>
                    <div class="mt-4 w-full bg-slate-100 rounded-full h-1.5">
                        <div class="bg-brand-500 h-1.5 rounded-full" style="width: <?= $progress ?>%"></div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- My Assignments -->
        <div class="space-y-4">
            <h3 class="text-lg font-bold text-slate-800 px-1">My Assignments</h3>
            <?php if (empty($myContributions)): ?>
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-8 text-center">
                    <p class="text-slate-500">You have not been assigned to any contributions.</p>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Campaign</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Status</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500">Balance</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($myContributions as $my): ?>
                                <?php $bal = $my['expected_amount'] - $my['paid_amount']; ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3">
                                        <p class="text-sm font-semibold text-slate-900"><?= e($my['title']) ?></p>
                                        <p class="text-[10px] text-slate-500">Target: <?= formatMoney($my['expected_amount']) ?></p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php if ($my['status'] === 'paid'): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">Paid</span>
                                        <?php elseif ($my['status'] === 'partial'): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">Partial</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm font-bold <?= $bal > 0 ? 'text-red-600' : 'text-slate-900' ?>"><?= formatMoney($bal) ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
