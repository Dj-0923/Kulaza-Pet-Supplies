<?php
$pageTitle = 'Sales Reports';
include resource_path('views/layouts/header.php');
?>
<?php if (!$ok): ?>
<div class="card">You do not have access to this page.</div>
<?php else: ?><?php  $cats = sample_categories();  ?>
<div class="card flex flex-wrap gap-3 items-end mb-4">
    <div><label class="lbl" for="rf">Date From</label><input id="rf" type="date" name="from" class="inp"></div>
    <div><label class="lbl" for="rt">Date To</label><input id="rt" type="date" name="to" class="inp"></div>
    <div><label class="lbl" for="rc">Category</label>
        <select id="rc" name="category" data-cats class="inp"><option>All Categories</option><?php foreach($cats as $c): ?><option><?= e($c) ?></option><?php endforeach; ?></select></div>
    <div><label class="lbl" for="rp">Payment Method</label>
        <select id="rp" name="payment" class="inp"><option>All Payment Methods</option><option>Cash</option><option>GCash</option><option>Card</option></select></div>
    <button type="button" id="apply" class="btn">Apply Filters</button>
    <button type="button" id="reset" class="btn-dark">Reset</button>
</div>
<div id="reportPrompt" class="card text-sm text-gray-400 mb-4 hidden">No sales records yet.</div>
<div id="reportEmpty" class="card text-sm text-gray-400 mb-4 hidden">No records match the selected filters.</div>
<div id="reportResults" class="hidden">
    <div class="grid md:grid-cols-3 gap-4 mb-4" id="rstats"></div>
    <div class="grid md:grid-cols-3 gap-4">
        <div class="card md:col-span-2"><p class="text-sm mb-2" id="ctitle">Last 7-days</p><div class="flex items-end gap-2 h-56" id="chart"></div></div>
        <div class="card text-sm space-y-3" id="summary"></div>
    </div>
</div>
<?php endif; ?>
<?php if ($ok): ob_start(); ?><script>
    // Sample data. Back-end: each sale needs date (YYYY-MM-DD), payment, items[{name, category, price, qty}].
    const sales = <?= json_encode($sales) ?>;
    const $ = id => document.getElementById(id);
    const ymd = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    const daysAgo = n => { const d = new Date(); d.setDate(d.getDate() - n); return ymd(d); };
    sales.forEach(s => { if (!s.date) s.date = daysAgo(s.days_ago); });
    const sum = a => a.reduce((t, s) => t + s.total, 0);

    // Only the chosen category's items are counted, so the graph, totals and summary show that category only.
    function filtered() {
        const f = $('rf').value, t = $('rt').value, c = $('rc').value, p = $('rp').value;
        return sales
            .filter(s => (!f || s.date >= f) && (!t || s.date <= t) && (p.startsWith('All') || s.payment === p))
            .map(s => { const items = c.startsWith('All') ? s.items : s.items.filter(i => i.category === c);
                        return {...s, items, total: items.reduce((a, i) => a + i.price * i.qty, 0)}; })
            .filter(s => s.items.length);
    }
    function render() {
        const L = filtered();
        $('reportPrompt').classList.add('hidden');
        $('reportEmpty').classList.toggle('hidden', L.length > 0);
        $('reportResults').classList.toggle('hidden', sales.length === 0);
        if (!sales.length) return;
        const c = $('rc').value, T = daysAgo(0), all = c.startsWith('All');
        const box = (k, v) => `<div class="card"><p class="text-xs text-gray-400">${k}</p><p class="text-3xl font-bold">${peso(v)}</p></div>`;
        $('rstats').innerHTML = box('Today', sum(L.filter(s => s.date === T))) + box('This Week', sum(L.filter(s => s.date >= daysAgo(6)))) + box('This Month', sum(L.filter(s => s.date.slice(0, 7) === T.slice(0, 7))));
        const days = [6, 5, 4, 3, 2, 1, 0].map(n => { const d = daysAgo(n); return {
            label: new Date(d + 'T00:00').toLocaleDateString('en-US', {weekday: 'short'}), v: sum(L.filter(s => s.date === d))}; });
        const mx = Math.max(1, ...days.map(x => x.v));
        $('ctitle').textContent = 'Last 7-days' + (all ? '' : ' - ' + c + ' sales only');
        $('chart').innerHTML = days.map(x => `<div class="flex-1 flex flex-col justify-end items-center h-full text-[10px] text-gray-400"><span>${peso(x.v)}</span><div class="w-full bg-green-500 rounded-sm" style="height:${Math.max(2, x.v / mx * 85)}%"></div><span class="mt-1">${x.label}</span></div>`).join('');
        const m = {}; L.forEach(s => s.items.forEach(i => m[i.name] = (m[i.name] || 0) + i.qty));
        const top = Object.entries(m).sort((a, b) => b[1] - a[1])[0];
        $('summary').innerHTML = `<p>Report Summary${all ? '' : ' - ' + c}</p><p><b>Transactions:</b> ${L.length}</p><p><b>Average transaction:</b> ${peso(L.length ? sum(L) / L.length : 0)}</p><p><b>Top product:</b> ${top ? top[0] + ' (' + top[1] + ' sold)' : 'None yet'}</p>`;
    }
    $('apply').onclick = render;
    $('reset').onclick = () => {
        ['rf', 'rt'].forEach(id => $(id).value = '');
        $('rc').selectedIndex = 0;
        $('rp').selectedIndex = 0;
        render();
    };
    if (sales.length) render();
    else $('reportPrompt').classList.remove('hidden');
</script>
<?php $extraScript = ob_get_clean(); endif; ?>
<?php include resource_path('views/layouts/footer.php'); ?>
