<?php
// Shared top part of every page (head, sidebar, title). A page sets $pageTitle, then includes this file.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> - Kulaza Pet Supplies</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;900&display=swap" rel="stylesheet">
    <style>
        .card{background:#141914;border:1px solid #1f271f;border-radius:14px;padding:18px}
        .brand-lockup{display:flex;align-items:center;gap:8px;width:100%}
        .brand-icon{display:block;flex:none;width:42px;height:auto}
        .brand-wordmark{display:block;flex:1;min-width:0;height:auto;overflow:visible}
        .brand-wordmark text{font-family:'Inter','Montserrat',Arial,sans-serif;fill:#fff}
        input,select,textarea{color-scheme:dark}
        /* white dropdown arrow */
        select.inp{appearance:none;-webkit-appearance:none;background-image:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='m6 9 6 6 6-6'/></svg>");background-repeat:no-repeat;background-position:right 10px center;padding-right:34px}
        /* white calendar icon (the browser's own icon is hidden but still opens the date picker) */
        .dw{position:relative;display:block}
        .dw input{padding-right:38px}
        .dw input::-webkit-calendar-picker-indicator{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer}
        .dw svg{position:absolute;right:10px;top:50%;transform:translateY(-50%);pointer-events:none}
        .inp{background:#0b0e0b;border:1px solid #1f271f;border-radius:8px;padding:8px 12px;color:#fff;width:100%}
        .btn{background:#2e7d32;border-radius:8px;padding:8px 18px;font-weight:600;color:#fff;display:inline-block;text-align:center}
        .btn-ghost{background:#142014;border:1px solid #1f2f1f;border-radius:8px;padding:8px 18px;color:#2e7d32;font-weight:600;display:block;text-align:center}
        .btn-dark{background:#0b0e0b;border:1px solid #1f271f;border-radius:8px;padding:8px 18px;color:#fff;display:inline-block;text-align:center}
        .btn-red{background:#4a1a1f;border-radius:8px;padding:4px 12px;color:#fff}
        .lbl{font-size:12px;color:#9aa39a;display:block;margin-bottom:4px}
        .pill{padding:4px 12px;border-radius:999px;font-size:12px;cursor:pointer;background:#141914;border:1px solid #1f271f}
        .pill.on{background:#2e7d32;border-color:#2e7d32}
        .tbl{width:100%;font-size:13px}.tbl th{text-align:left;color:#9aa39a;font-weight:500;padding:8px 6px}
        .tbl td{padding:10px 6px;border-top:1px solid #1f271f}
    </style>
</head>
<body class="bg-[#0b0e0b] text-white flex min-h-screen">
    <aside class="w-56 bg-black p-4 shrink-0 flex flex-col sticky top-0 h-screen self-start overflow-y-auto">
        <div class="pb-4 mb-4 border-b border-gray-700 flex justify-center">
            <div class="brand-lockup" role="img" aria-label="Kulaza Pet Supplies">
                <img src="<?= e(asset('images/logo-icon.png')) ?>" alt="" class="brand-icon">
                <svg class="brand-wordmark" viewBox="0 0 681 209" aria-hidden="true">
                    <text x="0" y="90" font-size="103" font-weight="900" textLength="681" lengthAdjust="spacing">KULAZA</text>
                    <rect x="0" y="112" width="681" height="3" fill="#fff"/>
                    <text x="0" y="195" font-size="90" font-weight="500" textLength="681" lengthAdjust="spacing">PET SUPPLIES</text>
                </svg>
            </div>
        </div>
        <?php 
            // Icons (Lucide-style outlines), all drawn in white
            $icons = [
                'dashboard'    => '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>',
                'products'     => '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
                'sales.create' => '<path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M14 8H8"/><path d="M16 12H8"/><path d="M13 16H8"/>',
                'transactions' => '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/>',
                'reports'      => '<path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/>',
                'monthly'      => '<path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="m9 16 2 2 4-4"/>',
                'staff'        => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
                'logout'       => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
            ];
            $isStaff = is_staff();   // staff only see: Dashboard, Products (view only), Record Sale, Transaction History
            $staffRoutes = ['dashboard','products','sales.create','transactions'];
            $nav = ['dashboard'=>'Dashboard','products'=>'Products','sales.create'=>'Record Sale','transactions'=>'Transaction History','reports'=>'Sales Reports','monthly'=>'Monthly Records','staff'=>'Staff Accounts'];
         ?>
        <?php foreach($nav as $route => $label): ?>
            <?php if(!$isStaff || in_array($route, $staffRoutes)): ?>
            <a href="<?= e(route($route, $isStaff ? ['role' => 'staff'] : [])) ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm mb-1 text-white <?= e(request()->routeIs($route) ? 'bg-[#1a2a1a]' : 'hover:bg-[#141914]') ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0"><?= $icons[$route] ?></svg>
                <span><?= e($label) ?></span>
            </a>
            <?php endif; ?>
        <?php endforeach; ?>
        <a href="<?= e(route('login')) ?>" class="flex items-center gap-3 px-3 py-2 mt-auto rounded-lg text-sm text-white hover:bg-[#141914]">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0"><?= $icons['logout'] ?></svg>
            <span>Log out</span>
        </a>
    </aside>
    <main class="flex-1 p-8">
        <h1 class="text-2xl font-bold"><?= e($pageTitle) ?></h1>
        <p class="text-xs text-gray-400 mb-6"><?= e(now()->format('l, F d, Y')) ?></p>
<?php
// Staff can only open these pages (pages show a no-access message otherwise).
$ok = !$isStaff;
foreach ($staffRoutes as $r) {
    if (request()->routeIs($r)) { $ok = true; }
}
$extraScript = '';
?>