<?php
$pageTitle = 'Staff Accounts';
include resource_path('views/layouts/header.php');
?>
<?php if (!$ok): ?>
<div class="card">You do not have access to this page.</div>
<?php else: ?><form method="POST" action="#" class="card mb-6">
    <?= csrf_field() ?>
    <h2 class="font-semibold mb-3">Add staff accounts</h2>
    <div class="grid grid-cols-2 gap-4">
        <div><label class="lbl">Full name</label><input type="text" name="full_name" class="inp"></div>
        <div><label class="lbl">Username</label><input type="text" name="username" class="inp"></div>
        <div><label class="lbl">Temporary password</label><input type="password" name="password" class="inp"></div>
        <div><label class="lbl">Roles</label><select name="role" class="inp"><option>Staff</option></select></div>
    </div>
    <div class="flex justify-between items-end mt-4">
        <div class="text-xs"><p class="mb-2">Staff can open</p>
            <?php foreach(['Dashboard','Record sale','Products (view only)','Own transactions'] as $p): ?>
                <span class="inline-block px-3 py-1 mr-1 rounded-full bg-[#0b0e0b] border border-[#1f271f]"><?= e($p) ?></span>
            <?php endforeach; ?>
            <p class="text-gray-500 mt-2">Not included: Sales reports, Monthly records, Staff accounts</p></div>
        <div class="space-x-2"><button type="reset" class="btn-dark">Cancel</button><button type="submit" class="btn">Create Account</button></div>
    </div>
</form>
<div class="card">
    <table class="tbl"><tr><th>Name</th><th>Username</th><th>Role</th><th>Status</th><th>Actions</th></tr>
        <!-- TEMPORARY preview staff account; replace with rows from the staff/users database. -->
        <tr><td class="font-semibold">Kung Fu Lou</td><td>Fu</td><td>Staff</td><td class="text-green-500">Active</td>
            <td class="space-x-1"><a href="#" class="btn-dark !py-1">Edit</a><a href="#" class="btn-dark !py-1">Disable</a><a href="#" class="btn-dark !py-1">Reset password</a></td></tr>
    </table>
</div>
<?php endif; ?>
<?php include resource_path('views/layouts/footer.php'); ?>
