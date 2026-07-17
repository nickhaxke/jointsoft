<div class="max-w-5xl mx-auto space-y-6">
    <div>
        <a href="<?= url('/contributions') ?>" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-slate-800 mb-4 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Contributions
        </a>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800"><?= e($campaign['title']) ?></h2>
                <p class="text-sm text-slate-500 mt-1">Due Date: <?= formatDateTime($campaign['due_date'], 'M j, Y') ?></p>
            </div>
            
            <?php 
                $totalCollected = 0;
                foreach ($members as $m) $totalCollected += $m['paid_amount'];
                $progress = $campaign['total_expected_amount'] > 0 ? min(100, round(($totalCollected / $campaign['total_expected_amount']) * 100)) : 0;
            ?>
            <div class="bg-white rounded-xl border border-slate-100 p-3 shadow-sm flex items-center gap-4">
                <div>
                    <p class="text-xs text-slate-500 font-medium">Progress</p>
                    <p class="text-lg font-bold text-slate-900"><?= $progress ?>%</p>
                </div>
                <div class="w-32 h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-brand-500" style="width: <?= $progress ?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaign Details -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <p class="text-sm font-medium text-slate-500">Target Amount</p>
            <p class="text-xl font-bold text-slate-900 mt-1"><?= formatMoney($campaign['total_expected_amount']) ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Collected So Far</p>
            <p class="text-xl font-bold text-emerald-600 mt-1"><?= formatMoney($totalCollected) ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Remaining Deficit</p>
            <p class="text-xl font-bold text-red-600 mt-1"><?= formatMoney(max(0, $campaign['total_expected_amount'] - $totalCollected)) ?></p>
        </div>
        <?php if ($campaign['description']): ?>
        <div class="md:col-span-3 pt-4 border-t border-slate-100">
            <p class="text-sm font-medium text-slate-500 mb-2">Description</p>
            <p class="text-sm text-slate-800 bg-slate-50 p-4 rounded-xl border border-slate-100 whitespace-pre-wrap"><?= e($campaign['description']) ?></p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Member Submit Payment Section -->
    <?php 
    $myRecord = null;
    foreach ($members as $m) {
        if ($m['user_id'] == auth()['id']) {
            $myRecord = $m;
            break;
        }
    }
    ?>
    <?php if ($myRecord && $myRecord['expected_amount'] > $myRecord['paid_amount']): ?>
    <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-6 shadow-sm">
        <h3 class="text-lg font-bold text-indigo-900 mb-2">Submit Your Payment</h3>
        <p class="text-sm text-indigo-700 mb-4">You have a remaining balance of <strong><?= formatMoney($myRecord['expected_amount'] - $myRecord['paid_amount']) ?></strong>. Submit a payment record below for Admin approval.</p>
        
        <form action="<?= url('/contributions/submit-payment/' . $myRecord['id']) ?>" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <?= csrf_field() ?>
            <div>
                <label class="block text-sm font-semibold text-indigo-900 mb-1">Amount Paid</label>
                <input type="number" name="amount" required step="0.01" max="<?= $myRecord['expected_amount'] - $myRecord['paid_amount'] ?>" class="block w-full px-3 py-2 border border-indigo-200 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm bg-white" placeholder="0.00">
            </div>
            <div>
                <label class="block text-sm font-semibold text-indigo-900 mb-1">Payment Method</label>
                <select name="payment_method" required class="block w-full px-3 py-2 border border-indigo-200 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm bg-white">
                    <option value="bank">Bank Transfer</option>
                    <option value="mobile_money">Mobile Money (M-Pesa/Tigo)</option>
                    <option value="cash">Cash</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-indigo-900 mb-1">Reference Code</label>
                <input type="text" name="reference_code" required class="block w-full px-3 py-2 border border-indigo-200 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm bg-white" placeholder="e.g. 7A8B9C...">
            </div>
            <div>
                <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-sm transition-colors">
                    Submit Payment
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Member Ledger -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-base font-semibold text-slate-800">Member Ledger</h3>
            <span class="text-xs font-semibold text-slate-500 bg-white px-3 py-1 rounded-lg border border-slate-200"><?= count($members) ?> Assigned</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-white">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Member</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Expected</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Paid</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Balance</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <?php if ($isAdmin): ?>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Update Payment</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($members as $m): ?>
                    <?php $bal = $m['expected_amount'] - $m['paid_amount']; ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600">
                                    <?= e(mb_strtoupper(mb_substr($m['member_name'], 0, 1))) ?>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900"><?= e($m['member_name']) ?></p>
                                    <p class="text-xs text-slate-500"><?= e($m['member_email']) ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-slate-900">
                            <?= formatMoney($m['expected_amount']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-emerald-600">
                            <?= formatMoney($m['paid_amount']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold <?= $bal > 0 ? 'text-red-600' : 'text-slate-900' ?>">
                            <?= formatMoney($bal) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <?php if ($m['status'] === 'paid'): ?>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-100 text-emerald-800">Paid</span>
                            <?php elseif ($m['status'] === 'partial'): ?>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-100 text-amber-800">Partial</span>
                            <?php elseif ($m['status'] === 'overpaid'): ?>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-100 text-blue-800">Overpaid</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-800">Pending</span>
                            <?php endif; ?>
                        </td>
                        <?php if ($isAdmin): ?>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <form action="<?= url('/contributions/payment/' . $m['id']) ?>" method="POST" class="flex items-center justify-end gap-2">
                                <?= csrf_field() ?>
                                <input type="number" name="paid_amount" value="<?= (float)$m['paid_amount'] ?>" step="0.01" min="0" class="w-24 px-2 py-1 text-sm border border-slate-200 rounded focus:ring-1 focus:ring-brand-500 focus:border-brand-500">
                                <button type="submit" class="p-1.5 bg-slate-100 hover:bg-brand-100 text-slate-600 hover:text-brand-700 rounded transition-colors" title="Update Payment">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </form>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
