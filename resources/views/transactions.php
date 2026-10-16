<?php
/** @var bool $ok */
/** @var array $transactions */
$pageTitle = 'Transaction History';
include resource_path('views/layouts/header.php');
?>
<?php if (!$ok): ?>
<div class="card">You do not have access to this page.</div>
<?php else: ?><div class="card">
    <div class="flex gap-3 mb-4">
        <input type="text" name="search" placeholder="Search invoice or customer..." class="inp">
        <input type="date" name="date" class="inp !w-44">
    </div>
    <table class="tbl"><tr><th>Invoice</th><th>Customer</th><th>Items</th><th>Payment</th><th>Total</th><th>Date</th></tr>
        <?php if (!$transactions): ?>
            <tr><td colspan="6" class="text-gray-400 text-center" style="padding:3rem 0">No transactions yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($transactions as $t): ?>
            <tr><td class="font-semibold"><?= e($t['invoice']) ?></td><td><?= e($t['customer']) ?></td><td><?= e($t['items']) ?></td><td><?= e($t['payment']) ?></td><td class="font-semibold">₱<?= e(number_format($t['total'], 2)) ?></td><td><?= e($t['date']) ?></td></tr>
        <?php endforeach; ?>
    </table>
</div>
<?php endif; ?>
<?php include resource_path('views/layouts/footer.php'); ?>
