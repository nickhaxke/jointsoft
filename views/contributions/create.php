<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <a href="<?= url('/contributions') ?>" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-slate-800 mb-4 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Contributions
        </a>
        <h2 class="text-2xl font-bold text-slate-800">Create Campaign</h2>
        <p class="text-sm text-slate-500 mt-1">Start a new contribution campaign. All active members will be automatically assigned their share.</p>
    </div>

    <form action="<?= url('/contributions/store') ?>" method="POST" class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <?= csrf_field() ?>
        
        <div class="p-6 md:p-8 space-y-6">
            <div>
                <label for="title" class="block text-sm font-semibold text-slate-700 mb-1">Campaign Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="title" required placeholder="e.g. July Office Rent"
                    class="block w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all text-sm">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="total_expected_amount" class="block text-sm font-semibold text-slate-700 mb-1">Total Target Amount (TZS) <span class="text-red-500">*</span></label>
                    <input type="number" name="total_expected_amount" id="total_expected_amount" required min="1" step="0.01" placeholder="e.g. 500000"
                        class="block w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all text-sm">
                    <p class="text-xs text-slate-500 mt-1">This amount will be split equally among all members.</p>
                </div>

                <div>
                    <label for="due_date" class="block text-sm font-semibold text-slate-700 mb-1">Due Date <span class="text-red-500">*</span></label>
                    <input type="date" name="due_date" id="due_date" required min="<?= date('Y-m-d') ?>"
                        class="block w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all text-sm">
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-slate-700 mb-1">Description</label>
                <textarea name="description" id="description" rows="3" placeholder="Provide extra details..."
                    class="block w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all text-sm"></textarea>
            </div>
        </div>

        <div class="p-6 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="<?= url('/contributions') ?>" class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-900 transition-colors">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Launch Campaign
            </button>
        </div>
    </form>
</div>
