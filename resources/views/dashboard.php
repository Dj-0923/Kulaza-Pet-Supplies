<?php
$pageTitle = 'Dashboard';
include resource_path('views/layouts/header.php');
?>
<?php if (!$ok): ?>
<div class="card">You do not have access to this page.</div>
<?php else: ?><?php  $staff = is_staff(); $q = $staff ? ['role' => 'staff'] : [];  ?>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
    <!-- TEMPORARY preview metrics; replace these values with totals calculated from saved records. -->
    <?php foreach([["Today's Sale", '₱0.00'], [$staff ? 'Transaction Today' : 'Transactions Today', '0'], ['Products', '0'], ['Total Sale', '₱0.00']] as [$k, $v]): ?>
        <div class="card"><p class="text-xs text-gray-400"><?= e($k) ?></p><p class="text-2xl font-bold"><?= e($v) ?></p></div>
    <?php endforeach; ?>
</div>
<div class="grid md:grid-cols-3 gap-4">
    <div class="card md:col-span-2">
        <h2 class="font-semibold mb-2">Recent transaction</h2>
        <table class="tbl"><tr><th>Invoice</th><th>Customer</th><th>Total</th><th>Date</th></tr>
            <!-- TEMPORARY preview transaction; replace with the latest saved transaction. -->
            <tr><td>INV-2698001</td><td>Kung Fu Lou</td><td><?= e($staff ? '₱256.00' : '₱254.00') ?></td><td>2026-09-08</td></tr>
        </table>
    </div>
    <div class="card space-y-2">
        <h2 class="font-semibold">Quick Actions</h2>
        <a href="<?= e(route('sales.create', $q)) ?>" class="btn block">+ Record New Sale</a>
        <?php if($staff): ?>
            <a href="<?= e(route('products', $q)) ?>" class="btn-ghost">Browse Products</a>
            <a href="<?= e(route('transactions', $q)) ?>" class="btn-ghost">My Transaction History</a>
        <?php else: ?>
            <a href="<?= e(route('products')) ?>" class="btn-dark block">+ Add Product</a>
            <a href="<?= e(route('reports')) ?>" class="btn-dark block">View Sales Reports</a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
<?php include resource_path('views/layouts/footer.php'); ?>
