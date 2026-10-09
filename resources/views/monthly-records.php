<?php
$pageTitle = 'Monthly Records';
$month = request('month', date('Y-m'));
if (!preg_match('/^\d{4}-\d{2}$/', $month) || (int) substr($month, 5, 2) < 1 || (int) substr($month, 5, 2) > 12) {
    $month = date('Y-m');
}
$category = request('category', '');
$payment = request('payment', '');
$payments = ['Cash', 'GCash', 'Card'];
$today = date('Y-m-d');
$weekStart = date('Y-m-d', strtotime('monday this week'));
$monthStart = date('Y-m-01');
$sales = array_map(function ($sale) {
    $date = $sale['date'] ?? date('Y-m-d', strtotime('-' . (int) ($sale['days_ago'] ?? 0) . ' days'));
    $items = $sale['items'];
    return [
        'date' => $date,
        'payment' => $sale['payment'],
        'items' => $items,
        'total' => array_sum(array_map(fn ($item) => $item['price'] * $item['qty'], $items)),
    ];
}, $sales ?? []);
$filteredSales = [];
foreach ($sales as $sale) {
    $items = $category === '' ? $sale['items'] : array_values(array_filter($sale['items'], fn ($item) => $item['category'] === $category));
    if (!$items || ($payment !== '' && $sale['payment'] !== $payment)) {
        continue;
    }
    $sale['items'] = $items;
    $sale['total'] = array_sum(array_map(fn ($item) => $item['price'] * $item['qty'], $items));
    $filteredSales[] = $sale;
}
$monthSales = array_values(array_filter($filteredSales, fn ($sale) => strpos($sale['date'], $month) === 0));
$salesForPeriod = fn ($start, $end) => array_values(array_filter($filteredSales, fn ($sale) =>
    $sale['date'] >= $start && $sale['date'] <= $end
));
$periods = [
    'Today' => $salesForPeriod($today, $today),
    'This Week' => $salesForPeriod($weekStart, $today),
    'This Month' => $salesForPeriod($monthStart, $today),
];
$topProducts = [];
foreach ($monthSales as $sale) {
    foreach ($sale['items'] as $item) {
        $topProducts[$item['name']] = ($topProducts[$item['name']] ?? 0) + $item['qty'];
    }
}
arsort($topProducts);
$paymentTotals = array_fill_keys($payments, 0);
foreach ($monthSales as $sale) {
    $paymentTotals[$sale['payment']] = ($paymentTotals[$sale['payment']] ?? 0) + $sale['total'];
}
$monthTotal = array_sum(array_column($monthSales, 'total'));
$daysInMonth = (int) date('t', strtotime($month . '-01'));
$daily = [];
for ($day = 1; $day <= $daysInMonth; $day++) {
    $date = $month . '-' . str_pad((string) $day, 2, '0', STR_PAD_LEFT);
    $daily[$date] = ['count' => 0, 'total' => 0];
}
foreach ($monthSales as $sale) {
    $daily[$sale['date']]['count']++;
    $daily[$sale['date']]['total'] += $sale['total'];
}
include resource_path('views/layouts/header.php');
?>
<?php if (!$ok): ?>
<div class="card">You do not have access to this page.</div>
<?php else: ?><div class="card">
    <form method="GET" class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
        <input type="month" name="month" value="<?= e($month) ?>" class="inp !w-full !h-11">
        <select name="category" data-cats class="inp !w-full !h-11">
            <option value="">All Categories</option>
            <?php foreach (sample_categories() as $c): ?><option value="<?= e($c) ?>" <?= $category === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?>
        </select>
        <select name="payment" class="inp !w-full !h-11">
            <option value="">All Payment Methods</option>
            <?php foreach ($payments as $method): ?><option value="<?= e($method) ?>" <?= $payment === $method ? 'selected' : '' ?>><?= e($method) ?></option><?php endforeach; ?>
        </select>
        <button class="btn !h-11 !flex items-center justify-center whitespace-nowrap col-span-2 md:col-span-1">Refresh Records</button>
    </form>
    <div class="grid grid-cols-3 gap-4 mb-4">
        <?php foreach ($periods as $label => $periodSales): ?>
            <div class="card !bg-[#0b0e0b]"><p class="text-xs text-gray-400"><?= e($label) ?></p><p class="text-2xl font-bold">₱<?= e(number_format(array_sum(array_column($periodSales, 'total')), 2)) ?></p></div>
        <?php endforeach; ?>
    </div>
    <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
        <div class="card !bg-[#0b0e0b]"><p class="mb-2">Top products</p>
            <?php if (!$topProducts): ?><p class="text-gray-400">No sales records for this selection.</p><?php endif; ?>
            <?php foreach (array_slice($topProducts, 0, 5, true) as $name => $quantity): ?>
                <div class="flex justify-between py-1"><span><?= e($name) ?></span><span class="text-gray-400"><?= e($quantity) ?> sold</span></div>
            <?php endforeach; ?>
        </div>
        <div class="card !bg-[#0b0e0b]"><p class="mb-2">Sales by payment method</p>
            <?php foreach ($payments as $method):
                $share = $monthTotal > 0 ? ($paymentTotals[$method] ?? 0) / $monthTotal * 100 : 0;
            ?>
                <div class="mb-2"><div class="flex justify-between"><span><?= e($method) ?></span><span><?= e(number_format($share, 1)) ?>%</span></div>
                <div class="h-1 bg-[#1f271f] rounded"><div class="h-1 bg-green-600 rounded" style="width: <?= e($share) ?>%"></div></div></div>
            <?php endforeach; ?>
        </div>
    </div>
    <p class="font-semibold mb-2">Daily Records · <?= e(date('F Y', strtotime($month . '-01'))) ?></p>
    <div class="overflow-x-auto">
    <table class="tbl"><tr><th>Day</th><th>Date</th><th>Transactions</th><th>Total Earnings</th></tr>
        <?php foreach ($daily as $date => $record): ?>
            <tr><td><?= e(date('l', strtotime($date))) ?></td><td><?= e($date) ?></td><td><?= e($record['count']) ?></td><td>₱<?= e(number_format($record['total'], 2)) ?></td></tr>
        <?php endforeach; ?>
    </table>
    </div>
    <p class="text-xs text-gray-400 mt-3">Month sales total: ₱<?= e(number_format($monthTotal, 2)) ?> · <?= count($monthSales) ?> transactions</p>
</div>
<?php endif; ?>
<?php include resource_path('views/layouts/footer.php'); ?>
