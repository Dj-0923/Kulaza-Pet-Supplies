<?php
/**
 * resources/views/price-records.php
 *
 * Expects $records (array) from the route/controller. One row per price change:
 *   ['date' => '2026-09-18', 'product' => 'Tomatoes', 'category' => 'Vegetables',
 *    'old_price' => 88.00, 'new_price' => 95.00, 'changed_by' => 'Maria']
 * The route currently supplies temporary records based on the sample products.
 */
$records = $records ?? [];
$pageTitle = 'Price Records';

$today    = date('Y-m-d');
$month    = request('month', date('Y-m'));
$cat      = request('category', '');
$prod     = request('product', '');
$sort     = request('sort', 'new');
$page     = max(1, (int) request('page', 1));
$perPage  = 10;
$peso     = fn($v) => '₱' . number_format((float) $v, 2);
$qs       = fn(array $o) => '?' . http_build_query(array_merge(request()->query(), $o));

// ---- Filter options (built only from real records) ----
$months = ['all' => 'All time'];
for ($i = 0; $i < 6; $i++) {
    $m = date('Y-m', strtotime("first day of -$i month"));
    $months[$m] = date('F Y', strtotime($m . '-01'));
}
$categories = array_values(array_unique(array_column($records, 'category')));
sort($categories);
$products = [];
foreach ($records as $r) {
    if ($cat === '' || $r['category'] === $cat) $products[$r['product']] = true;
}
$products = array_keys($products);
sort($products);

// ---- Category + product filter (used by stats and chart) ----
$base = array_values(array_filter($records, fn($r) =>
    ($cat === '' || $r['category'] === $cat) && ($prod === '' || $r['product'] === $prod)
));

// ---- Table rows: base + selected period, sorted, paginated ----
$rows = array_values(array_filter($base, fn($r) => $month === 'all' || strpos($r['date'], $month) === 0));
usort($rows, function ($a, $b) use ($sort) {
    if ($sort === 'old') return strcmp($a['date'], $b['date']);
    if ($sort === 'big') return abs($b['new_price'] - $b['old_price']) <=> abs($a['new_price'] - $a['old_price']);
    return strcmp($b['date'], $a['date']);
});
$total    = count($rows);
$pages    = max(1, (int) ceil($total / $perPage));
$page     = min($page, $pages);
$pageRows = array_slice($rows, ($page - 1) * $perPage, $perPage);

// ---- Stat cards: Today / This Week / This Month ----
$weekStart  = date('Y-m-d', strtotime('monday this week'));
$monthStart = date('Y-m-01');
$periods = [
    'Today'      => fn($d) => $d === $today,
    'This Week'  => fn($d) => $d >= $weekStart && $d <= $today,
    'This Month' => fn($d) => $d >= $monthStart && $d <= $today,
];
$stats = [];
foreach ($periods as $label => $in) {
    $list = array_values(array_filter($base, fn($r) => $in($r['date'])));
    usort($list, fn($a, $b) => strcmp($b['date'], $a['date']));
    $stats[] = ['label' => $label, 'count' => count($list), 'latest' => $list[0] ?? null];
}

// ---- Chart data: one series per product ----
if ($month === 'all') {
    $start = $base ? min(array_column($base, 'date')) : $today;
    $end   = $today;
} else {
    $start = $month . '-01';
    $end   = min(date('Y-m-t', strtotime($start)), $today);
    if ($end < $start) $end = $start;
}
$byProd = [];
foreach ($base as $r) $byProd[$r['product']][] = $r;
$prodSeries = [];
foreach ($byProd as $name => $list) {
    usort($list, fn($a, $b) => strcmp($a['date'], $b['date']));
    $prodSeries[] = [
        'product' => $name,
        'cat'     => $list[0]['category'],
        'base'    => (float) $list[0]['old_price'],
        'changes' => array_map(fn($r) => ['d' => $r['date'], 'o' => (float) $r['old_price'], 'n' => (float) $r['new_price']], $list),
    ];
}

// "All Categories" at walang napiling product: isang linya kada category
// (average ng presyo ng mga product nito). Kapag may napiling category o
// product: isang linya kada product. Ang 'subs' ay ang mga product sa linya.
$series = [];
if ($cat === '' && $prod === '') {
    $byCat = [];
    foreach ($prodSeries as $p) $byCat[$p['cat']][] = $p;
    ksort($byCat);
    foreach ($byCat as $name => $subs) $series[] = ['product' => $name, 'subs' => $subs];
} else {
    foreach ($prodSeries as $p) $series[] = ['product' => $p['product'], 'subs' => [$p]];
}
$chart = ['series' => $series, 'start' => $start, 'end' => $end];
?>
<?php include resource_path('views/layouts/header.php'); ?>

<?php if (!$ok): ?>
<div class="card">You do not have access to this page.</div>
<?php else: ?>
<style>
.pr-filters{display:grid;grid-template-columns:repeat(4,1fr) auto;gap:12px}
.pr-filters select{height:44px;padding:0 14px;border-radius:10px;border:1px solid #1e251e;background:#0c100c;color:inherit;min-width:0}
.pr-go{height:44px;padding:0 28px;border-radius:10px;border:0;background:#2f8f3a;color:#fff;font-weight:600;cursor:pointer}
.pr-go:hover{background:#3fb14c}
.pr-stat{border:1px solid #1e251e;border-radius:12px;padding:14px 18px;background:#0c100c;min-height:96px}
.pr-stat.rise{border-left:3px solid #e5675a}.pr-stat.drop{border-left:3px solid #3fb14c}
.pr-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
.pr-cnt{font-size:12px;background:#1b2a1d;color:#9fd6a6;border-radius:99px;padding:2px 10px}
.pr-hl{font-size:20px;font-weight:600}.pr-hl span{font-size:15px}
.pr-mute{color:#8d988d;font-size:12px}
.pr-up{color:#e5675a}.pr-down{color:#3fb14c}
.pr-empty{padding:28px;text-align:center;color:#8d988d}
.pr-leg{display:flex;gap:16px;flex-wrap:wrap;font-size:12px;color:#8d988d;margin-bottom:4px}
.pr-leg i,.pr-tip i{display:inline-block;width:9px;height:9px;border-radius:50%;margin-right:6px}
#pr-chart{position:relative}#pr-chart svg{width:100%;height:auto;display:block;cursor:crosshair}
.pr-tip{position:absolute;pointer-events:none;background:#000;border:1px solid #222;border-radius:12px;padding:8px 14px;font-size:12px;line-height:1.35;white-space:nowrap;display:none}
.pr-tip small{display:block;color:#8d988d}.pr-tip b{font-size:18px}
.pr-tip .r{display:flex;align-items:center;gap:10px;margin-top:4px}.pr-tip .r b{font-size:12px;margin-left:auto}
.pr-pill{display:inline-block;padding:2px 8px;border-radius:99px;background:#1b2a1d;color:#9fd6a6;font-size:12px}
.pr-pager{display:flex;justify-content:space-between;align-items:center;margin-top:14px;font-size:12px;color:#8d988d}
.pr-pager a{padding:7px 14px;border:1px solid #1e251e;border-radius:10px;color:inherit;text-decoration:none}
.pr-pager a.off{opacity:.4;pointer-events:none}
@media(max-width:900px){.pr-filters{grid-template-columns:1fr 1fr}.pr-go{grid-column:1/-1}}
</style>

<!-- Filters -->
<form method="GET" class="card !bg-[#0b0e0b] mb-6">
    <div class="pr-filters">
        <select name="month" aria-label="Period" onchange="this.form.submit()">
            <?php foreach ($months as $v => $label): ?>
                <option value="<?= e($v) ?>" <?= $month === $v ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="category" aria-label="Category" onchange="this.form.product.value='';this.form.submit()">
            <option value="">All Categories</option>
            <?php foreach ($categories as $c): ?>
                <option <?= $cat === $c ? 'selected' : '' ?>><?= e($c) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="product" aria-label="Product" onchange="this.form.submit()">
            <option value="">All Products</option>
            <?php foreach ($products as $p): ?>
                <option <?= $prod === $p ? 'selected' : '' ?>><?= e($p) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="sort" aria-label="Sort" onchange="this.form.submit()">
            <option value="new" <?= $sort === 'new' ? 'selected' : '' ?>>Newest first</option>
            <option value="old" <?= $sort === 'old' ? 'selected' : '' ?>>Oldest first</option>
            <option value="big" <?= $sort === 'big' ? 'selected' : '' ?>>Biggest change</option>
        </select>
        <button class="pr-go" type="submit">Refresh Records</button>
    </div>
</form>

<!-- Stats -->
<div class="grid grid-cols-3 gap-4 mb-6">
    <?php foreach ($stats as $s):
        $l  = $s['latest'];
        $ch = $l ? $l['new_price'] - $l['old_price'] : 0;
        $pp = $l && $l['old_price'] > 0 ? $ch / $l['old_price'] * 100 : 0;
    ?>
        <div class="pr-stat <?= $l ? ($ch > 0 ? 'rise' : 'drop') : '' ?>">
            <div class="pr-top">
                <span class="pr-mute"><?= e($s['label']) ?></span>
                <span class="pr-cnt"><?= (int) $s['count'] ?> <?= $s['count'] === 1 ? 'change' : 'changes' ?></span>
            </div>
            <?php if ($l): ?>
                <div class="pr-hl"><?= e($l['product']) ?>
                    <span class="<?= $ch > 0 ? 'pr-up' : 'pr-down' ?>"><?= $ch > 0 ? '▲ +' : '▼ ' ?><?= number_format($pp, 1) ?>%</span>
                </div>
                <div class="pr-mute mt-1"><?= e($peso($l['old_price'])) ?> to <?= e($peso($l['new_price'])) ?> on <?= e($l['date']) ?></div>
            <?php else: ?>
                <div class="pr-mute" style="font-size:15px">No Data</div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<!-- Graph -->
<div class="card !bg-[#0b0e0b] mb-6">
    <div class="flex justify-between items-baseline">
        <p class="font-semibold mb-2"><?= e($prod !== '' ? "$prod price over time"
            : ($cat !== '' ? "Products in $cat · price over time" : 'Average price per category over time')) ?></p>
        <span class="pr-mute"><?= e($start) ?> to <?= e($end) ?></span>
    </div>
    <?php if (empty($series)): ?>
        <div class="pr-empty">No Data</div>
    <?php else: ?>
        <div class="pr-leg" id="pr-leg"></div>
        <div id="pr-chart"><div class="pr-tip" id="pr-tip"></div></div>
    <?php endif; ?>
</div>

<!-- Records table -->
<div class="card !bg-[#0b0e0b]">
    <p class="font-semibold mb-2">Price change records</p>
    <?php if ($total === 0): ?>
        <div class="pr-empty">No Data</div>
    <?php else: ?>
        <div style="overflow-x:auto">
        <table class="tbl">
            <tr><th>Date</th><th>Product</th><th>Category</th><th>Old price</th><th>New price</th><th>Change</th><th>Changed by</th></tr>
            <?php foreach ($pageRows as $r):
                $ch = $r['new_price'] - $r['old_price'];
                $pp = $r['old_price'] > 0 ? $ch / $r['old_price'] * 100 : 0;
            ?>
                <tr>
                    <td><?= e($r['date']) ?></td>
                    <td><?= e($r['product']) ?></td>
                    <td><span class="pr-pill"><?= e($r['category']) ?></span></td>
                    <td><?= e($peso($r['old_price'])) ?></td>
                    <td><strong><?= e($peso($r['new_price'])) ?></strong></td>
                    <td class="<?= $ch > 0 ? 'pr-up' : 'pr-down' ?>">
                        <?= $ch > 0 ? '▲' : '▼' ?> <?= e($peso(abs($ch))) ?> (<?= $ch > 0 ? '+' : '' ?><?= number_format($pp, 1) ?>%)
                    </td>
                    <td><?= e($r['changed_by'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        </div>
        <div class="pr-pager">
            <span>Showing <?= ($page - 1) * $perPage + 1 ?>-<?= ($page - 1) * $perPage + count($pageRows) ?> of <?= $total ?></span>
            <div style="display:flex;gap:8px">
                <a href="<?= e($qs(['page' => $page - 1])) ?>" class="<?= $page <= 1 ? 'off' : '' ?>">Previous</a>
                <a href="<?= e($qs(['page' => $page + 1])) ?>" class="<?= $page >= $pages ? 'off' : '' ?>">Next</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($series)): ?>
<script>
(function () {
    const DATA = <?= json_encode($chart, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const COL = ['#3fb14c', '#e5a93a', '#4aa3df', '#c87be0', '#e5675a'];
    const box = document.getElementById('pr-chart'), tip = document.getElementById('pr-tip');
    const DAY = 864e5, ts = [];
    for (let t = Date.parse(DATA.start); t <= Date.parse(DATA.end); t += DAY) ts.push(t);
    const iso = t => new Date(t).toISOString().slice(0, 10);
    const peso = v => '₱' + Number(v).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const fd = t => new Date(t).toLocaleDateString('en-US', {month: 'short', day: 'numeric', timeZone: 'UTC'});

    // price in effect on each day, per product
    const ser = DATA.series.map((s, k) => ({
        p: s.product, col: COL[k % COL.length],
        pts: ts.map(t => {
            // average ng presyo ng lahat ng product sa linya (1 product = presyo mismo)
            const ds = iso(t); let sum = 0, delta = 0;
            s.subs.forEach(b => {
                let v = b.base;
                b.changes.forEach(x => { if (x.d <= ds) v = x.n; if (x.d === ds) delta += x.n - x.o; });
                sum += v;
            });
            const d = delta / s.subs.length;
            return {v: sum / s.subs.length, d, has: Math.abs(d) > 0.0001};
        })
    }));
    const one = ser.length === 1, n = ts.length;
    const W = 900, H = 280, L = 14, R = 18, T = 34, B = 14;
    const vs = ser.flatMap(q => q.pts.map(z => z.v));
    const lo = Math.min(...vs), hi = Math.max(...vs), pad = (hi - lo || 10) * (one ? .25 : .12);
    const mn = lo - pad * (one ? 2 : 1), mx = hi + pad;
    const X = i => L + i / (n - 1 || 1) * (W - L - R);
    const Y = v => T + (1 - (v - mn) / (mx - mn)) * (H - T - B);

    document.getElementById('pr-leg').innerHTML = one ? '' :
        ser.map(q => `<span><i style="background:${q.col}"></i>${q.p}</span>`).join('');

    let g = '';
    ts.forEach((t, i) => g += `<line x1="${X(i)}" x2="${X(i)}" y1="${T}" y2="${H - B}" stroke="#fff" opacity=".05"/>`);
    [0, .25, .5, .75, 1].forEach((k, j) => {
        const i = Math.round(k * (n - 1));
        g += `<text x="${X(i)}" y="16" fill="#8d988d" font-size="11" text-anchor="${j == 0 ? 'start' : j == 4 ? 'end' : 'middle'}">${fd(ts[i])}</text>`;
    });
    let body = '';
    ser.forEach(q => {
        let d = `M${X(0)} ${Y(q.pts[0].v)}`;
        for (let i = 1; i < n; i++) {
            const mid = (X(i - 1) + X(i)) / 2;
            d += `C${mid} ${Y(q.pts[i - 1].v)} ${mid} ${Y(q.pts[i].v)} ${X(i)} ${Y(q.pts[i].v)}`;
        }
        if (one) body += `<path d="${d}V${H - B}H${X(0)}Z" fill="url(#pr-ag)"/>`;
        body += `<path d="${d}" fill="none" stroke="${q.col}" stroke-width="${one ? 1.8 : 2}"/>`;
    });
    const dots = ser.map(q => `<circle class="cd" r="4.5" fill="${q.col}"/>`).join('');
    box.insertAdjacentHTML('afterbegin',
        `<svg viewBox="0 0 ${W} ${H}" role="img" tabindex="0" aria-label="Price chart. Use the arrow keys to read each day.">
        <defs><linearGradient id="pr-ag" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="${ser[0].col}" stop-opacity=".28"/><stop offset="1" stop-color="${ser[0].col}" stop-opacity="0"/></linearGradient></defs>
        ${g}${body}<line id="pr-cx" y1="${T}" y2="${H - B}" stroke="#8d988d" stroke-width="1.2" style="display:none"/>${dots}
        <rect width="${W}" height="${H}" fill="transparent"/></svg>`);

    const svg = box.querySelector('svg'), cx = svg.querySelector('#pr-cx'), cds = [...svg.querySelectorAll('.cd')];
    let cur = n - 1;
    const show = (i, hover) => {
        cur = i = Math.max(0, Math.min(n - 1, i));
        const x = X(i), ys = ser.map(q => Y(q.pts[i].v));
        cds.forEach((c, k) => { c.setAttribute('cx', x); c.setAttribute('cy', ys[k]); });
        cx.setAttribute('x1', x); cx.setAttribute('x2', x); cx.style.display = hover ? 'block' : 'none';
        if (!hover) { tip.style.display = 'none'; return; }
        if (one) {
            const q = ser[0].pts[i], ch = q.d;
            tip.innerHTML = `<small>${fd(ts[i])}</small><b>${peso(q.v)}</b>` +
                (q.has ? `<div class="${ch > 0 ? 'pr-up' : 'pr-down'}">${ch > 0 ? '▲ +' : '▼ -'}${peso(Math.abs(ch))} from ${peso(q.v - ch)}</div>` : '');
            tip.style.top = (ys[0] / H * 100) + '%'; tip.style.transform = 'translate(-50%,-125%)';
        } else {
            tip.innerHTML = `<small>${fd(ts[i])}</small>` + ser.map(q => {
                const z = q.pts[i], ch = z.d;
                return `<div class="r"><span><i style="background:${q.col}"></i>${q.p}</span><b>${peso(z.v)}</b>` +
                    (z.has ? `<span class="${ch > 0 ? 'pr-up' : 'pr-down'}">${ch > 0 ? '▲' : '▼'}</span>` : '') + `</div>`;
            }).join('');
            tip.style.top = '12%'; tip.style.transform = 'translate(-50%,0)';
        }
        tip.style.left = Math.min(Math.max(x / W * 100, 14), 86) + '%'; tip.style.display = 'block';
    };
    const idx = e => {
        const r = svg.getBoundingClientRect(), cl = e.touches ? e.touches[0].clientX : e.clientX;
        return Math.round(((cl - r.left) / r.width * W - L) / (W - L - R) * (n - 1));
    };
    svg.onmousemove = svg.ontouchmove = e => show(idx(e), true);
    svg.onmouseleave = svg.onblur = () => show(n - 1, false);
    svg.onkeydown = e => {
        if (e.key === 'ArrowLeft') { show(cur - 1, true); e.preventDefault(); }
        if (e.key === 'ArrowRight') { show(cur + 1, true); e.preventDefault(); }
    };
    show(n - 1, false);
})();
</script>
<?php endif; ?>
<?php endif; ?>

<?php include resource_path('views/layouts/footer.php'); ?>
