<?php
$pageTitle = 'Products';
include resource_path('views/layouts/header.php');
?>
<?php if (!$ok): ?>
<div class="card">You do not have access to this page.</div>
<?php else: ?><?php  $cats = sample_categories();  ?>
<div class="flex flex-wrap gap-2 mb-4" id="cats"></div>
<div class="card" style="min-height:26rem">
    <div class="flex gap-3 mb-4">
        <input type="text" id="search" placeholder="Search product..." class="inp">
        <?php if(!is_staff()): ?>
            <button type="button" id="deleteCategory" class="btn-red whitespace-nowrap hidden">Delete Category</button>
            <button type="button" id="openAdd" class="btn whitespace-nowrap">+ Add Product</button>
        <?php endif; ?>
    </div>
    <div class="overflow-x-auto">
        <table class="tbl" style="table-layout:fixed;min-width:480px">
            <colgroup><col style="width:30%"><col style="width:23%"><col style="width:17%"><?php if(!is_staff()): ?><col style="width:15%"><col style="width:15%"><?php else: ?><col style="width:30%"><?php endif; ?></colgroup>
            <tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><?php if(!is_staff()): ?><th>Actions</th><?php endif; ?></tr>
            <tbody id="prod">
                <?php foreach($products as $p): ?>
                    <tr data-cat="<?= e($p['category']) ?>" data-name="<?= e(strtolower($p['name'])) ?>">
                        <td><span class="font-semibold"><?= e($p['name']) ?></span><?php if (!empty($p['weight'])): ?><span class="block text-xs text-gray-400"><?= e($p['weight']) ?> kg</span><?php endif; ?></td><td><?= e($p['category']) ?></td>
                        <td>₱<?= e(number_format($p['price'], 2)) ?></td><td><?= e(number_format($p['stock'])) ?></td>
                        <?php if(!is_staff()): ?><td><button type="button" data-delete-product class="btn-red">Delete</button></td><?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                <tr id="none" class="hidden"><td colspan="<?= is_staff() ? 4 : 5 ?>" class="text-gray-400 text-center" style="padding:5rem 0">No products match. Try another category or search.</td></tr>
            </tbody>
        </table>
    </div>
</div>


<?php if(!is_staff()): ?>
<div id="modal" class="hidden fixed inset-0 z-50 items-center justify-center p-4" style="background:rgba(0,0,0,.7)">
    <form method="POST" action="#" id="addForm" class="card w-full max-w-md" style="background:#171c15;padding:24px">
        <?= csrf_field() ?>
        <h2 class="text-lg font-bold mb-4">Add Product</h2>
        <div class="space-y-4">
            <div><label class="lbl" for="pn">Product name</label><input id="pn" name="name" class="inp" placeholder="Enter product name" autocomplete="off" required></div>
            <div><label class="lbl" for="pc">Category</label>
                <select id="pc" name="category" class="inp" required>
                    <option value="">Choose a category</option>
                    <option value="__new_category__">+ Add new category</option>
                </select>
            </div>
            <div id="newCategoryWrap" class="hidden">
                <label class="lbl" for="newCategory">New category</label>
                <input id="newCategory" name="new_category" class="inp" placeholder="Enter new category" autocomplete="off" disabled>
            </div>
            <div>
                <label class="flex items-center gap-2 text-sm text-gray-300">
                    <input id="usesKg" type="checkbox" class="accent-green-600">
                    Does this product use kg?
                </label>
            </div>
            <div id="weightWrap" class="hidden">
                <label class="lbl" for="productWeight">Weight (kg)</label>
                <input id="productWeight" type="number" min="0.01" step="0.01" class="inp" placeholder="Enter weight in kg" disabled>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="lbl" for="pp">Price</label><input id="pp" name="price" type="number" min="0" step="0.01" class="inp" placeholder="Enter price" required></div>
                <div><label class="lbl" for="pk">Stock</label><input id="pk" name="stock" type="number" min="0" class="inp" placeholder="Enter quantity" required></div>
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
<?php if ($ok): ob_start(); ?><script>
    let cat = 'All';
    const $ = id => document.getElementById(id);
    function filter() {
        const q = $('search').value.toLowerCase(); let n = 0;
        document.querySelectorAll('#prod tr[data-cat]').forEach(r => {
            const show = (cat === 'All' || r.dataset.cat === cat) && r.dataset.name.includes(q);
            r.classList.toggle('hidden', !show); if (show) n++;
        });
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
        ['All', ...getCats()].forEach(category => {
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
    $('prod').addEventListener('click', e => {
        const button = e.target.closest('button[data-delete-product]');
        if (!button) return;
        if (!confirm('Delete this product?')) return;
        button.closest('tr').remove();
        filter();
        toast('Product deleted (preview).');
    });
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
    // PREVIEW ONLY: adds the row in the browser. Back-end: delete this handler so the form posts to the server.
    $('addForm').onsubmit = e => {
        e.preventDefault();
        const f = new FormData(e.target);
        const selectedCategory = f.get('category');
        const category = selectedCategory === '__new_category__'
            ? addCat(f.get('new_category'))
            : selectedCategory;
        if (!category) {
            toast('Choose or enter a category.');
            return;
        }
        const weight = $('usesKg').checked ? Number(weightInput.value) : null;
        if ($('usesKg').checked && (!Number.isFinite(weight) || weight <= 0)) {
            toast('Enter a weight greater than zero.');
            return;
        }
        const tr = document.createElement('tr');
        tr.dataset.cat = category; tr.dataset.name = f.get('name').toLowerCase();
        const nameCell = document.createElement('td');
        const productName = document.createElement('span');
        productName.className = 'font-semibold';
        productName.textContent = f.get('name');
        nameCell.appendChild(productName);
        if (weight !== null) {
            const weightLabel = document.createElement('span');
            weightLabel.className = 'block text-xs text-gray-400';
            weightLabel.textContent = weight + ' kg';
            nameCell.appendChild(weightLabel);
        }
        tr.appendChild(nameCell);
        [category, peso(f.get('price')), f.get('stock')].forEach(value => {
            const cell = document.createElement('td');
            cell.textContent = value;
            tr.appendChild(cell);
        });
        const actionCell = document.createElement('td');
        actionCell.innerHTML = '<button type="button" data-delete-product class="btn-red">Delete</button>';
        tr.appendChild(actionCell);
        $('prod').insertBefore(tr, $('none'));
        e.target.reset();
        categorySelect.value = '';
        updateCategoryInput();
        updateWeightInput();
        openModal(false);
        renderTabs();
        fillCategorySelects();
        refreshAddCategoryOptions();
        filter();
        toast('Product added (preview).');
    };
    }
</script>
<?php $extraScript = ob_get_clean(); endif; ?>
<?php include resource_path('views/layouts/footer.php'); ?>
