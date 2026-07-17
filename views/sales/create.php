<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-4 mb-2">
        <a href="<?= url('/dashboard') ?>" class="w-10 h-10 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <h2 class="text-2xl font-bold text-slate-800">Record New Sale</h2>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-base font-semibold text-slate-800">Sale Information</h3>
            <p class="text-sm text-slate-500 mt-1">Enter the details from your EFD receipt and upload the purchase evidence.</p>
        </div>

        <form action="<?= url('/sales/store') ?>" method="POST" enctype="multipart/form-data" class="p-6 space-y-6" onsubmit="return validateSaleForm(this)">
            <?= csrf_field() ?>

            <!-- Amounts Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label for="sale_amount" class="block text-sm font-semibold text-slate-700">Sale Amount (TZS) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-slate-400 sm:text-sm">TZS</span>
                        </div>
                        <input type="number" step="0.01" min="1" name="sale_amount" id="sale_amount" required
                            class="block w-full pl-12 pr-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all text-sm"
                            placeholder="0.00" oninput="calculateEstimate()">
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="purchase_amount" class="block text-sm font-semibold text-slate-700">Purchase Amount (TZS) <span class="text-slate-400 font-normal">(Optional)</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-slate-400 sm:text-sm">TZS</span>
                        </div>
                        <input type="number" step="0.01" min="0" name="purchase_amount" id="purchase_amount"
                            class="block w-full pl-12 pr-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all text-sm"
                            placeholder="0.00">
                    </div>
                </div>
            </div>

            <!-- Receipt Upload -->
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-slate-700">Purchase Receipt <span class="text-slate-400 font-normal">(Required for 3% rate)</span></label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-xl hover:bg-slate-50 transition-colors relative group" id="drop-zone">
                    <div class="space-y-2 text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-400 group-hover:text-brand-500 transition-colors" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-slate-600 justify-center">
                            <label for="receipt" class="relative cursor-pointer rounded-md bg-transparent font-medium text-brand-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-brand-500 focus-within:ring-offset-2 hover:text-brand-500">
                                <span>Upload a file</span>
                                <input id="receipt" name="receipt" type="file" class="sr-only" accept="image/jpeg,image/png,image/webp,application/pdf" onchange="handleFileSelect(this)">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-slate-500">PNG, JPG, PDF up to 5MB</p>
                    </div>
                </div>
                <div id="file-name-display" class="hidden mt-2 p-3 bg-brand-50 text-brand-700 text-sm rounded-lg flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span id="file-name-text" class="font-medium">filename.jpg</span>
                    </span>
                    <button type="button" onclick="clearFile()" class="text-brand-500 hover:text-brand-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- Notes -->
            <div class="space-y-2">
                <label for="notes" class="block text-sm font-semibold text-slate-700">Additional Notes</label>
                <textarea id="notes" name="notes" rows="3" 
                    class="block w-full p-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all text-sm" 
                    placeholder="Any comments regarding this sale or receipt..."></textarea>
            </div>

            <!-- Live Estimate -->
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 flex gap-4">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-blue-900">Estimated Contribution</h4>
                    <p class="text-sm text-blue-700 mt-1">
                        Based on the sale amount, your estimated contribution will be 
                        <strong id="est-amount">TZS 0.00</strong> 
                        at a rate of <strong id="est-rate">10%</strong>.
                    </p>
                    <p class="text-xs text-blue-600 mt-1" id="est-hint">Upload a receipt to lower your rate to 3%.</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="<?= url('/dashboard') ?>" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-50 rounded-xl transition-colors">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save Record
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let hasFile = false;

    function handleFileSelect(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            document.getElementById('file-name-text').textContent = file.name;
            document.getElementById('file-name-display').classList.remove('hidden');
            document.getElementById('file-name-display').classList.add('flex');
            hasFile = true;
            calculateEstimate();
        }
    }

    function clearFile() {
        document.getElementById('receipt').value = '';
        document.getElementById('file-name-display').classList.add('hidden');
        document.getElementById('file-name-display').classList.remove('flex');
        hasFile = false;
        calculateEstimate();
    }

    function calculateEstimate() {
        const amount = parseFloat(document.getElementById('sale_amount').value) || 0;
        const rate = hasFile ? 0.03 : 0.10;
        const rateText = hasFile ? '3%' : '10%';
        const contribution = amount * rate;
        
        document.getElementById('est-amount').textContent = formatCurrency(contribution);
        document.getElementById('est-rate').textContent = rateText;
        
        if (hasFile) {
            document.getElementById('est-hint').textContent = 'Receipt attached. Pending admin approval for 3% rate.';
            document.getElementById('est-hint').className = 'text-xs text-emerald-600 mt-1';
        } else {
            document.getElementById('est-hint').textContent = 'Upload a receipt to lower your rate to 3%.';
            document.getElementById('est-hint').className = 'text-xs text-blue-600 mt-1';
        }
    }

    function validateSaleForm(form) {
        if (!hasFile) {
            return confirmAction('You are saving this record WITHOUT a receipt. Your contribution rate will be locked at 10%. Are you sure you want to proceed?');
        }
        return true;
    }

    // Drag and drop support
    const dropZone = document.getElementById('drop-zone');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.add('border-brand-500', 'bg-brand-50'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.remove('border-brand-500', 'bg-brand-50'), false);
    });

    dropZone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        document.getElementById('receipt').files = files;
        handleFileSelect(document.getElementById('receipt'));
    }
</script>
