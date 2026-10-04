<?php // Shared bottom part of every page: closes the layout and loads the shared scripts. ?>
    </main>
<div id="toast" class="fixed bottom-6 right-6 bg-[#2e7d32] px-4 py-2 rounded-lg text-sm hidden"></div>
<script>
    const peso = n => '₱' + Number(n).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.querySelectorAll('input[type=date],input[type=month]').forEach(i => {
        const w = document.createElement('span'); w.className = 'dw';
        i.parentNode.insertBefore(w, i); w.appendChild(i);
        w.insertAdjacentHTML('beforeend', '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>');
    });
    // ---- Categories: starting list from the server + any the admin typed (TEMPORARY, kept in this browser only) ----
    const BASE_CATS = <?= json_encode(sample_categories()) ?>;
    let memCats = [];
    const DELETED_CATS_KEY = 'kulaza_deleted_cats';
    function getCats() {
        let extra = memCats;
        try { extra = JSON.parse(localStorage.getItem('kulaza_cats') || '[]'); } catch (e) {}
        const out = BASE_CATS.slice();
        extra.forEach(c => { if (!out.some(x => x.toLowerCase() === c.toLowerCase())) out.push(c); });
        let deleted = [];
        try { deleted = JSON.parse(localStorage.getItem(DELETED_CATS_KEY) || '[]'); } catch (e) {}
        return out.filter(c => !deleted.some(x => x.toLowerCase() === c.toLowerCase()));
    }
    function addCat(name) {   // returns the category name to use (existing spelling if it already exists)
        name = (name || '').trim().replace(/\s+/g, ' ');
        if (!name) return '';
        const found = getCats().find(c => c.toLowerCase() === name.toLowerCase());
        if (found) return found;
        let deleted = [];
        let extra = [];
        try { deleted = JSON.parse(localStorage.getItem(DELETED_CATS_KEY) || '[]'); } catch (e) {}
        try { extra = JSON.parse(localStorage.getItem('kulaza_cats') || '[]'); } catch (e) {}
        const previous = [...BASE_CATS, ...extra, ...memCats].find(c => c.toLowerCase() === name.toLowerCase());
        if (previous) {
            deleted = deleted.filter(c => c.toLowerCase() !== name.toLowerCase());
            try { localStorage.setItem(DELETED_CATS_KEY, JSON.stringify(deleted)); } catch (e) {}
            return previous;
        }
        memCats.push(name);
        try { const x = JSON.parse(localStorage.getItem('kulaza_cats') || '[]'); x.push(name); localStorage.setItem('kulaza_cats', JSON.stringify(x)); } catch (e) {}
        return name;
    }
    function deleteCat(name) {
        let deleted = [];
        try { deleted = JSON.parse(localStorage.getItem(DELETED_CATS_KEY) || '[]'); } catch (e) {}
        if (!deleted.some(c => c.toLowerCase() === name.toLowerCase())) deleted.push(name);
        localStorage.setItem(DELETED_CATS_KEY, JSON.stringify(deleted));
    }
    function fillCategorySelects() {   // every <select data-cats> = "All Categories" + all categories
        document.querySelectorAll('select[data-cats]').forEach(sel => {
            const keep = sel.value;
            while (sel.options.length > 1) sel.remove(1);
            getCats().forEach(c => sel.add(new Option(c, c)));
            sel.value = keep;
        });
    }
    function makeTabs(box, current, onPick) {   // category tabs: All + every category
        box.innerHTML = '';
        ['All', ...getCats()].forEach(c => {
            const b = document.createElement('button'); b.type = 'button'; b.textContent = c; b.className = 'pill' + (c === current ? ' on' : '');
            b.onclick = () => onPick(c); box.appendChild(b);
        });
    }
    fillCategorySelects();
    function toast(m) { const t = document.getElementById('toast'); t.textContent = m; t.classList.remove('hidden'); setTimeout(() => t.classList.add('hidden'), 2200); }
</script>
<?= $extraScript ?? '' ?>
</body>
</html>
