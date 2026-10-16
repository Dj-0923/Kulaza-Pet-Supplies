<?php
/** @var bool $ok */
/** @var \Illuminate\Support\Collection<int, \App\Models\User> $staffAccounts */
/** @var \Illuminate\Support\ViewErrorBag $errors */
$pageTitle = 'Staff Accounts';
include resource_path('views/layouts/header.php');
?>
<?php if (!$ok): ?>
<div class="card">You do not have access to this page.</div>
<?php else: ?>
<?php if (session('status')): ?><div class="card text-green-400 mb-4"><?= e(session('status')) ?></div><?php endif; ?>
<?php if ($errors->any()): ?><div class="card text-red-300 mb-4"><?php foreach ($errors->all() as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<form method="POST" action="<?= e(route('staff.store')) ?>" class="card mb-6">
    <?= csrf_field() ?>
    <h2 class="font-semibold mb-3">Add Staff Account</h2>
    <div class="grid grid-cols-2 gap-4">
        <div><label class="lbl" for="full_name">Full name</label><input id="full_name" type="text" name="full_name" value="<?= e(old('full_name')) ?>" class="inp" required></div>
        <div><label class="lbl" for="username">Username</label><input id="username" type="text" name="username" value="<?= e(old('username')) ?>" class="inp" autocomplete="username" required></div>
        <div><label class="lbl" for="password">Temporary password</label><div class="relative"><input id="password" type="password" name="password" class="inp pr-14" autocomplete="new-password" minlength="8" required><button type="button" data-toggle-password="password" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-300" aria-label="Show password" aria-pressed="false"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg></button></div></div>
        <div><label class="lbl" for="password_confirmation">Confirm password</label><div class="relative"><input id="password_confirmation" type="password" name="password_confirmation" class="inp pr-14" autocomplete="new-password" minlength="8" required><button type="button" data-toggle-password="password_confirmation" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-300" aria-label="Show confirmation password" aria-pressed="false"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg></button></div></div>
    </div>
    <div class="flex justify-between items-end mt-4">
        <div class="text-xs"><p class="mb-2">Staff can open</p>
            <?php foreach(['Dashboard','Record sale','Products (view only)','Own transactions'] as $p): ?>
                <span class="inline-block px-3 py-1 mr-1 rounded-full bg-[#0b0e0b] border border-[#1f271f]"><?= e($p) ?></span>
            <?php endforeach; ?>
            <p class="text-gray-500 mt-2">Not included: Sales reports, Monthly records, Staff accounts</p></div>
        <div class="space-x-2"><button type="reset" class="btn-dark">Clear</button><button type="submit" class="btn">Create Account</button></div>
    </div>
</form>
<div class="card">
    <table class="tbl"><tr><th>Name</th><th>Username</th><th>Role</th><th>Status</th><th>Actions</th></tr>
        <?php if ($staffAccounts->isEmpty()): ?>
            <tr><td colspan="5" class="text-gray-400 text-center" style="padding:3rem 0">No staff accounts yet. Create an account using the form above.</td></tr>
        <?php endif; ?>
        <?php foreach ($staffAccounts as $staffAccount): ?>
            <tr>
                <td class="font-semibold"><?= e($staffAccount->name) ?></td>
                <td><?= e($staffAccount->username) ?></td>
                <td>Staff</td>
                <td class="<?= $staffAccount->is_active ? 'text-green-500' : 'text-gray-400' ?>"><?= $staffAccount->is_active ? 'Active' : 'Inactive' ?></td>
                <td>
                    <div class="flex items-center gap-2 whitespace-nowrap">
                    <form method="POST" action="<?= e(route('staff.status', $staffAccount)) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_method" value="PATCH">
                        <input type="hidden" name="is_active" value="<?= $staffAccount->is_active ? '0' : '1' ?>">
                        <button type="submit" class="btn-dark !py-1"><?= $staffAccount->is_active ? 'Mark Inactive' : 'Mark Active' ?></button>
                    </form>
                    <button type="button" class="btn-dark !py-1" data-reset-password="<?= e($staffAccount->id) ?>">Reset Password</button>
                    </div>
                    <form method="POST" action="<?= e(route('staff.password', $staffAccount)) ?>" id="reset-password-<?= e($staffAccount->id) ?>" class="hidden space-y-2">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_method" value="PATCH">
                        <input type="password" name="password" class="inp" placeholder="New password" minlength="8" autocomplete="new-password" required>
                        <input type="password" name="password_confirmation" class="inp" placeholder="Confirm new password" minlength="8" autocomplete="new-password" required>
                        <button type="submit" class="btn">Save Password</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php endif; ?>
<?php if ($ok): ob_start(); ?><script>
    document.querySelectorAll('[data-reset-password]').forEach(button => {
        button.addEventListener('click', () => {
            const form = document.getElementById(`reset-password-${button.dataset.resetPassword}`);
            form.classList.toggle('hidden');
            button.setAttribute('aria-expanded', String(!form.classList.contains('hidden')));
        });
    });
    const eyeIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>';
    const eyeOffIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/></svg>';
    document.querySelectorAll('[data-toggle-password]').forEach(button => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.togglePassword);
            const isVisible = input.type === 'text';
            input.type = isVisible ? 'password' : 'text';
            button.innerHTML = isVisible ? eyeIcon : eyeOffIcon;
            button.setAttribute('aria-pressed', String(!isVisible));
            button.setAttribute('aria-label', `${isVisible ? 'Show' : 'Hide'} ${input.id === 'password' ? 'password' : 'confirmation password'}`);
        });
    });
</script>
<?php $extraScript = ob_get_clean(); endif; ?>
<?php include resource_path('views/layouts/footer.php'); ?>
