<?php
$pageTitle = 'Monthly Records';
include resource_path('views/layouts/header.php');
?>
<?php if (!$ok): ?>
<div class="card">You do not have access to this page.</div>
<?php else: ?><div class="card">
    <!-- TEMPORARY preview monthly records below; replace dates, metrics, summaries, and rows with filtered database results. -->
    <form method="GET" class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
        <input type="month" name="month" value="2026-09" class="inp !w-full !h-11">
        <select name="category" data-cats class="inp !w-full !h-11"><option>All Categories</option><?php foreach(sample_categories() as $c): ?><option><?= e($c) ?></option><?php endforeach; ?></select>
        <select name="payment" class="inp !w-full !h-11"><option>All Payment Methods</option><option>Cash</option><option>GCash</option><option>Card</option></select>
        <button class="btn !h-11 !flex items-center justify-center whitespace-nowrap col-span-2 md:col-span-1">Refresh Records</button>
    </form>
    <div class="grid grid-cols-3 gap-4 mb-4">
        <?php foreach(['Today'=>'₱0.00','This Week'=>'₱340.00','This Month'=>'₱340.00'] as $k => $v): ?>
            <div class="card !bg-[#0b0e0b]"><p class="text-xs text-gray-400"><?= e($k) ?></p><p class="text-2xl font-bold"><?= e($v) ?></p></div>
        <?php endforeach; ?>
    </div>
    <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
        <div class="card !bg-[#0b0e0b]"><p class="mb-2">Top products</p>
            <?php foreach([['Tomatoes','Vegetables','4 sold'],['Rice','Grains','1 sold'],['Corn','Grains','1 sold']] as $i => [$n,$c,$s]): ?>
                <div class="flex justify-between py-1"><span><?= e($i+1) ?> &nbsp; <?= e($n) ?></span><span class="text-gray-400"><?= e($c) ?> &nbsp; <?= e($s) ?></span></div>
            <?php endforeach; ?>
        </div>
        <div class="card !bg-[#0b0e0b]"><p class="mb-2">Sales by payment method</p>
            <?php foreach(['Cash'=>100,'GCash'=>0,'Card'=>0] as $m => $pct): ?>
                <div class="mb-2"><div class="flex justify-between"><span><?= e($m) ?></span><span><?= e($pct) ?>%</span></div>
                <div class="h-1 bg-[#1f271f] rounded"><div class="h-1 bg-green-600 rounded" style="width: <?= e($pct) ?>%"></div></div></div>
            <?php endforeach; ?>
        </div>
    </div>
    <p class="font-semibold mb-2">30 Days Daily Records</p>
    <table class="tbl"><tr><th>Day</th><th>Date</th><th>Transactions</th><th>Total Earnings</th></tr>
        <?php for($i = 1; $i <= 7; $i++): ?>
            <tr><td>Day <?= e($i) ?></td><td>2026-09-<?= e(str_pad($i, 2, '0', STR_PAD_LEFT)) ?></td><td>0</td><td>₱0.00</td></tr>
        <?php endfor; ?>
    </table>
</div>
<?php endif; ?>
<?php include resource_path('views/layouts/footer.php'); ?>
