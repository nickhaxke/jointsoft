<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Financial Statement</h2>
            <p class="text-sm text-slate-500 mt-1">Official ledger for <?= e($member['name']) ?></p>
        </div>
        <div class="text-right">
            <?php 
                $latest = end($transactions);
                $finalBalance = $latest ? $latest['running_balance'] : 0.00;
            ?>
            <p class="text-sm font-medium text-slate-500">Current Balance</p>
            <p class="text-2xl font-bold <?= $finalBalance > 0 ? 'text-red-600' : ($finalBalance < 0 ? 'text-emerald-600' : 'text-slate-900') ?>">
                <?php if ($finalBalance > 0): ?>
                    <?= formatMoney($finalBalance) ?> <span class="text-xs text-red-500 font-medium ml-1">OWES OFFICE</span>
                <?php elseif ($finalBalance < 0): ?>
                    <?= formatMoney(abs($finalBalance)) ?> <span class="text-xs text-emerald-500 font-medium ml-1">OFFICE OWES MEMBER</span>
                <?php else: ?>
                    <?= formatMoney(0) ?> <span class="text-xs text-slate-400 font-medium ml-1">CLEARED</span>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <?php if (empty($transactions)): ?>
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">No Transactions Found</h3>
                <p class="text-slate-500 text-sm">There are no financial records for this member yet.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Description</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Type</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Debit (Owes)</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Credit (Paid)</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <?php foreach ($transactions as $txn): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                <?= formatDateTime($txn['created_at'], 'd M Y, H:i') ?>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-semibold text-slate-900"><?= e($txn['description']) ?></p>
                                <?php if ($txn['created_by_name']): ?>
                                <p class="text-[10px] text-slate-400 mt-0.5">Recorded by <?= e($txn['created_by_name']) ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 capitalize">
                                    <?= str_replace('_', ' ', $txn['transaction_type']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm <?= $txn['debit'] > 0 ? 'font-bold text-red-600' : 'text-slate-300' ?>">
                                <?= $txn['debit'] > 0 ? formatMoney($txn['debit']) : '-' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm <?= $txn['credit'] > 0 ? 'font-bold text-emerald-600' : 'text-slate-300' ?>">
                                <?= $txn['credit'] > 0 ? formatMoney($txn['credit']) : '-' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-bold <?= $txn['running_balance'] > 0 ? 'text-red-600' : ($txn['running_balance'] < 0 ? 'text-emerald-600' : 'text-slate-900') ?>">
                                    <?= formatMoney(abs($txn['running_balance'])) ?>
                                </span>
                                <?php if ($txn['running_balance'] > 0): ?>
                                    <span class="text-[10px] text-red-500/80 font-medium block">OWES OFFICE</span>
                                <?php elseif ($txn['running_balance'] < 0): ?>
                                    <span class="text-[10px] text-emerald-500/80 font-medium block">OFFICE OWES MEMBER</span>
                                <?php else: ?>
                                    <span class="text-[10px] text-slate-400 font-medium block">CLEARED</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
