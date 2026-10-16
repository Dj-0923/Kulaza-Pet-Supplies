<?php
/** @var bool $ok */
/** @var bool $isStaff */
/** @var array $products */
/** @var \Illuminate\Support\ViewErrorBag $errors */
$pageTitle = 'Products';
include resource_path('views/layouts/header.php');
?>
<?php if (!$ok): ?>
<div class="card">You do not have access to this page.</div>
<?php else: ?>
<?php if (session('status')): ?><div class="card text-green-400 mb-4"><?= e(session('status')) ?></div><?php endif; ?>
<?php if (session('stock_status')): ?><div id="stockUpdateToast" class="fixed bottom-6 right-6 z-[60] max-w-sm rounded-lg border border-[#1f271f] bg-[#141914] px-4 py-3 text-sm text-green-400 shadow-lg" role="status" aria-live="polite"><?= e(session('stock_status')) ?></div><?php endif; ?>
<?php if ($errors->any()): ?><div class="card text-red-300 mb-4"><?php foreach ($errors->all() as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<div class="flex flex-wrap gap-2 mb-4" id="cats" style="display:<?= $products ? 'flex' : 'none' ?>"></div>
<div class="card" style="min-height:26rem">
    <div class="flex gap-3 mb-4">
        <input type="text" id="search" placeholder="Search product..." class="inp">
        <?php if(!$isStaff): ?>
            <button type="button" id="deleteCategory" class="btn-red whitespace-nowrap hidden">Delete Category</button>
            <button type="button" id="openAdd" class="btn whitespace-nowrap">+ Add Product</button>
        <?php endif; ?>
    </div>
    <div class="overflow-x-auto">
        <table class="tbl" style="table-layout:fixed;min-width:480px">
            <colgroup><col style="width:30%"><col style="width:23%"><col style="width:17%"><?php if(!$isStaff): ?><col style="width:15%"><col style="width:15%"><?php else: ?><col style="width:30%"><?php endif; ?></colgroup>
            <tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><?php if(!$isStaff): ?><th>Actions</th><?php endif; ?></tr>
            <tbody id="prod">
                <?php foreach($products as $p): ?>
                    <tr data-id="<?= e($p['id']) ?>" data-cat="<?= e($p['category']) ?>" data-name="<?= e(strtolower($p['name'])) ?>">
                        <td><span class="font-semibold"><?= e($p['name']) ?></span><?php if (!empty($p['weight'])): ?><span class="block text-xs text-gray-400"><?= e($p['weight']) ?> kg</span><?php endif; ?></td><td><?= e($p['category']) ?></td>
                        <td>₱<?= e(number_format($p['price'], 2)) ?></td><td><?= e(number_format($p['stock'])) ?></td>
                        <?php if(!$isStaff): ?>
                            <td class="space-y-1">
                                <button type="button" class="btn-dark" data-edit-stock="<?= e($p['id']) ?>" data-stock-url="<?= e(route('products.stock', $p['id'])) ?>" data-product-name="<?= e($p['name']) ?>" data-stock="<?= e($p['stock']) ?>">Edit Stock</button>
                                <button type="button" data-delete-product class="btn-red">Delete</button>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                <tr id="none" class="<?= $products ? 'hidden' : '' ?>"><td colspan="<?= $isStaff ? 4 : 5 ?>" class="text-gray-400 text-center" style="padding:5rem 0"><?= $products ? 'No products match. Try another category or search.' : 'No products have been added yet.' ?></td></tr>
            </tbody>
        </table>
    </div>
</div>


<?php if(!$isStaff): ?>
<div id="modal" class="hidden fixed inset-0 z-50 items-center justify-center p-4" style="background:rgba(0,0,0,.7)">
    <form method="POST" action="<?= e(route('products.store')) ?>" id="addForm" class="card w-full max-w-md" style="background:#171c15;padding:24px">
        <?= csrf_field() ?>
        <h2 class="text-lg font-bold mb-4">Add Product</h2>
        <div class="space-y-4">
            <div><label class="lbl" for="pn">Product name</label><input id="pn" name="name" value="<?= e(old('name')) ?>" class="inp" placeholder="Enter product name" autocomplete="off" required></div>
            <div><label class="lbl" for="pc">Category</label>
                <select id="pc" name="category" class="inp" required>
                    <option value="">Choose a category</option>
                    <?php foreach ($categories as $category): ?><option value="<?= e($category) ?>" <?= old('category') === $category ? 'selected' : '' ?>><?= e($category) ?></option><?php endforeach; ?>
                    <option value="__new_category__">+ Add new category</option>
                </select>
            </div>
            <div id="newCategoryWrap" class="hidden">
                <label class="lbl" for="newCategory">New category</label>
                <input id="newCategory" name="new_category" value="<?= e(old('new_category')) ?>" class="inp" placeholder="Enter new category" autocomplete="off" disabled>
            </div>
            <div>
                <label class="flex items-center gap-2 text-sm text-gray-300">
                    <input id="usesKg" type="checkbox" class="accent-green-600">
                    Does this product use kg?
                </label>
            </div>
            <div id="weightWrap" class="hidden">
                <label class="lbl" for="productWeight">Weight (kg)</label>
                <input id="productWeight" name="weight" type="number" min="0.01" step="0.01" value="<?= e(old('weight')) ?>" class="inp" placeholder="Enter weight in kg" disabled>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="lbl" for="pp">Price</label><input id="pp" name="price" type="number" min="0" step="0.01" value="<?= e(old('price')) ?>" class="inp" placeholder="Enter price" required></div>
                <div><label class="lbl" for="pk">Stock</label><input id="pk" name="stock" type="number" min="0" value="<?= e(old('stock')) ?>" class="inp" placeholder="Enter quantity" required></div>
            </div>
        </div>
        <div class="flex justify-end gap-2 mt-6">
            <button type="button" id="closeAdd" class="btn-dark">Cancel</button>
            <button type="submit" class="btn">Save Product</button>
        </div>
    </form>
</div>
<?php endif; ?>
<?php endif; ?>
<?php if(!$isStaff && $ok): ?>
<div id="stockModal" class="hidden fixed inset-0 z-50 items-center justify-center p-4" style="background:rgba(0,0,0,.7)" role="dialog" aria-modal="true" aria-labelledby="stockModalTitle">
    <form method="POST" id="stockForm" class="card w-full max-w-md" style="background:#171c15;padding:24px">
        <?= csrf_field() ?>
        <h2 id="stockModalTitle" class="text-lg font-bold mb-2">Edit Stock</h2>
        <p id="stockProductName" class="text-sm text-gray-400 mb-4"></p>
        <label class="lbl mt-4" for="stockQuantity">Quantity</label>
        <div class="flex items-center gap-2">
            <button type="button" id="decreaseStockQuantity" class="btn-dark !px-2 !py-0 rounded-full" aria-label="Decrease quantity">−</button>
            <input id="stockQuantity" name="quantity" type="number" min="0" max="4294967295" step="1" value="0" class="inp text-center" required>
            <button type="button" id="increaseStockQuantity" class="btn-dark !px-2 !py-0 rounded-full" aria-label="Increase quantity">+</button>
        </div>
        <div class="flex justify-end gap-2 mt-6">
            <button type="button" id="closeStock" class="btn-dark">Cancel</button>
            <button type="submit" class="btn">Update Stock</button>
        </div>
    </form>
</div>
<?php endif; ?>
<?php if ($ok): ob_start(); ?><script>
    let cat = 'All';
    const $ = id => document.getElementById(id);
    const stockUpdateToast = $('stockUpdateToast');
    if (stockUpdateToast) setTimeout(() => stockUpdateToast.remove(), 3000);
    function filter() {
        const q = $('search').value.toLowerCase(); let n = 0;
        const rows = document.querySelectorAll('#prod tr[data-cat]');
        rows.forEach(r => {
            const show = (cat === 'All' || r.dataset.cat === cat) && r.dataset.name.includes(q);
            r.classList.toggle('hidden', !show); if (show) n++;
        });
        $('cats').hidden = rows.length === 0;
        $('none').querySelector('td').textContent = rows.length === 0
            ? 'No products have been added yet.'
            : 'No products match. Try another category or search.';
        $('none').classList.toggle('hidden', n > 0);
    }
    const modal = $('modal');
    const categorySelect = $('pc');
    const newCategoryWrap = $('newCategoryWrap');
    const newCategoryInput = $('newCategory');
    const weightWrap = $('weightWrap');
    const weightInput = $('productWeight');
    function refreshAddCategoryOptions() {
        if (!categorySelect) return;
        const selected = categorySelect.value;
        while (categorySelect.options.length > 2) categorySelect.remove(1);
        getCats().forEach(category => categorySelect.add(new Option(category, category), categorySelect.options[1]));
        categorySelect.value = selected;
        if (categorySelect.value !== selected) categorySelect.value = '';
    }
    function updateCategoryInput() {
        const isNew = categorySelect.value === '__new_category__';
        newCategoryWrap.classList.toggle('hidden', !isNew);
        newCategoryInput.disabled = !isNew;
        newCategoryInput.required = isNew;
        if (!isNew) newCategoryInput.value = '';
    }
    function updateWeightInput() {
        const usesKg = $('usesKg').checked;
        weightWrap.classList.toggle('hidden', !usesKg);
        weightInput.disabled = !usesKg;
        weightInput.required = usesKg;
        if (!usesKg) weightInput.value = '';
    }
    function renderTabs() {
        const box = $('cats');
        box.innerHTML = '';
        const categories = [...new Set(Array.from(document.querySelectorAll('#prod tr[data-cat]'), row => row.dataset.cat))];
        box.style.display = categories.length ? 'flex' : 'none';
        if (!categories.includes(cat)) cat = 'All';
        ['All', ...categories].forEach(category => {
            const group = document.createElement('span');
            group.className = 'inline-flex items-center gap-1';
            const tab = document.createElement('button');
            tab.type = 'button';
            tab.textContent = category;
            tab.className = 'pill' + (category === cat ? ' on' : '');
            tab.onclick = () => {
                cat = category;
                renderTabs();
                filter();
                updateCategoryDeleteButton();
            };
            group.appendChild(tab);
            box.appendChild(group);
        });
    }
    function updateCategoryDeleteButton() {
        const button = $('deleteCategory');
        if (!button) return;
        button.classList.toggle('hidden', cat === 'All');
        button.textContent = cat === 'All' ? 'Delete Category' : `Delete ${cat}`;
    }
    renderTabs();
    updateCategoryDeleteButton();
    if ($('deleteCategory')) $('deleteCategory').onclick = () => {
        const category = cat;
        const inUse = Array.from(document.querySelectorAll('#prod tr[data-cat]'))
            .some(row => row.dataset.cat.toLowerCase() === category.toLowerCase());
        if (inUse) {
            toast('Delete products in this category before deleting the category.');
            return;
        }
        if (!confirm(`Delete the "${category}" category?`)) return;
        try {
            deleteCat(category);
        } catch (error) {
            console.error('Unable to save deleted category.', error);
            toast('Could not delete the category. Browser storage is unavailable.');
            return;
        }
        cat = 'All';
        renderTabs();
        updateCategoryDeleteButton();
        fillCategorySelects();
        toast('Category deleted (preview).');
    };
    $('search').oninput = filter;
    const stockModal = $('stockModal');
    const stockForm = $('stockForm');
    const stockQuantity = $('stockQuantity');
    function adjustStockQuantity(amount) {
        const quantity = Number.parseInt(stockQuantity.value, 10);
        const current = Number.isInteger(quantity) && quantity >= 0 ? quantity : 0;
        stockQuantity.value = String(Math.min(4294967295, Math.max(0, current + amount)));
    }
    let activeStockButton = null;
    function closeStockModal() {
        stockModal.classList.add('hidden');
        stockModal.classList.remove('flex');
        if (activeStockButton) activeStockButton.focus();
        activeStockButton = null;
    }
    $('prod').addEventListener('click', e => {
        const stockButton = e.target.closest('button[data-edit-stock]');
        if (stockButton) {
            activeStockButton = stockButton;
            stockForm.action = stockButton.dataset.stockUrl;
            $('stockProductName').textContent = stockButton.dataset.productName;
            stockQuantity.value = stockButton.dataset.stock;
            stockModal.classList.remove('hidden');
            stockModal.classList.add('flex');
            stockQuantity.focus();
            return;
        }
        const button = e.target.closest('button[data-delete-product]');
        if (!button) return;
        if (!confirm('Delete this product?')) return;
        const productRow = button.closest('tr');
        productRow.remove();
        renderTabs();
        updateCategoryDeleteButton();
        filter();
        toast('Product deleted (preview).');
    });
    if (stockModal) {
        $('decreaseStockQuantity').onclick = () => adjustStockQuantity(-1);
        $('increaseStockQuantity').onclick = () => adjustStockQuantity(1);
        $('closeStock').onclick = closeStockModal;
        stockModal.onclick = e => { if (e.target === stockModal) closeStockModal(); };
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && !stockModal.classList.contains('hidden')) closeStockModal();
        });
    }
    if (modal) {
    function openModal(v) {
        modal.classList.toggle('hidden', !v);
        modal.classList.toggle('flex', v);
        if (v) {
            refreshAddCategoryOptions();
            $('pn').focus();
        }
    }
    refreshAddCategoryOptions();
    categorySelect.onchange = updateCategoryInput;
    $('usesKg').onchange = updateWeightInput;
    updateCategoryInput();
    updateWeightInput();
    $('openAdd').onclick = () => openModal(true);
    $('closeAdd').onclick = () => openModal(false);
    modal.onclick = e => { if (e.target === modal) openModal(false); };
    document.addEventListener('keydown', e => { if (e.key === 'Escape') openModal(false); });
    }
</script>
<?php $extraScript = ob_get_clean(); endif; ?>
<?php include resource_path('views/layouts/footer.php'); ?>
