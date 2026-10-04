<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Kulaza Pet Supplies</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        input::-ms-reveal,input::-ms-clear{display:none}
        .login-logo{display:block;width:96px;height:auto;margin:0 auto 2rem}
    </style>
</head>
<body class="text-white min-h-screen flex items-center justify-center p-4" style="background:radial-gradient(circle at 20% 15%,#14210f 0,#0a0d0a 45%,#050605 100%)">
    <form method="POST" action="#" class="w-full max-w-lg rounded-2xl px-12 pt-8 pb-12" style="background:#171c15;border:2px solid #dfee3a">
        <?= csrf_field() ?>
        <img src="<?= e(asset('images/logo-icon.png')) ?>" alt="Kulaza Pet Supplies" class="login-logo">
        <label for="username" class="block text-sm text-gray-300 mb-1">Username / email</label>
        <input id="username" type="text" name="username" autocomplete="off" class="w-full bg-transparent border-b border-gray-500 focus:border-green-500 mb-8 py-2.5 text-base outline-none">
        <label for="password" class="block text-sm text-gray-300 mb-1">Password</label>
        <div class="relative mb-10">
            <input id="password" type="password" name="password" class="w-full bg-transparent border-b border-gray-500 focus:border-green-500 py-2.5 pr-10 text-base outline-none">
            <button type="button" id="togglePw" aria-label="Show password" class="absolute right-0 top-1/2 -translate-y-1/2 p-1"></button>
        </div>
        
        <a id="loginBtn" href="<?= e(route('dashboard')) ?>" data-admin="<?= e(route('dashboard')) ?>" data-staff="<?= e(route('dashboard', ['role' => 'staff'])) ?>" class="block w-3/4 mx-auto text-center bg-[#2e7d32] rounded-lg py-3 text-base font-semibold">Log in</a>
    </form>
<script>
        // White show/hide password icon
        const svg = p => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + p + '</svg>';
        const EYE = svg('<path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/>');
        const EYE_OFF = svg('<path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/>');
        const lb = document.getElementById('loginBtn');
        lb.onclick = () => { const u = document.getElementById('username').value.trim().toLowerCase(); lb.href = (u === '' || u === 'admin') ? lb.dataset.admin : lb.dataset.staff; };
        const pw = document.getElementById('password'), tg = document.getElementById('togglePw');
        tg.innerHTML = EYE;
        tg.onclick = () => { const show = pw.type === 'password'; pw.type = show ? 'text' : 'password'; tg.innerHTML = show ? EYE_OFF : EYE; tg.setAttribute('aria-label', show ? 'Hide password' : 'Show password'); };
    </script>
</body>
</html>
