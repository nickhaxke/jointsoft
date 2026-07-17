<?php
$statusColors = [
    'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
    'approved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
    'rejected' => 'bg-red-100 text-red-800 border-red-200',
    'missing_receipt' => 'bg-slate-100 text-slate-800 border-slate-200',
];
$statusLabels = [
    'pending' => 'Pending Review',
    'approved' => 'Approved (3%)',
    'rejected' => 'Rejected (10%)',
    'missing_receipt' => 'No Receipt (10%)',
];
$badgeClass = $statusColors[$sale['status']] ?? $statusColors['missing_receipt'];
$badgeLabel = $statusLabels[$sale['status']] ?? 'Unknown';
?>

<div x-data="{ receiptModalOpen: false, currentReceiptUrl: '' }" class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="javascript:history.back()" class="w-10 h-10 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
                    Record #<?= str_pad((string)$sale['id'], 5, '0', STR_PAD_LEFT) ?>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full border <?= $badgeClass ?>">
                        <?= $badgeLabel ?>
                    </span>
                </h2>
                <p class="text-sm text-slate-500 mt-1">Submitted by <strong class="text-slate-700"><?= e($sale['owner_name']) ?></strong> on <?= date('M d, Y h:i A', strtotime($sale['created_at'])) ?></p>
            </div>
        </div>
        
        <?php if ($isAdmin && $sale['status'] === 'pending'): ?>
        <div class="flex items-center gap-3">
            <form action="<?= url('/review/' . $sale['id'] . '/reject') ?>" method="POST" class="inline" onsubmit="return confirmAction('Are you sure you want to reject this receipt? The contribution rate will increase to 10%.')">
                <?= csrf_field() ?>
                <button type="submit" class="px-4 py-2 bg-white border border-red-200 text-red-600 hover:bg-red-50 hover:border-red-300 rounded-xl text-sm font-semibold transition-colors">
                    Reject Receipt
                </button>
            </form>
            <form action="<?= url('/review/' . $sale['id'] . '/approve') ?>" method="POST" class="inline" onsubmit="return confirmAction('Are you sure you want to approve this receipt? The contribution rate will be locked at 3%.')">
                <?= csrf_field() ?>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-sm shadow-emerald-600/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Approve Receipt
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Details (Left Col) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Financial Summary -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-base font-semibold text-slate-800">Financial Summary</h3>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-6 divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
                    <div class="px-2">
                        <p class="text-sm font-medium text-slate-500 mb-1">Sale Amount</p>
                        <p class="text-2xl font-bold text-slate-800"><?= formatMoney($sale['sale_amount']) ?></p>
                    </div>
                    <div class="px-2 pt-4 sm:pt-0">
                        <p class="text-sm font-medium text-slate-500 mb-1">Purchase Amount</p>
                        <p class="text-2xl font-bold text-slate-800"><?= formatMoney($sale['purchase_amount']) ?></p>
                    </div>
                    <div class="px-2 pt-4 sm:pt-0">
                        <p class="text-sm font-medium text-brand-600 mb-1">Contribution Due</p>
                        <p class="text-2xl font-bold text-brand-700"><?= formatMoney($sale['contribution_amount']) ?></p>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <?php if (!empty($sale['notes'])): ?>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h3 class="text-sm font-semibold text-slate-800 mb-3">Additional Notes</h3>
                <div class="p-4 bg-slate-50 rounded-xl text-sm text-slate-600 whitespace-pre-wrap"><?= e($sale['notes']) ?></div>
            </div>
            <?php endif; ?>

            <!-- Timeline / Comments -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-base font-semibold text-slate-800">Activity Timeline</h3>
                </div>
                
                <div class="p-6 flex-1 max-h-[400px] overflow-y-auto space-y-6">
                    <!-- Initial Entry -->
                    <div class="flex gap-4">
                        <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <div>
                            <p class="text-sm text-slate-800 font-medium">Record Created</p>
                            <p class="text-xs text-slate-500 mt-1"><?= date('M d, Y h:i A', strtotime($sale['created_at'])) ?></p>
                        </div>
                    </div>
                    
                    <!-- Comments -->
                    <?php foreach ($comments as $comment): ?>
                    <div class="flex gap-4">
                        <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs shrink-0">
                            <?= e(mb_strtoupper(mb_substr($comment['user_name'], 0, 1))) ?>
                        </div>
                        <div class="flex-1 bg-slate-50 rounded-2xl rounded-tl-none p-4 border border-slate-100">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-semibold text-slate-800"><?= e($comment['user_name']) ?></span>
                                <span class="text-xs text-slate-400"><?= timeAgo($comment['created_at']) ?></span>
                            </div>
                            <p class="text-sm text-slate-600 whitespace-pre-wrap"><?= e($comment['content']) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Comment Form -->
                <div class="p-4 border-t border-slate-100 bg-white">
                    <form action="<?= url('/review/' . $sale['id'] . '/comment') ?>" method="POST" class="flex gap-3">
                        <?= csrf_field() ?>
                        <input type="text" name="content" required placeholder="Type a message..." 
                            class="flex-1 px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all text-sm">
                        <button type="submit" class="w-10 h-10 bg-brand-600 hover:bg-brand-700 text-white rounded-xl flex items-center justify-center transition-colors shrink-0">
                            <svg class="w-4 h-4 transform rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar (Right Col) -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Receipt Card -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden sticky top-6">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-800">Attached Receipt</h3>
                    <span class="text-xs font-medium px-2.5 py-1 bg-slate-200 text-slate-700 rounded-md">
                        <?= count($receipts) ?> File(s)
                    </span>
                </div>
                <div class="p-5">
                    <?php if (empty($receipts)): ?>
                        <div class="text-center py-8">
                            <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <p class="text-sm font-medium text-slate-600 mb-1">No Receipt Attached</p>
                            <p class="text-xs text-slate-400">Contribution rate is set to 10%.</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($receipts as $receipt): ?>
                            <button type="button" @click="currentReceiptUrl = '<?= url('/receipts/' . $sale['id'] . '/view') ?>'; receiptModalOpen = true" 
                                class="w-full text-left flex items-start gap-3 p-3 rounded-xl border border-slate-200 hover:border-brand-300 hover:shadow-sm hover:bg-brand-50 transition-all group">
                                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                </div>
                                <div class="flex-1 min-w-0 overflow-hidden">
                                    <p class="text-sm font-medium text-slate-800 truncate group-hover:text-brand-700"><?= e($receipt['original_name']) ?></p>
                                    <p class="text-xs text-slate-500 mt-0.5"><?= formatFileSize($receipt['file_size']) ?> • <?= strtoupper(explode('/', $receipt['mime_type'])[1] ?? 'FILE') ?></p>
                                </div>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Image Modal -->
    <div x-show="receiptModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            
            <div x-show="receiptModalOpen" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" 
                 @click="receiptModalOpen = false" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="receiptModalOpen" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="inline-block align-bottom bg-transparent text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full">
                
                <div class="relative">
                    <button type="button" @click="receiptModalOpen = false" class="absolute -top-10 right-0 bg-white/10 hover:bg-white/20 text-white rounded-full p-2 backdrop-blur-md transition-colors focus:outline-none">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <img :src="currentReceiptUrl" alt="Receipt Preview" class="max-w-full h-auto mx-auto rounded-xl max-h-[85vh] object-contain shadow-2xl">
                </div>
            </div>
        </div>
    </div>
</div>
