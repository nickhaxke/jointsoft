<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Office Funds</h2>
            <p class="text-sm text-slate-500 mt-1">Manage physical office cash and bank balances.</p>
        </div>
        <div class="bg-emerald-50 text-emerald-700 px-4 py-2 rounded-xl border border-emerald-100 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-emerald-600 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold opacity-80 uppercase tracking-wider">Total Liquidity</p>
                <p class="text-lg font-bold leading-none"><?= formatMoney($totalFunds) ?></p>
            </div>
        </div>
    </div>

    <!-- Account Balances -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <?php foreach ($accounts as $acc): ?>
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-500 mb-2"><?= e($acc['name']) ?></h3>
            <p class="text-2xl font-bold text-slate-900"><?= formatMoney($acc['balance']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Transaction Form -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden sticky top-6">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-base font-semibold text-slate-800">Record Transaction</h3>
                </div>
                <form action="<?= url('/funds/process') ?>" method="POST" class="p-5 space-y-4">
                    <?= csrf_field() ?>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Transaction Type</label>
                        <select name="action" id="action" required onchange="toggleTransferFields()" class="block w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 text-sm">
                            <option value="deposit">Deposit Funds (In)</option>
                            <option value="withdrawal">Withdraw Funds (Out)</option>
                            <option value="transfer">Transfer between Accounts</option>
                            <option value="reimburse">Reimburse Member (Advance Settlement)</option>
                        </select>
                    </div>

                    <div id="single-account-field">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Source/Target Account</label>
                        <select name="account_id" class="block w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 text-sm">
                            <?php foreach ($accounts as $acc): ?>
                                <option value="<?= $acc['id'] ?>"><?= e($acc['name']) ?> (Bal: <?= number_format($acc['balance']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="member-field" class="hidden">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Member to Reimburse</label>
                        <select name="user_id" id="user_id" class="block w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 text-sm">
                            <?php foreach ($members as $m): ?>
                                <option value="<?= $m['id'] ?>"><?= e($m['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="transfer-fields" class="hidden space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">From Account</label>
                            <select name="from_account_id" class="block w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 text-sm">
                                <?php foreach ($accounts as $acc): ?>
                                    <option value="<?= $acc['id'] ?>"><?= e($acc['name']) ?> (Bal: <?= number_format($acc['balance']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">To Account</label>
                            <select name="to_account_id" class="block w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 text-sm">
                                <?php foreach ($accounts as $acc): ?>
                                    <option value="<?= $acc['id'] ?>"><?= e($acc['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Amount (TZS)</label>
                        <input type="number" name="amount" required min="1" step="0.01" class="block w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Description</label>
                        <input type="text" name="description" required placeholder="Reason for transaction" class="block w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 text-sm">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all" onclick="return confirmAction('Process this transaction?')">
                            Process Transaction
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-base font-semibold text-slate-800">Recent Transactions</h3>
                </div>
                <?php if (empty($recentTransactions)): ?>
                    <div class="p-12 text-center text-slate-500 text-sm">No recent fund transactions.</div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-white">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Date & Account</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Description</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($recentTransactions as $txn): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <p class="text-sm font-semibold text-slate-900"><?= e($txn['account_name']) ?></p>
                                        <p class="text-[10px] text-slate-500"><?= formatDateTime($txn['created_at'], 'M j, Y H:i') ?></p>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <?php if ($txn['type'] === 'deposit'): ?>
                                            <span class="inline-flex px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold uppercase tracking-wider">+ IN</span>
                                        <?php else: ?>
                                            <span class="inline-flex px-2 py-0.5 rounded bg-rose-100 text-rose-800 text-[10px] font-bold uppercase tracking-wider">- OUT</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="text-sm text-slate-800"><?= e($txn['description']) ?></p>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                        <span class="text-sm font-bold <?= $txn['type'] === 'deposit' ? 'text-emerald-600' : 'text-rose-600' ?>">
                                            <?= formatMoney($txn['amount']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function toggleTransferFields() {
    const action = document.getElementById('action').value;
    const singleField = document.getElementById('single-account-field');
    const transferFields = document.getElementById('transfer-fields');
    const memberField = document.getElementById('member-field');
    const userIdSelect = document.getElementById('user_id');
    
    if (action === 'transfer') {
        singleField.classList.add('hidden');
        transferFields.classList.remove('hidden');
        memberField.classList.add('hidden');
        userIdSelect.removeAttribute('required');
    } else if (action === 'reimburse') {
        singleField.classList.remove('hidden');
        transferFields.classList.add('hidden');
        memberField.classList.remove('hidden');
        userIdSelect.setAttribute('required', 'required');
    } else {
        singleField.classList.remove('hidden');
        transferFields.classList.add('hidden');
        memberField.classList.add('hidden');
        userIdSelect.removeAttribute('required');
    }
}
</script>
