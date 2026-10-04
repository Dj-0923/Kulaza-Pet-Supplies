<?php
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
        <!-- TEMPORARY preview transaction; replace with rows from the transactions database. -->
        <tr><td class="font-semibold">INV-2698001</td><td>Kung Fu Lou</td><td>Tomatoes x4</td><td>Cash</td><td class="font-semibold">₱254.00</td><td>2026-09-08</td></tr>
    </table>
</div>
<?php endif; ?>
<?php include resource_path('views/layouts/footer.php'); ?>
