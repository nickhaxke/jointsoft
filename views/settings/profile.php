<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">My Profile</h2>
        <p class="text-sm text-slate-500 mt-1">Manage your account settings and change your password.</p>
    </div>

    <!-- Personal Info -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-base font-semibold text-slate-800">Personal Information</h3>
        </div>
        <div class="p-6 space-y-6">
            <div class="flex items-center gap-6">
                <div class="w-20 h-20 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-3xl font-bold border-4 border-white shadow-sm shrink-0">
                    <?= e(mb_strtoupper(mb_substr(auth()['name'], 0, 1))) ?>
                </div>
                <div>
                    <p class="text-xl font-bold text-slate-900"><?= e(auth()['name']) ?></p>
                    <p class="text-slate-500"><?= e(auth()['email']) ?></p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 mt-2">
                        <?= ucfirst(auth()['role']) ?> Account
                    </span>
                </div>
            </div>
            <div class="bg-blue-50 text-blue-800 p-4 rounded-xl text-sm border border-blue-100">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p>To update your name or email address, please contact the System Administrator.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-base font-semibold text-slate-800">Change Password</h3>
            <p class="text-sm text-slate-500 mt-1">Ensure your account is using a long, random password to stay secure.</p>
        </div>
        <form action="<?= url('/settings/profile/update') ?>" method="POST" class="p-6 space-y-6">
            <?= csrf_field() ?>
            
            <div class="space-y-2">
                <label for="current_password" class="block text-sm font-semibold text-slate-700">Current Password</label>
                <input type="password" name="current_password" id="current_password" required
                    class="block w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all text-sm">
            </div>

            <div class="space-y-2">
                <label for="new_password" class="block text-sm font-semibold text-slate-700">New Password</label>
                <input type="password" name="new_password" id="new_password" required minlength="8"
                    class="block w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all text-sm">
                <p class="text-xs text-slate-500">Must be at least 8 characters long.</p>
            </div>

            <div class="space-y-2">
                <label for="confirm_password" class="block text-sm font-semibold text-slate-700">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required minlength="8"
                    class="block w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all text-sm">
            </div>

            <div class="pt-4 flex justify-end border-t border-slate-100">
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>
