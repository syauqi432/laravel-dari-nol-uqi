<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Investasi Saham') - Uqi</title>
    <script>
        document.documentElement.dataset.theme = localStorage.getItem('portfolio-theme') === 'light' ? 'light' : 'dark';
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { color-scheme:dark; --bg:#0b0b0b; --panel:#151515; --panel-2:#1c1c1c; --text:#f2f2f2; --muted:#929292; --border:rgba(255,255,255,.09); --accent:#16a77a; --green:#18ad80; --red:#fb7185; --shadow:0 18px 48px rgba(0,0,0,.18); }
        html[data-theme="light"] { color-scheme:light; --bg:#f3f8f5; --panel:#fff; --panel-2:#f6faf8; --text:#14231c; --muted:#61766b; --border:rgba(69,99,81,.16); --accent:#047857; --green:#059669; --red:#e11d48; --shadow:0 14px 36px rgba(36,67,48,.08); }
        * { box-sizing:border-box; }
        body { margin:0; padding:24px 20px 104px; background:radial-gradient(circle at 85% 5%,rgba(22,167,122,.08),transparent 26rem),var(--bg); color:var(--text); font:14px/1.55 Inter,Segoe UI,sans-serif; transition:background .2s,color .2s; }
        a { color:var(--accent); text-decoration:none; }
        a:hover { color:var(--green); }
        .app-wrap { max-width:1200px; margin:auto; }
        .page-head,.panel,.metric { background:var(--panel); border:1px solid var(--border); border-radius:14px; box-shadow:var(--shadow); }
        .page-head { padding:22px; margin-bottom:16px; display:flex; justify-content:space-between; align-items:center; gap:16px; background:linear-gradient(125deg,rgba(22,167,122,.08),transparent 60%),var(--panel); }
        .page-head h1 { margin:0; font-size:24px; letter-spacing:-.035em; }
        .muted,.caption { color:var(--muted); }
        .panel { padding:20px; margin-bottom:16px; }
        .panel h2,.panel h3 { margin:0 0 12px; letter-spacing:-.02em; }
        .metrics { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:12px; }
        .metric { padding:17px; background:var(--panel-2); transition:transform .18s,border-color .18s; }
        a.metric:hover { transform:translateY(-2px); border-color:rgba(22,167,122,.45); }
        .metric strong { display:block; font-size:20px; margin-top:5px; }
        .grid-2 { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; }
        .grid-3 { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }
        .row { display:flex; flex-wrap:wrap; align-items:center; gap:10px; }
        label { display:grid; gap:5px; color:var(--muted); font-size:12px; }
        input,select,textarea,button { font:inherit; }
        input,select,textarea { width:100%; min-width:0; color:var(--text); background:var(--panel-2); border:1px solid var(--border); border-radius:10px; padding:10px 12px; }
        input:focus,select:focus,textarea:focus { outline:0; border-color:var(--accent); box-shadow:0 0 0 3px color-mix(in srgb,var(--accent) 18%,transparent); }
        textarea { resize:vertical; min-height:90px; }
        button,.button { display:inline-flex; justify-content:center; align-items:center; gap:7px; color:#fff; background:linear-gradient(135deg,#159c72,#087a58); border:1px solid transparent; border-radius:10px; padding:10px 14px; font-weight:750; cursor:pointer; transition:filter .15s,transform .15s; }
        button:hover,.button:hover { filter:brightness(1.08); transform:translateY(-1px); color:#fff; }
        .button.secondary,button.secondary { color:var(--text); background:var(--panel-2); border:1px solid var(--border); }
        .button.green,button.green { color:#fff; background:#087a58; }
        .button.red,button.red { color:#fff; background:#be123c; }
        .notice { padding:12px 14px; border-radius:10px; margin-bottom:14px; background:color-mix(in srgb,var(--accent) 9%,var(--panel)); border:1px solid color-mix(in srgb,var(--accent) 30%,transparent); }
        .notice.success { background:rgba(52,211,153,.1); border-color:rgba(52,211,153,.25); }
        .notice.error { background:rgba(251,113,133,.1); border-color:rgba(251,113,133,.25); }
        .table-wrap { overflow:auto; }
        table { border-collapse:collapse; width:100%; min-width:620px; }
        th,td { text-align:left; padding:12px; border-bottom:1px solid var(--border); }
        th { color:var(--muted); background:var(--panel-2); text-transform:uppercase; font-size:11px; letter-spacing:.045em; }
        tbody tr:hover { background:color-mix(in srgb,var(--accent) 5%,transparent); }
        .badge { display:inline-block; padding:4px 9px; border-radius:99px; background:color-mix(in srgb,var(--accent) 12%,transparent); color:var(--accent); font-size:12px; font-weight:700; }
        .subnav { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:14px; }
        .subnav a { padding:8px 11px; border:1px solid var(--border); border-radius:9px; color:var(--muted); }
        .subnav a.active { background:color-mix(in srgb,var(--accent) 12%,transparent); color:var(--accent); border-color:color-mix(in srgb,var(--accent) 36%,transparent); }
        .empty { color:var(--muted); text-align:center; padding:24px 12px; }
        .issuer-tabs { position:sticky; top:8px; z-index:10; display:flex; overflow-x:auto; gap:7px; padding:8px; margin-bottom:14px; border:1px solid var(--border); border-radius:13px; background:color-mix(in srgb,var(--bg) 94%,transparent); backdrop-filter:blur(16px); }
        .issuer-tabs button { flex:0 0 auto; white-space:nowrap; color:var(--muted); background:transparent; border:1px solid transparent; }
        .issuer-tabs button[aria-selected="true"] { color:var(--accent); background:color-mix(in srgb,var(--accent) 12%,transparent); border-color:color-mix(in srgb,var(--accent) 28%,transparent); }
        .issuer-overview { display:grid; grid-template-columns:minmax(0,1.7fr) minmax(250px,1fr); gap:18px; }
        .issuer-profile { padding:16px; border:1px solid var(--border); border-radius:12px; background:var(--panel-2); }
        .issuer-profile h2 { margin:0 0 12px; }
        .issuer-profile dl { display:grid; gap:0; margin:0; }
        .issuer-profile dl div { display:flex; justify-content:space-between; gap:12px; padding:10px 0; border-bottom:1px solid var(--border); }
        .issuer-profile dt { color:var(--muted); }
        .issuer-profile dd { margin:0; text-align:right; font-weight:700; }
        .issuer-chart { height:290px; }
        .section-heading { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:12px; }
        .section-heading h2,.section-heading p { margin:0; }
        .chart-style { min-width:115px; }
        .quote-summary { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:8px; margin-top:12px; }
        .quote-summary div { display:grid; gap:3px; padding:10px; background:var(--panel-2); border:1px solid var(--border); border-radius:9px; }
        .quote-summary strong { font-size:13px; }
        .positive,.passed { color:var(--green)!important; }
        .negative,.not-passed { color:var(--red)!important; }
        .data-badge { padding:6px 9px; border-radius:99px; color:#fbbf24; background:rgba(251,191,36,.1); font-size:12px; }
        .market-news-list { display:grid; gap:9px; }
        .market-news-item { padding:11px 12px; border:1px solid var(--border); border-radius:12px; background:var(--panel-2); }
        .market-news-item a { color:var(--text); font-weight:650; }
        .market-news-meta { display:flex; flex-wrap:wrap; gap:8px; margin-top:5px; color:var(--muted); font-size:12px; }
        .period-tabs { margin:10px 0; }
        .period-button.active { color:var(--accent); background:color-mix(in srgb,var(--accent) 12%,transparent); border-color:color-mix(in srgb,var(--accent) 35%,transparent); }
        .entry-form { margin-top:14px; padding:12px 14px; border:1px solid var(--border); border-radius:10px; }
        .entry-form summary { color:var(--accent); cursor:pointer; font-weight:700; }
        .entry-form form { margin-top:14px; }
        .score-card { display:grid; gap:8px; padding:18px; border:1px solid color-mix(in srgb,var(--accent) 28%,transparent); border-radius:12px; background:color-mix(in srgb,var(--accent) 7%,var(--panel)); }
        .score-number { color:var(--green); font-size:30px; }
        .score-number span { color:var(--muted); font-size:17px; }
        .risk-profile-form { display:grid; gap:12px; margin:18px 0; }
        .risk-question { min-width:0; margin:0; padding:14px; border:1px solid var(--border); border-radius:12px; }
        .risk-question legend { max-width:100%; padding:0 6px; color:var(--text); font-weight:700; }
        .risk-options { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; margin-top:8px; }
        .risk-option { display:flex; align-items:flex-start; gap:9px; padding:10px 12px; border:1px solid var(--border); border-radius:10px; color:var(--text); background:var(--panel-2); cursor:pointer; }
        .risk-option input[type="radio"] { flex:0 0 auto; width:16px; min-width:16px; margin:3px 0 0; padding:0; accent-color:var(--accent); }
        .risk-profile-result { margin:14px 0; padding:16px; border:1px solid color-mix(in srgb,var(--accent) 32%,transparent); border-radius:12px; background:color-mix(in srgb,var(--accent) 8%,var(--panel)); }
        .risk-profile-result h3,.risk-profile-result p { margin:5px 0 0; }
        .risk-error { margin-top:8px; color:var(--red); font-size:12px; }
        .score-checks { margin-top:12px; }
        .check-state { font-size:12px; font-weight:800; }
        .check-state.unknown { color:#fbbf24; }
        .post-card { margin-top:10px; }
        .tab-panel[hidden] { display:none; }
        .account-bar { display:flex; justify-content:flex-end; align-items:center; gap:12px; margin-bottom:12px; color:var(--muted); }
        .account-bar form { margin:0; }
        .account-bar button { padding:6px 10px; }
        .theme-toggle { padding:6px 10px; color:var(--text); background:var(--panel); border:1px solid var(--border); border-radius:9px; font-size:12px; }
        .theme-toggle:hover { color:var(--text); transform:none; }
        .bottom-nav { position:fixed; z-index:20; bottom:0; left:0; right:0; display:flex; justify-content:center; gap:6px; padding:10px 12px calc(10px + env(safe-area-inset-bottom)); background:color-mix(in srgb,var(--bg) 92%,transparent); border-top:1px solid var(--border); backdrop-filter:blur(16px); }
        .bottom-nav a { width:min(190px,20vw); padding:7px 8px; border-radius:10px; color:var(--muted); text-align:center; font-size:12px; }
        .bottom-nav a span { display:grid; place-items:center; width:34px; height:34px; margin:0 auto 4px; border-radius:50%; background:var(--panel-2); font-size:18px; transition:background .15s,color .15s; }
        .bottom-nav a.active { color:var(--accent); background:color-mix(in srgb,var(--accent) 10%,transparent); }
        .bottom-nav a.active span { color:var(--accent); background:color-mix(in srgb,var(--accent) 17%,var(--panel-2)); }
        @media(max-width:700px) { body { padding:12px 12px 92px; } .page-head { align-items:flex-start; flex-direction:column; } .grid-2,.grid-3,.issuer-overview,.risk-options { grid-template-columns:1fr; } .metrics { grid-auto-columns:minmax(150px,72vw); grid-auto-flow:column; grid-template-columns:none; overflow-x:auto; padding-bottom:4px; scroll-snap-type:x mandatory; } .metric { scroll-snap-align:start; } .quote-summary { grid-template-columns:repeat(2,minmax(0,1fr)); } .issuer-chart { height:230px; } .issuer-tabs { top:0; } .bottom-nav { gap:0; } .bottom-nav a { font-size:10px; padding:6px 2px; } }
        @media(max-width:420px) { .page-head,.panel { padding:15px; } .account-bar { justify-content:space-between; gap:6px; font-size:12px; } .account-bar span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; } }
        :root { --bg:#0b1110; --panel:#111a17; --panel-2:#17221e; --text:#f2f7f4; --muted:#a0b0a7; --border:rgba(191,224,205,.12); --accent:#37c997; --green:#41d6a2; --red:#ff7c91; --shadow:0 18px 52px rgba(0,0,0,.24); }
        html[data-theme="light"] { --bg:#f2f7f4; --panel:#fff; --panel-2:#f6faf8; --text:#15231c; --muted:#607369; --border:rgba(49,83,62,.14); --accent:#087b59; --green:#07865e; --red:#d12d4d; --shadow:0 16px 40px rgba(36,67,48,.08); }
        body { padding:20px 24px 124px; background:radial-gradient(ellipse at 82% 0%,rgba(55,201,151,.12),transparent 34rem),var(--bg); font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif; }
        .app-wrap { width:calc(100% - 132px); max-width:1280px; margin:0 24px 0 108px; }
        .app-topbar { display:flex; justify-content:space-between; align-items:center; gap:18px; padding:12px 14px; margin-bottom:22px; border:1px solid var(--border); border-radius:18px; background:color-mix(in srgb,var(--panel) 90%,transparent); box-shadow:var(--shadow); backdrop-filter:blur(16px); }
        .brand { display:flex; align-items:center; gap:11px; color:var(--text); }
        .brand:hover { color:var(--text); }
        .brand-mark { display:grid; place-items:center; width:42px; height:42px; border-radius:14px; color:#06291d; background:linear-gradient(145deg,#65e6b5,#12a87a); font-size:20px; font-weight:900; box-shadow:0 8px 20px rgba(22,167,122,.24); }
        .brand-copy { display:grid; line-height:1.2; }
        .brand-copy strong { font-size:14px; letter-spacing:.08em; }
        .brand-copy strong span { color:var(--accent); }
        .brand-copy small,.account-details small { color:var(--muted); font-size:11px; }
        .account-bar { display:flex; justify-content:flex-end; align-items:center; gap:10px; margin:0; }
        .account-identity { display:flex; align-items:center; gap:9px; }
        .account-avatar { display:grid; place-items:center; width:34px; height:34px; border:1px solid color-mix(in srgb,var(--accent) 32%,transparent); border-radius:50%; color:var(--accent); background:color-mix(in srgb,var(--accent) 13%,var(--panel-2)); font-size:13px; font-weight:800; }
        .account-details { display:grid; line-height:1.25; }
        .account-details strong { max-width:130px; overflow:hidden; color:var(--text); font-size:12px; text-overflow:ellipsis; white-space:nowrap; }
        .account-bar form { margin:0; }
        .account-bar button { min-height:36px; }
        .theme-toggle { white-space:nowrap; }
        .page-head { padding:26px; margin-bottom:18px; border-radius:20px; background:linear-gradient(115deg,rgba(55,201,151,.12),transparent 58%),var(--panel); }
        .page-head h1 { font-size:clamp(24px,3vw,32px); line-height:1.15; }
        .page-head .muted { margin-top:7px; }
        .panel,.metric { border-radius:18px; }
        .panel { padding:22px; margin-bottom:18px; }
        .panel,.metric,.grid-2 > *,.grid-3 > * { min-width:0; }
        .panel h2 { font-size:18px; }
        .metrics { gap:14px; }
        .metric { min-height:94px; padding:18px; }
        .metric strong { font-size:22px; letter-spacing:-.03em; overflow-wrap:anywhere; }
        .badge,.data-badge { white-space:nowrap; }
        .quote-card { display:grid; gap:11px; min-height:150px; padding:16px; border:1px solid var(--border); border-radius:16px; color:var(--text); background:linear-gradient(145deg,color-mix(in srgb,var(--accent) 7%,var(--panel-2)),var(--panel-2) 60%); transition:transform .18s,border-color .18s; }
        .quote-card:hover { color:var(--text); border-color:color-mix(in srgb,var(--accent) 42%,var(--border)); transform:translateY(-2px); }
        .quote-card-heading { display:flex; justify-content:space-between; align-items:flex-start; gap:10px; }
        .quote-card-status.unavailable { color:#b45309; background:rgba(245,158,11,.14); }
        html:not([data-theme="light"]) .quote-card-status.unavailable { color:#fbbf24; }
        .quote-card-identity { display:grid; min-width:0; gap:3px; }
        .quote-card-symbol { color:var(--accent); font-size:13px; font-weight:850; letter-spacing:.04em; }
        .quote-card-company { overflow:hidden; color:var(--muted); font-size:11px; text-overflow:ellipsis; white-space:nowrap; }
        .quote-card-price { font-size:22px; font-weight:800; letter-spacing:-.03em; }
        .quote-card-change { justify-self:start; padding:4px 8px; border-radius:999px; background:color-mix(in srgb,var(--muted) 12%,transparent); color:var(--muted); font-size:11px; font-weight:750; }
        .quote-card-change.positive { color:var(--green); background:color-mix(in srgb,var(--green) 12%,transparent); }
        .quote-card-change.negative { color:var(--red); background:color-mix(in srgb,var(--red) 12%,transparent); }
        .quote-card-meta { color:var(--muted); font-size:10px; line-height:1.45; }
        .button,button { min-height:42px; }
        .button:focus-visible,button:focus-visible,a:focus-visible,input:focus-visible,select:focus-visible,textarea:focus-visible { outline:3px solid color-mix(in srgb,var(--accent) 65%,white); outline-offset:3px; }
        .table-wrap { border:1px solid var(--border); border-radius:14px; scrollbar-color:var(--muted) transparent; }
        .table-wrap table { border:0; }
        .compact-table table { min-width:0; table-layout:fixed; }
        .compact-table th,.compact-table td { padding:10px 8px; overflow-wrap:anywhere; }
        th { padding-top:13px; padding-bottom:13px; }
        .empty-state { display:grid; justify-items:center; gap:8px; grid-column:1/-1; padding:28px 20px; border:1px dashed var(--border); border-radius:14px; text-align:center; }
        .empty-state h3,.empty-state p { margin:0; }
        .empty-state p { max-width:560px; color:var(--muted); }
        .eyebrow { color:var(--accent); font-size:11px; font-weight:800; letter-spacing:.13em; text-transform:uppercase; }
        .app-footer { padding:4px 8px 16px; color:var(--muted); font-size:11px; text-align:center; }
        .bottom-nav { position:fixed; z-index:20; top:50%; right:auto; bottom:auto; left:16px; display:flex; flex-direction:column; gap:4px; width:76px; max-width:none; padding:8px; border:1px solid var(--border); border-radius:20px; background:color-mix(in srgb,var(--panel) 97%,transparent); box-shadow:0 14px 40px rgba(0,0,0,.28); transform:translateY(-50%); }
        .bottom-nav a { display:grid; justify-items:center; width:58px; padding:8px 4px; border-radius:13px; font-size:10px; font-weight:650; }
        .bottom-nav a span { display:grid; place-items:center; width:34px; height:34px; margin:0 auto 5px; border-radius:12px; background:var(--panel-2); }
        .bottom-nav a span svg { width:18px; height:18px; fill:none; stroke:currentColor; stroke-linecap:round; stroke-linejoin:round; stroke-width:1.8; }
        .bottom-nav a.active { color:var(--accent); }
        @media(max-width:999px) {
            .app-wrap { width:auto; margin:0 auto; }
            .bottom-nav { top:auto; right:auto; bottom:18px; left:50%; flex-direction:row; width:max-content; max-width:calc(100vw - 32px); transform:translateX(-50%); }
            .bottom-nav a { width:112px; padding:8px 10px; font-size:11px; }
        }
        @media(max-width:700px) {
            body { padding:12px 12px 116px; }
            .app-topbar { padding:10px; margin-bottom:16px; border-radius:16px; }
            .brand-mark { width:38px; height:38px; border-radius:12px; }
            .brand-copy small { display:none; }
            .page-head { padding:20px; }
            .page-head .row { width:100%; }
            .page-head .row .button { flex:1; }
            .panel { padding:18px; }
            .metrics { grid-auto-columns:initial; grid-auto-flow:row; grid-template-columns:repeat(2,minmax(0,1fr)); overflow:visible; scroll-snap-type:none; }
            .metric { scroll-snap-align:unset; }
            .quote-card { min-height:140px; }
            .bottom-nav { right:0; bottom:0; left:0; width:auto; max-width:none; justify-content:space-around; gap:0; padding:7px 8px calc(7px + env(safe-area-inset-bottom)); border-right:0; border-bottom:0; border-left:0; border-radius:18px 18px 0 0; transform:none; }
            .bottom-nav a { flex:1; width:auto; max-width:130px; padding:6px 2px; font-size:10px; }
            .bottom-nav a span { width:32px; height:30px; margin-bottom:3px; border-radius:11px; }
        }
        @media(max-width:420px) {
            body { padding:10px 10px 108px; }
            .app-topbar { gap:6px; }
            .account-identity { gap:0; }
            .account-details { display:none; }
            .account-bar { gap:6px; }
            .theme-toggle,.account-bar form button { padding:6px 8px; font-size:11px; }
            .brand-copy strong { font-size:12px; }
            .page-head,.panel { padding:16px; }
            .metric { padding:14px; }
            .metric strong { font-size:18px; }
        }
        @media(prefers-reduced-motion:reduce) {
            *,*::before,*::after { scroll-behavior:auto!important; animation-duration:.01ms!important; animation-iteration-count:1!important; transition-duration:.01ms!important; }
        }
    </style>
</head>
<body>
    <main class="app-wrap">
        <header class="app-topbar">
            <a class="brand" href="{{ route('market.overview') }}" aria-label="Uqi Invest, beranda">
                <span class="brand-mark" aria-hidden="true">U</span>
                <span class="brand-copy"><strong>UQI <span>INVEST</span></strong><small>Ruang belajar &amp; simulasi saham</small></span>
            </a>
            <div class="account-bar">
                <span class="account-identity">
                    <span class="account-avatar" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="account-details"><strong>{{ auth()->user()->name }}</strong><small>Akun pribadi</small></span>
                </span>
                <button type="button" class="theme-toggle" id="themeBtn" aria-pressed="false">☀️ Terang</button>
                <form action="{{ route('logout') }}" method="POST">@csrf<button class="secondary" type="submit" aria-label="Keluar dari akun">Keluar</button></form>
            </div>
        </header>
        @if(session('success')) <div class="notice success">{{ session('success') }}</div> @endif
        @if($errors->any()) <div class="notice error"><strong>Ada yang perlu diperiksa:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif
        @yield('content')
        <footer class="app-footer">Untuk edukasi dan simulasi. Bukan layanan broker atau rekomendasi beli/jual; verifikasi data dan sumber sebelum mengambil keputusan.</footer>
    </main>
    @include('partials.investment-nav', ['active' => $activeNav ?? 'market'])
    <script>
        const themeButton = document.getElementById('themeBtn');
        const applyTheme = theme => {
            document.documentElement.dataset.theme = theme;
            themeButton.textContent = theme === 'light' ? '🌙 Gelap' : '☀️ Terang';
            themeButton.setAttribute('aria-pressed', theme === 'light' ? 'true' : 'false');
        };
        applyTheme(document.documentElement.dataset.theme);
        themeButton.addEventListener('click', () => {
            const theme = document.documentElement.dataset.theme === 'light' ? 'dark' : 'light';
            localStorage.setItem('portfolio-theme', theme);
            applyTheme(theme);
        });
    </script>
    @stack('scripts')
</body>
</html>
