<?php
/** @var bool $ok */
/** @var bool $isStaff */
/** @var float $todaySale */
/** @var int $todayCount */
/** @var int $productCount */
/** @var float $totalSale */
/** @var \App\Models\Transaction|null $latest */
$pageTitle = 'Dashboard';
include resource_path('views/layouts/header.php');
?>
<?php if (!$ok): ?>
<div class="card">You do not have access to this page.</div>
<?php else: ?><?php  $staff = $isStaff; $q = $staff ? ['role' => 'staff'] : [];  ?>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
    <?php $metrics = [
        ["Today's Sale", '₱' . number_format($todaySale, 2)],
        [$staff ? 'Transaction Today' : 'Transactions Today', number_format($todayCount)],
        ['Products', number_format($productCount)],
        ['Total Sale', '₱' . number_format($totalSale, 2)],
    ]; ?>
    <?php foreach ($metrics as [$k, $v]): ?>
        <div class="card"><p class="text-xs text-gray-400"><?= e($k) ?></p><p class="text-2xl font-bold"><?= e($v) ?></p></div>
    <?php endforeach; ?>
</div>
<div class="grid md:grid-cols-3 gap-4">
    <div class="card md:col-span-2">
        <h2 class="font-semibold mb-2">Recent transaction</h2>
        <table class="tbl"><tr><th>Invoice</th><th>Customer</th><th>Total</th><th>Date</th></tr>
            <?php if ($latest): ?>
            <tr><td class="font-semibold"><?= e($latest->invoice) ?></td><td><?= e($latest->customer_name) ?></td><td>₱<?= e(number_format($latest->total, 2)) ?></td><td><?= e($latest->sold_at) ?></td></tr>
            <?php else: ?>
            <tr><td colspan="4" class="text-gray-400 text-center" style="padding:2rem 0">No transactions yet.</td></tr>
            <?php endif; ?>
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
