<?php
$pageTitle = 'Record Sale';
include resource_path('views/layouts/header.php');
?>
<?php if (!$ok): ?>
<div class="card">You do not have access to this page.</div>
<?php else: ?><?php  $cats = sample_categories();  ?>
<form method="POST" action="#" id="saleForm" class="grid lg:grid-cols-3 gap-4 items-start">
    <?= csrf_field() ?>
    <div class="card lg:col-span-2">
        <input type="text" id="ss" placeholder="Search product..." class="inp mb-4">
        <div class="flex flex-wrap gap-2 mb-4" id="scats"></div>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3" id="sgrid"></div>
    </div>

    <div class="card lg:sticky lg:top-6">
        <h2 class="font-semibold mb-3">Order summary</h2>
        <div class="space-y-3 mb-4">
            <div><label class="lbl" for="cust">Customer Name</label><input id="cust" name="customer_name" value="Walk-in Customer" class="inp"></div>
            <div><label class="lbl" for="pay">Payment Method</label>
                <select id="pay" name="payment_method" class="inp"><option>Cash</option><option>GCash</option><option>Card</option></select></div>
        </div>
        <div id="rows" class="mb-4"></div>
        <div id="hidden"></div>
        <div class="flex justify-between bg-[#1a2a1a] rounded-lg px-3 py-2 mb-4"><span>Total Sale</span><span id="tot" class="font-bold">₱0.00</span></div>
        <button type="submit" class="btn block w-full">Save Transaction</button>
    </div>
</form>
<?php endif; ?>
<?php if ($ok): ob_start(); ?><script>
    const products = <?= json_encode($products) ?>;   // back-end: same shape (id, name, category, price, stock)
    let cart = [], scat = 'All';
    const $ = id => document.getElementById(id);

    function renderProducts() {
        const q = $('ss').value.toLowerCase();
        const list = products.map((p, i) => ({p, i})).filter(({p}) => (scat === 'All' || p.category === scat) && p.name.toLowerCase().includes(q));
        $('sgrid').innerHTML = list.map(({p, i}) => `<button type="button" ${p.stock < 1 ? 'disabled' : ''} onclick="addToCart(${i})" class="text-left rounded-xl p-3 border border-[#1f271f] bg-[#0b0e0b] hover:border-green-600 ${p.stock < 1 ? 'opacity-40' : ''}">
            <p class="font-semibold">${p.name}</p><p class="text-xs text-gray-400">${p.category}</p>
            <p class="mt-2 text-green-400 font-semibold">${peso(p.price)}</p><p class="text-xs text-gray-500">${p.stock < 1 ? 'Out of stock' : p.stock + ' in stock'}</p></button>`).join('')
            || '<p class="col-span-full text-center text-gray-400 py-12">No products match. Try another category or search.</p>';
    }
    function renderCart() {
        $('rows').innerHTML = cart.map((r, i) => { const p = products[r.p]; return `<div class="py-2 border-t border-[#1f271f]">
            <div class="flex justify-between text-sm"><span class="font-semibold">${p.name}</span><span id="lt${i}">${peso(p.price * r.q)}</span></div>
            <div class="flex items-center gap-2 mt-1 text-xs text-gray-400"><span>${peso(p.price)} each</span><span class="flex-1"></span>
            <button type="button" class="btn-dark !px-2 !py-0" onclick="chg(${i},-1)" aria-label="Decrease">-</button>
            <input type="number" inputmode="numeric" min="1" max="${p.stock}" value="${r.q}" aria-label="Quantity" class="inp !w-16 !py-1 text-center" onfocus="this.select()" oninput="typeQty(${i},this)" onchange="renderCart()">
            <button type="button" class="btn-dark !px-2 !py-0" onclick="chg(${i},1)" aria-label="Increase">+</button>
            <button type="button" class="btn-red !py-0" onclick="cart.splice(${i},1);renderCart()" aria-label="Remove">X</button></div></div>`; }).join('')
            || '<p class="text-sm text-gray-400 py-4 text-center border-t border-[#1f271f]">No items yet. Click a product to add it.</p>';
        $('hidden').innerHTML = cart.map((r, i) => `<input type="hidden" name="items[${i}][product_id]" value="${products[r.p].id}"><input type="hidden" name="items[${i}][quantity]" value="${r.q}">`).join('');
        updTot();
    }
    function updTot() { $('tot').textContent = peso(cart.reduce((t, r) => t + products[r.p].price * r.q, 0)); }
    function typeQty(i, el) {
        const r = cart[i], v = parseInt(el.value); if (isNaN(v) || v < 1) return;
        const m = products[r.p].stock; if (v > m) { toast('Only ' + m + ' in stock for ' + products[r.p].name + '.'); el.value = m; }
        r.q = Math.min(v, m); $('lt' + i).textContent = peso(products[r.p].price * r.q); updTot();
        $('hidden').querySelectorAll('input')[i * 2 + 1].value = r.q;
    }
    function addToCart(i) {
        const r = cart.find(x => x.p === i);
        if (r) { if (r.q >= products[i].stock) return toast('No more stock for ' + products[i].name + '.'); r.q++; } else cart.push({p: i, q: 1});
        renderCart();
    }
    function chg(i, d) {
        const r = cart[i]; if (d > 0 && r.q >= products[r.p].stock) return toast('No more stock for ' + products[r.p].name + '.');
        r.q += d; if (r.q < 1) cart.splice(i, 1); renderCart();
    }
    function renderSTabs() { makeTabs($('scats'), scat, c => { scat = c; renderSTabs(); renderProducts(); }); }
    renderSTabs();
    $('ss').oninput = renderProducts;
    $('saleForm').onsubmit = e => {
        if (!cart.length) { e.preventDefault(); return toast('Add at least one product.'); }
        // PREVIEW ONLY: while action is "#", do not submit. Back-end sets a real action and this stops applying.
        if (e.target.getAttribute('action') === '#') {
            e.preventDefault();
            cart = [];
            scat = 'All';
            $('cust').value = 'Walk-in Customer';
            $('pay').selectedIndex = 0;
            $('ss').value = '';
            renderSTabs();
            renderProducts();
            renderCart();
            toast('Preview only: transaction was not saved. Connect the backend to record it.');
        }
    };
    renderProducts(); renderCart();
</script>
<?php $extraScript = ob_get_clean(); endif; ?>
<?php include resource_path('views/layouts/footer.php'); ?>
