<div>
    <!-- Simplicity is the essence of happiness. - Cedric Bledsoe -->
</div>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Akun Investasi') - Uqi</title>
    <script>
        document.documentElement.dataset.theme = localStorage.getItem('portfolio-theme') === 'light' ? 'light' : 'dark';
    </script>
    <style>
        :root { color-scheme:dark; --bg:#0b1110; --panel:#111a17; --panel-2:#17221e; --text:#f2f7f4; --muted:#a0b0a7; --border:rgba(191,224,205,.12); --accent:#37c997; --red:#ff7c91; --shadow:0 24px 70px rgba(0,0,0,.28); }
        html[data-theme="light"] { color-scheme:light; --bg:#f2f7f4; --panel:#fff; --panel-2:#f6faf8; --text:#15231c; --muted:#607369; --border:rgba(49,83,62,.14); --accent:#087b59; --red:#d12d4d; --shadow:0 20px 55px rgba(36,67,48,.12); }
        * { box-sizing:border-box; }
        body { min-height:100vh; display:grid; place-items:center; margin:0; padding:20px; color:var(--text); background:radial-gradient(circle at 75% 15%,rgba(22,167,122,.1),transparent 25rem),var(--bg); font:15px/1.55 Inter,Segoe UI,sans-serif; }
        main { width:min(100%,460px); padding:30px; border:1px solid var(--border); border-radius:22px; background:linear-gradient(145deg,color-mix(in srgb,var(--accent) 5%,var(--panel)),var(--panel) 40%); box-shadow:var(--shadow); }
        .auth-brand { display:flex; align-items:center; gap:11px; margin-bottom:28px; color:var(--text); font-weight:850; letter-spacing:.08em; }
        .auth-mark { display:grid; place-items:center; width:42px; height:42px; border-radius:14px; color:#06291d; background:linear-gradient(145deg,#65e6b5,#12a87a); font-size:20px; }
        .auth-brand small { display:block; margin-top:3px; color:var(--muted); font-size:11px; font-weight:500; letter-spacing:0; }
        h1 { margin:0 0 8px; font-size:26px; letter-spacing:-.035em; }
        p { color:var(--muted); }
        form { display:grid; gap:14px; margin-top:20px; }
        label { display:grid; gap:6px; color:var(--muted); font-size:13px; }
        input { width:100%; padding:11px 12px; color:var(--text); background:var(--panel-2); border:1px solid var(--border); border-radius:10px; font:inherit; }
        input:focus { outline:0; border-color:var(--accent); box-shadow:0 0 0 3px rgba(22,167,122,.14); }
        button { padding:11px 14px; border:0; border-radius:10px; color:white; background:linear-gradient(135deg,#159c72,#087a58); font:inherit; font-weight:800; cursor:pointer; }
        .notice { padding:12px; border:1px solid var(--border); border-radius:10px; background:var(--panel-2); }
        .errors { color:var(--red); }
        a { color:var(--accent); }
        button:focus-visible,a:focus-visible,input:focus-visible { outline:3px solid color-mix(in srgb,var(--accent) 65%,white); outline-offset:3px; }
        @media(max-width:480px) { main { padding:22px; } }
    </style>
</head>
<body>
    <main>
        <div class="auth-brand"><span class="auth-mark" aria-hidden="true">U</span><span>UQI INVEST<small>Ruang belajar &amp; simulasi saham</small></span></div>
        @if($errors->any())
            <div class="notice errors"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        @yield('content')
    </main>
</body>
</html>
