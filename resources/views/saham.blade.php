<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portofolio Saham - Uqi</title>
    <!-- CDN Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            color-scheme: dark;
            --bg-color: #0b0b0b;
            --bg-secondary: #1c1c1c;
            --card-bg: rgba(21, 21, 21, 0.97);
            --card-solid: #151515;
            --text-color: #f2f2f2;
            --muted-color: #929292;
            --border-color: rgba(255, 255, 255, 0.09);
            --table-hover: rgba(22, 167, 122, 0.07);
            --accent: #16a77a;
            --accent-soft: rgba(22, 167, 122, 0.14);
            --success: #18ad80;
            --success-soft: rgba(24, 173, 128, 0.14);
            --danger: #fb7185;
            --danger-soft: rgba(251, 113, 133, 0.12);
            --chart-bg: #0b0b0b;
            --shadow: 0 18px 45px rgba(2, 6, 23, 0.28);
        }
        .light-mode {
            color-scheme: light;
            --bg-color: #f4f7fb;
            --bg-secondary: #eaf0f7;
            --card-bg: rgba(255, 255, 255, 0.94);
            --card-solid: #ffffff;
            --text-color: #172033;
            --muted-color: #64748b;
            --border-color: rgba(100, 116, 139, 0.16);
            --table-hover: #f3f7fb;
            --accent: #047857;
            --accent-soft: rgba(4, 120, 87, 0.10);
            --success-soft: rgba(16, 185, 129, 0.10);
            --danger-soft: rgba(244, 63, 94, 0.10);
            --chart-bg: #f8fafc;
            --shadow: 0 18px 45px rgba(51, 65, 85, 0.10);
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at 85% 5%, rgba(22, 167, 122, 0.08), transparent 24rem),
                var(--bg-color);
            color: var(--text-color);
            margin: 0;
            min-height: 100vh;
            padding: 28px 20px;
            transition: background 0.25s, color 0.25s;
        }
        .container { width: calc(100% - 132px); max-width: 1280px; margin: 0 24px 0 108px; }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            background: var(--card-bg);
            backdrop-filter: blur(14px);
            padding: 18px 20px;
            border: 1px solid var(--border-color);
            border-radius: 18px;
            box-shadow: var(--shadow);
            background-image: linear-gradient(125deg, rgba(22, 167, 122, 0.08), transparent 65%);
        }
        .header h2 { margin: 0; font-size: 21px; letter-spacing: -0.02em; }
        .header-subtitle { color: var(--muted-color); font-size: 12px; margin-top: 5px; }
        .btn-group { display: flex; gap: 10px; align-items: center; }
        .btn-tambah { background: linear-gradient(135deg, #159c72, #087a58); color: white; padding: 10px 14px; border-radius: 10px; text-decoration: none; font-weight: 800; box-shadow: 0 8px 20px rgba(16, 167, 122, 0.18); }
        .btn-tambah:hover { transform: translateY(-1px); }
        .btn-theme { background: var(--bg-secondary); color: var(--text-color); border: 1px solid var(--border-color); padding: 10px 12px; border-radius: 10px; cursor: pointer; font-weight: 700; }
        .top-tools { display: grid; grid-template-columns: minmax(280px, 1fr) auto; gap: 12px; margin-bottom: 18px; }
        .search-form, .filter-form { display: flex; gap: 8px; }
        .search-form { max-width: 480px; }
        input, select { padding: 10px 12px; border-radius: 10px; border: 1px solid var(--border-color); background: var(--card-solid); color: var(--text-color); outline: none; }
        input:focus, select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft); }
        .btn-action { padding: 10px 16px; background: linear-gradient(135deg, #159c72, #087a58); border: none; border-radius: 10px; color: white; cursor: pointer; font-weight: 800; }
        .btn-reset { padding: 10px 12px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-color); text-decoration: none; display: flex; align-items: center; }

        .summary-box, .chart-box {
            background: var(--card-bg);
            backdrop-filter: blur(14px);
            padding: 18px;
            border: 1px solid var(--border-color);
            border-radius: 18px;
            box-shadow: var(--shadow);
            margin-bottom: 18px;
        }
        .summary-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; }
        .allocation-layout { display: grid; grid-template-columns: minmax(220px, 320px) minmax(0, 1fr); gap: 18px; align-items: center; }
        .allocation-layout > * { min-width: 0; }
        .metric-card { min-height: 78px; padding: 16px; border: 1px solid var(--border-color); border-radius: 12px; background: var(--card-solid); position: relative; overflow: hidden; }
        .metric-card::after { content: ''; position: absolute; width: 70px; height: 70px; right: -35px; bottom: -35px; border-radius: 50%; background: var(--accent-soft); }
        .metric-label { color: var(--muted-color); font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
        .metric-value { margin-top: 8px; font-size: 20px; font-weight: 800; letter-spacing: -0.02em; }
        .chart-box { padding: 0; overflow: hidden; }
        .chart-heading { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 16px 18px; border-bottom: 1px solid var(--border-color); flex-wrap: wrap; }
        .chart-controls { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .chart-title { font-size: 14px; font-weight: 800; color: var(--accent); }
        .market-status { display: inline-flex; align-items: center; color: var(--muted-color); background: var(--bg-secondary); padding: 7px 10px; border-radius: 999px; font-size: 11px; font-weight: 800; }
        .live-status { display: inline-flex; align-items: center; gap: 7px; color: var(--success); background: var(--success-soft); padding: 7px 10px; border-radius: 999px; font-size: 11px; font-weight: 800; }
        .live-status::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: var(--success); box-shadow: 0 0 0 4px var(--success-soft); animation: pulse 1.8s infinite; }
        @keyframes pulse { 50% { box-shadow: 0 0 0 7px transparent; } }
        .chart-container { height: 280px; padding: 14px 14px 10px; background: var(--chart-bg); }
        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        th, td { padding: 13px 14px; text-align: left; border-bottom: 1px solid var(--border-color); font-size: 13px; }
        th { background: var(--bg-secondary); color: var(--muted-color); font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; font-size: 11px; }
        tr:hover { background-color: var(--table-hover); }
        tbody tr:last-child td { border-bottom: 0; }
        .badge { background: var(--accent-soft); color: var(--accent); border: 1px solid rgba(22, 167, 122, 0.2); padding: 5px 8px; border-radius: 7px; font-size: 12px; font-weight: 800; }
        .btn-edit { background: var(--accent-soft); color: var(--accent); padding: 7px 9px; border-radius: 7px; text-decoration: none; font-size: 12px; font-weight: 800; }
        .btn-hapus { background: var(--danger-soft); color: var(--danger); border: 1px solid transparent; padding: 7px 9px; border-radius: 7px; cursor: pointer; font-size: 12px; font-weight: 800; }
        .table-wrap { overflow-x: auto; border-radius: 16px; }
        body#bodyEl { padding-bottom: 124px; }
        .bottom-nav { position: fixed; z-index: 20; top: 50%; right: auto; bottom: auto; left: 16px; display: flex; flex-direction: column; justify-content: center; gap: 4px; width: 76px; max-width: none; padding: 8px; border: 1px solid var(--border-color); border-radius: 20px; background: rgba(15, 23, 42, .96); box-shadow: 0 14px 40px rgba(0, 0, 0, .28); backdrop-filter: blur(16px); transform: translateY(-50%); }
        .light-mode .bottom-nav { background: rgba(255, 255, 255, .9); }
        .bottom-nav a { display: grid; justify-items: center; width: 58px; padding: 8px 4px; border-radius: 13px; color: var(--muted-color); text-decoration: none; text-align: center; font-size: 10px; font-weight: 650; }
        .bottom-nav a span { display: grid; place-items: center; width: 34px; height: 34px; margin: 0 auto 5px; border-radius: 12px; background: var(--bg-secondary); }
        .bottom-nav a span svg { width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.8; }
        .bottom-nav a.active { color: var(--accent); background: var(--accent-soft); }
        .bottom-nav a.active span { color: var(--accent); }
        @media (max-width: 999px) {
            .container { width: auto; margin: 0 auto; }
            .bottom-nav { top: auto; right: auto; bottom: 18px; left: 50%; flex-direction: row; width: max-content; max-width: calc(100vw - 32px); transform: translateX(-50%); }
            .bottom-nav a { width: 112px; padding: 8px 10px; font-size: 11px; }
        }
        @media (max-width: 760px) {
            body { padding: 16px 12px 116px; }
            .header, .chart-heading { align-items: flex-start; }
            .header { flex-direction: column; gap: 12px; }
            .top-tools, .summary-grid { grid-template-columns: 1fr; }
            .top-tools > *, .search-form, .filter-form { min-width: 0; max-width: 100%; }
            .filter-form { width: 100%; flex-wrap: wrap; }
            .filter-form select { flex: 1 1 130px; min-width: 0; }
            .filter-form .btn-reset { flex: 1 1 auto; justify-content: center; }
            .search-form { width: 100%; }
            .search-form input { min-width: 0; flex: 1; }
            .chart-container { height: 230px; }
            .allocation-layout { grid-template-columns: minmax(0, 1fr); }
            .bottom-nav { top: auto; right: 0; bottom: 0; left: 0; width: auto; max-width: none; justify-content: space-around; gap: 0; padding: 7px 8px calc(7px + env(safe-area-inset-bottom)); border-right: 0; border-bottom: 0; border-left: 0; border-radius: 18px 18px 0 0; transform: none; }
            .bottom-nav a { flex: 1; width: auto; max-width: 130px; padding: 6px 2px; font-size: 10px; }
            .bottom-nav a span { width: 32px; height: 30px; margin-bottom: 3px; border-radius: 11px; }
        }
    </style>
</head>
<body id="bodyEl">

    @include('partials.investment-nav', ['active' => 'portfolio'])

    <div class="container">
        <!-- HEADER & TOOLS -->
        <div class="header">
            <div>
                <h2>Portofolio &amp; aset</h2>
                <div class="header-subtitle">Ringkasan kepemilikan, estimasi nilai, dividen, dan aktivitas saham Anda</div>
            </div>
            <div class="btn-group">
                <button onclick="toggleTheme()" class="btn-theme" id="themeBtn" aria-pressed="false">☀️ Terang</button>
                <a href="/saham/tambah" class="btn-tambah">+ Tambah Saham</a>
            </div>
        </div>

        <!-- SEARCH & FILTER -->
        <div class="top-tools">
            <form action="/saham" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Cari emiten/perusahaan..." value="{{ request('search') }}">
                <input type="hidden" name="sektor" value="{{ request('sektor') }}">
                <button type="submit" class="btn-action">Cari</button>
            </form>

            <form action="/saham" method="GET" class="filter-form">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <select name="sektor" onchange="this.form.submit()">
                    <option value="">-- Semua Sektor --</option>
                    @foreach($sektorList as $sek)
                        <option value="{{ $sek }}" {{ request('sektor') == $sek ? 'selected' : '' }}>{{ $sek }}</option>
                    @endforeach
                </select>
                <select name="sort" onchange="this.form.submit()" aria-label="Urutkan portofolio">
                    <option value="terbaru" {{ request('sort', 'terbaru') == 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                    <option value="nama" {{ request('sort') == 'nama' ? 'selected' : '' }}>Nama A-Z</option>
                    <option value="kode" {{ request('sort') == 'kode' ? 'selected' : '' }}>Kode Emiten</option>
                    <option value="lot-terbanyak" {{ request('sort') == 'lot-terbanyak' ? 'selected' : '' }}>Lot Terbanyak</option>
                    <option value="modal-terbesar" {{ request('sort') == 'modal-terbesar' ? 'selected' : '' }}>Modal Terbesar</option>
                </select>
                <a href="/saham/export/csv" class="btn-reset">Export CSV</a>
                @if(request('search') || request('sektor'))
                    <a href="/saham" class="btn-reset">Reset</a>
                @endif
            </form>
        </div>

        <!-- RINGKASAN TOTAL MODAL & DIVIDEN -->
        @php
            $totalModal = 0;
            $totalDividenKeseluruhan = 0;
            foreach($saham as $item) {
                $totalModal += $item->jumlah_lot * $item->harga_beli * 100;
                $totalDividenKeseluruhan += $item->jumlah_lot * 100 * ($item->dividen_per_saham ?? 0);
            }
        @endphp

        <div class="summary-box">
            <div class="summary-grid">
                <div class="metric-card">
                    <div class="metric-label">Total Modal</div>
                    <div class="metric-value" style="color: #38bdf8;">Rp {{ number_format($totalModal, 0, ',', '.') }}</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Estimasi Dividen</div>
                    <div class="metric-value" style="color: #34d399;">Rp {{ number_format($totalDividenKeseluruhan, 0, ',', '.') }}</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Jumlah Emiten</div>
                    <div class="metric-value">{{ count($saham) }} <span style="font-size:13px;color:var(--muted-color);">Emiten</span></div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Nilai Bersih Estimasi (setelah biaya jual)</div>
                    <div class="metric-value" id="currentPortfolioValue">Menghitung...</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Profit / Loss</div>
                    <div class="metric-value" id="portfolioProfitLoss">Menghitung...</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Sumber quote P/L</div>
                    <div class="metric-value" style="font-size:13px;" id="portfolioQuoteStatus">Memuat...</div>
                </div>
            </div>
        </div>

        @php
            $alokasiSektor = $saham->groupBy('sektor')->map(function ($items) {
                return $items->sum(fn ($item) => $item->jumlah_lot * $item->harga_beli * 100);
            });
        @endphp
        <div class="summary-box">
            <div class="allocation-layout">
                <div style="height:220px;">
                    <canvas id="sectorAllocationChart"></canvas>
                </div>
                <div>
                    <div class="chart-title" style="margin-bottom:12px;">Alokasi Berdasarkan Modal Beli</div>
                    @foreach($alokasiSektor as $namaSektor => $nilaiSektor)
                        <div style="display:flex;justify-content:space-between;gap:12px;padding:7px 0;border-bottom:1px solid var(--border-color);">
                            <span style="color:var(--muted-color);">{{ $namaSektor ?: 'Lainnya' }}</span>
                            <strong>Rp {{ number_format($nilaiSektor, 0, ',', '.') }}</strong>
                        </div>
                    @endforeach
                    @if($alokasiSektor->isEmpty())
                        <div style="color:var(--muted-color);">Belum ada data portofolio.</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- GRAFIK LIVE MARKET INTERAKTIF (BISA GANTI EMITEN & TIPE GRAFIK) -->
        <div class="chart-box">
            <div class="chart-heading">
                <div class="chart-controls">
                    <span class="chart-title" id="chartTitle">📊 Candlestick Historis</span>

                    <!-- Dropdown Pilih Emiten -->
                    <select id="emitenSelector" onchange="gantiEmiten()" aria-label="Pilih emiten">
                        <option value="BBCA">BBCA (Bank Central Asia)</option>
                        @foreach($saham as $item)
                            <option value="{{ $item->kode_emiten }}">
                                {{ $item->kode_emiten }} ({{ $item->nama_perusahaan }})
                            </option>
                        @endforeach
                    </select>

                    <select id="chartTimeframeSelector" onchange="gantiTimeframe()" aria-label="Pilih timeframe OHLC">
                        <option value="daily">Harian</option>
                        <option value="weekly">Mingguan</option>
                        <option value="monthly">Bulanan</option>
                    </select>

                    <!-- Dropdown Ganti Model Grafik -->
                    <select id="chartTypeSelector" onchange="gantiTipeGrafik()" aria-label="Pilih tipe grafik">
                        <option value="candlestick">🕯️ Candlestick</option>
                        <option value="line">📈 Grafik Garis (Line)</option>
                        <option value="bar">📊 Grafik Batang (Bar)</option>
                    </select>
                </div>

                <span class="market-status" id="marketStatus">Memuat OHLC historis...</span>
            </div>

            <div class="chart-container">
                <canvas id="liveMarketChart"></canvas>
            </div>
        </div>

        <!-- TABEL DATA SAHAM -->
        <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Foto</th>
                    <th>Emiten</th>
                    <th>Favorit</th>
                    <th>Perusahaan</th>
                    <th>Sektor</th>
                    <th>Lot</th>
                    <th>Harga Beli</th>
                    <th>Total Modal</th>
                    <th>Total Dividen</th>
                    <th>P/L Estimasi</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($saham as $index => $item)
                @php
                    $modalItem = $item->jumlah_lot * $item->harga_beli * 100;
                    $totalDividenItem = $item->jumlah_lot * 100 * ($item->dividen_per_saham ?? 0);
                @endphp
                <tr data-emiten="{{ $item->kode_emiten }}" data-modal="{{ $modalItem }}">
                    <td>{{ $index + 1 }}</td>
                    <td>
                        @if($item->foto)
                            <img src="{{ asset('uploads/' . $item->foto) }}" alt="Foto" width="35" height="35" style="object-fit: cover; border-radius: 4px;">
                        @else
                            <span style="font-size: 11px; opacity: 0.6;">N/A</span>
                        @endif
                    </td>
                    <td><span class="badge">{{ $item->kode_emiten }}</span></td>
                    <td>
                        <form action="/saham/{{ $item->id }}/favorite" method="POST">
                            @csrf
                            <button type="submit" class="btn-theme" title="Ubah favorit" style="padding:6px 8px;">
                                {{ $item->is_favorite ? '★' : '☆' }}
                            </button>
                        </form>
                    </td>
                    <td style="font-weight: 600;">{{ $item->nama_perusahaan }}</td>
                    <td style="opacity: 0.8;">{{ $item->sektor }}</td>
                    <td>{{ $item->jumlah_lot }}</td>
                    <td>Rp {{ number_format($item->harga_beli, 0, ',', '.') }}</td>
                    <td style="font-weight: 600; color: #38bdf8;">Rp {{ number_format($modalItem, 0, ',', '.') }}</td>
                    <td style="font-weight: 600; color: #34d399;">Rp {{ number_format($totalDividenItem, 0, ',', '.') }}</td>
                    <td class="profit-loss" data-harga-beli="{{ $item->harga_beli }}" data-lot="{{ $item->jumlah_lot }}" data-biaya-jual-rate="{{ config('investment.paper_trading.sell_fee_rate') }}">Menghitung...</td>
                    <td style="text-align: center; white-space: nowrap;">
                        <a href="{{ route('issuers.show', $item->id) }}" class="btn-edit" style="margin-right: 4px;">Detail</a>
                        <a href="/saham/{{ $item->id }}/edit" class="btn-edit" style="margin-right: 4px;">Edit</a>
                        <form action="/saham/{{ $item->id }}" method="POST" onsubmit="return confirm('Yakin hapus saham ini?');" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-hapus">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" style="text-align: center; opacity: 0.7; padding: 30px;">Tidak ada data saham yang ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        <div class="summary-box" style="margin-top:18px;">
            <div class="chart-heading" style="padding:0 0 14px;">
                <span class="chart-title">Riwayat Transaksi</span>
            </div>
            <div class="top-tools" style="grid-template-columns:1fr; margin-bottom:12px;">
                <form action="{{ $saham->first() ? '/saham/'.$saham->first()->id.'/transaksi' : '/saham' }}" method="POST" class="filter-form" style="flex-wrap:wrap;">
                    @csrf
                    @if($saham->isEmpty())
                        <span style="color:var(--muted-color);">Tambahkan saham terlebih dahulu untuk mencatat transaksi.</span>
                    @else
                        <select name="saham_id" id="transactionSaham" aria-label="Pilih saham">
                            @foreach($saham as $item)
                                <option value="{{ $item->id }}">{{ $item->kode_emiten }} - {{ $item->nama_perusahaan }}</option>
                            @endforeach
                        </select>
                        <select name="jenis" aria-label="Jenis transaksi">
                            <option value="beli">Beli</option>
                            <option value="jual">Jual</option>
                        </select>
                        <input type="number" name="jumlah_lot" min="1" placeholder="Jumlah lot" required>
                        <input type="number" name="harga" min="0" step="0.01" placeholder="Harga per lembar" required>
                        <input type="date" name="tanggal" value="{{ now()->toDateString() }}" required>
                        <input type="text" name="catatan" placeholder="Catatan (opsional)" maxlength="255">
                        <button type="submit" class="btn-action" onclick="this.form.action='/saham/' + document.getElementById('transactionSaham').value + '/transaksi'">Simpan</button>
                    @endif
                </form>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Emiten</th>
                            <th>Jenis</th>
                            <th>Lot</th>
                            <th>Harga</th>
                            <th>Nilai</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->tanggal->format('d/m/Y') }}</td>
                                <td><span class="badge">{{ $transaction->saham->kode_emiten }}</span></td>
                                <td style="color:{{ $transaction->jenis === 'beli' ? '#34d399' : '#fb7185' }};font-weight:800;">{{ strtoupper($transaction->jenis) }}</td>
                                <td>{{ $transaction->jumlah_lot }}</td>
                                <td>Rp {{ number_format($transaction->harga, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($transaction->jumlah_lot * $transaction->harga * 100, 0, ',', '.') }}</td>
                                <td>{{ $transaction->catatan ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" style="text-align:center;opacity:.7;padding:24px;">Belum ada riwayat transaksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Script JavaScript untuk Grafik Live & Tema -->
    <script>
        function toggleTheme() {
            const body = document.getElementById('bodyEl');
            const btn = document.getElementById('themeBtn');
            body.classList.toggle('light-mode');
            const isLight = body.classList.contains('light-mode');
            btn.textContent = isLight ? '🌙 Gelap' : '☀️ Terang';
            localStorage.setItem('portfolio-theme', isLight ? 'light' : 'dark');
            sinkronkanTemaGrafik();
        }

        // Inisialisasi Chart.js Live Market
        const ctx = document.getElementById('liveMarketChart').getContext('2d');
        const candles = [];
        let selectedChartType = 'candlestick';
        let activeEmiten = 'BBCA';
        let historyRequestId = 0;

        const chartData = {
            labels: [],
            datasets: [{
                label: 'Harga BBCA (IDR)',
                data: [],
                borderColor: 'transparent',
                backgroundColor: 'transparent',
                borderWidth: 2,
                pointRadius: 0,
                fill: false,
                tension: 0.3
            }]
        };
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    ticks: { color: '#66736c', maxTicksLimit: 8 },
                    grid: { color: 'rgba(51, 65, 85, 0.08)' }
                },
                y: {
                    ticks: {
                        color: '#66736c',
                        callback: value => new Intl.NumberFormat('id-ID').format(value)
                    },
                    grid: { color: 'rgba(51, 65, 85, 0.12)' }
                }
            }
        };

        function sinkronkanTemaGrafik() {
            const isLight = document.getElementById('bodyEl').classList.contains('light-mode');
            const tickColor = isLight ? '#64748b' : '#94a3b8';
            chartOptions.scales.x.ticks.color = tickColor;
            chartOptions.scales.y.ticks.color = tickColor;
            chartOptions.scales.x.grid.color = isLight ? 'rgba(100,116,139,0.12)' : 'rgba(51,65,85,0.20)';
            chartOptions.scales.y.grid.color = isLight ? 'rgba(100,116,139,0.14)' : 'rgba(51,65,85,0.24)';
            if (typeof liveChart !== 'undefined') {
                liveChart.update('none');
            }
        }
        const candlestickPlugin = {
            id: 'portfolioCandlesticks',
            afterDatasetsDraw(chart) {
                if (selectedChartType !== 'candlestick') {
                    return;
                }

                const { ctx, chartArea, scales } = chart;
                const xScale = scales.x;
                const yScale = scales.y;
                if (!chartArea || !xScale || !yScale) {
                    return;
                }

                ctx.save();
                ctx.beginPath();
                ctx.rect(chartArea.left, chartArea.top, chartArea.width, chartArea.height);
                ctx.clip();

                candles.forEach((candle, index) => {
                    const x = xScale.getPixelForTick(index);
                    const nextX = index < candles.length - 1
                        ? xScale.getPixelForTick(index + 1)
                        : x;
                    const previousX = index > 0
                        ? xScale.getPixelForTick(index - 1)
                        : x;
                    const spacing = Math.min(
                        Math.abs(nextX - x) || Infinity,
                        Math.abs(x - previousX) || Infinity
                    );
                    const width = Math.max(3, Math.min(12, spacing * 0.58));
                    const rising = candle.close >= candle.open;
                    const color = rising ? '#16a34a' : '#dc2626';
                    const openY = yScale.getPixelForValue(candle.open);
                    const closeY = yScale.getPixelForValue(candle.close);

                    ctx.strokeStyle = color;
                    ctx.fillStyle = color;
                    ctx.lineWidth = 1;
                    ctx.beginPath();
                    ctx.moveTo(x, yScale.getPixelForValue(candle.high));
                    ctx.lineTo(x, yScale.getPixelForValue(candle.low));
                    ctx.stroke();
                    ctx.fillRect(
                        x - width / 2,
                        Math.min(openY, closeY),
                        width,
                        Math.max(1, Math.abs(closeY - openY))
                    );
                });

                ctx.restore();
            }
        };

        function sinkronkanChart() {
            chartData.labels = candles.map(candle => candle.time);
            chartData.datasets[0].data = candles.map(candle => candle.close);

            if (selectedChartType === 'candlestick' && candles.length > 0) {
                const prices = candles.flatMap(candle => [candle.low, candle.high]);
                const padding = Math.max(1, (Math.max(...prices) - Math.min(...prices)) * 0.08);
                chartOptions.scales.y.min = Math.min(...prices) - padding;
                chartOptions.scales.y.max = Math.max(...prices) + padding;
            } else {
                delete chartOptions.scales.y.min;
                delete chartOptions.scales.y.max;
            }

            const lastCandle = candles[candles.length - 1];
            if (!lastCandle) {
                chartData.datasets[0].borderColor = 'transparent';
                chartData.datasets[0].backgroundColor = 'transparent';
                liveChart.update('none');

                return;
            }

            const rising = lastCandle.close >= lastCandle.open;
            const dataset = chartData.datasets[0];
            dataset.borderColor = selectedChartType === 'candlestick'
                ? 'transparent'
                : (rising ? '#34d399' : '#f87171');
            dataset.backgroundColor = selectedChartType === 'bar'
                ? (rising ? '#34d399' : '#f87171')
                : (rising ? 'rgba(52, 211, 153, 0.1)' : 'rgba(248, 113, 113, 0.1)');
            dataset.fill = selectedChartType === 'line';
            dataset.pointRadius = selectedChartType === 'line' ? 2 : 0;
            dataset.tension = 0.3;

            liveChart.update('none');
        }

        function renderChart() {
            if (liveChart) {
                liveChart.destroy();
            }

            liveChart = new Chart(ctx, {
                type: selectedChartType === 'bar' ? 'bar' : 'line',
                data: chartData,
                options: chartOptions,
                plugins: [candlestickPlugin]
            });
        }

        let liveChart;
        renderChart();
        sinkronkanChart();

        async function muatOHLCHistoris(symbol, requestId) {
            const timeframe = document.getElementById('chartTimeframeSelector').value;
            const marketStatus = document.getElementById('marketStatus');
            candles.length = 0;
            marketStatus.textContent = 'Memuat OHLC historis...';
            sinkronkanChart();
            try {
                const url = '/api/harga-saham/' + encodeURIComponent(symbol)
                    + '/historical?timeframe=' + encodeURIComponent(timeframe);
                const response = await fetch(url);
                const result = await response.json();
                if (!response.ok || result.status !== 'available' || !Array.isArray(result.candles)) {
                    throw new Error(result.message || 'Penyedia tidak mengembalikan data OHLC valid.');
                }
                if (requestId !== historyRequestId) {
                    return;
                }

                candles.push(...result.candles);
                chartData.datasets[0].label = `${symbol} • ${timeframe} • ${result.source}`;
                sinkronkanChart();
                const freshness = result.freshness === 'recent'
                    ? 'relatif baru'
                    : result.freshness === 'stale' ? 'usang' : 'tidak diketahui';
                const retrievedAt = result.retrieved_at
                    ? new Date(result.retrieved_at).toLocaleString('id-ID')
                    : 'waktu pengambilan tidak tersedia';
                marketStatus.textContent = `${result.source} • candle ${result.last_updated} • data ${freshness} • diambil ${retrievedAt}`;
            } catch (error) {
                if (requestId === historyRequestId) {
                    candles.length = 0;
                    sinkronkanChart();
                    marketStatus.textContent = `OHLC tidak tersedia • ${error.message}`;
                }
            }
        }

        function gantiEmiten() {
            activeEmiten = document.getElementById('emitenSelector').value;
            historyRequestId++;
            muatOHLCHistoris(activeEmiten, historyRequestId);
        }

        function gantiTimeframe() {
            historyRequestId++;
            muatOHLCHistoris(activeEmiten, historyRequestId);
        }

        function gantiTipeGrafik() {
            const chartType = document.getElementById('chartTypeSelector').value;
            if (!['candlestick', 'line', 'bar'].includes(chartType)) {
                return;
            }

            selectedChartType = chartType;
            const chartNames = {
                candlestick: 'Candlestick Historis',
                line: 'Grafik Garis Historis',
                bar: 'Grafik Batang Historis'
            };
            document.getElementById('chartTitle').textContent = `📊 ${chartNames[chartType]}`;
            renderChart();
            sinkronkanChart();
        }

        const sectorLabels = @json($alokasiSektor->keys());
        const sectorValues = @json($alokasiSektor->values());
        const sectorCanvas = document.getElementById('sectorAllocationChart');

        if (sectorCanvas && sectorValues.length > 0) {
            new Chart(sectorCanvas, {
                type: 'doughnut',
                data: {
                    labels: sectorLabels,
                    datasets: [{
                        data: sectorValues,
                        backgroundColor: ['#38bdf8', '#34d399', '#fbbf24', '#a78bfa', '#fb7185', '#22d3ee'],
                        borderWidth: 0,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: '#94a3b8', boxWidth: 10, padding: 12 }
                        }
                    }
                }
            });
        }

        async function hitungProfitLoss() {
            const rows = [...document.querySelectorAll('.profit-loss')];
            const heldRows = rows.filter(row => Number.parseInt(row.dataset.lot, 10) > 0);
            let totalValue = 0;
            let totalModal = 0;
            let quoteUnavailable = false;
            const quoteDates = [];
            const uniqueEmitens = [...new Set(heldRows.map(row => row.closest('tr').dataset.emiten))];

            for (const emiten of uniqueEmitens) {
                let currentPrice;
                let quoteData;
                try {
                    const response = await fetch('/api/harga-saham/' + encodeURIComponent(emiten));
                    if (!response.ok) {
                        throw new Error(`Permintaan quote gagal (${response.status}).`);
                    }
                    const result = await response.json();
                    if (result.status !== 'available') {
                        throw new Error(result.message || 'Quote tidak tersedia.');
                    }
                    currentPrice = Number.parseFloat(result?.['Global Quote']?.['05. price']);
                    if (!Number.isFinite(currentPrice) || currentPrice <= 0) {
                        throw new Error('Quote tidak valid.');
                    }
                    quoteData = result;
                } catch (error) {
                    quoteUnavailable = true;
                    heldRows.filter(cell => cell.closest('tr').dataset.emiten === emiten)
                        .forEach(cell => {
                            cell.textContent = 'Quote tidak tersedia';
                            cell.style.color = 'var(--muted-color)';
                            cell.title = error.message;
                        });
                    continue;
                }

                quoteDates.push({
                    source: quoteData.source || 'Sumber tidak diketahui',
                    date: quoteData.last_updated || 'tanggal tidak diketahui',
                    freshness: quoteData.freshness || 'unknown'
                });
                heldRows.forEach(cell => {
                    if (cell.closest('tr').dataset.emiten !== emiten) {
                        return;
                    }

                    const hargaBeli = Number.parseFloat(cell.dataset.hargaBeli);
                    const lot = Number.parseInt(cell.dataset.lot, 10);
                    const lembar = lot * 100;
                    const modal = lembar * hargaBeli;
                    const nilaiKotor = lembar * currentPrice;
                    const estimasiBiayaJual = nilaiKotor * Number(cell.dataset.biayaJualRate);
                    const nilaiSaatIni = nilaiKotor - estimasiBiayaJual;
                    const profitLoss = nilaiSaatIni - modal;
                    totalModal += modal;
                    totalValue += nilaiSaatIni;

                    const prefix = profitLoss >= 0 ? '+' : '-';
                    const persentase = modal > 0 ? (profitLoss / modal) * 100 : 0;
                    cell.style.color = profitLoss >= 0 ? '#34d399' : '#fb7185';
                    cell.style.fontWeight = '800';
                    cell.title = `${quoteData.source || 'Sumber tidak diketahui'} • ${quoteData.last_updated || 'tanggal tidak diketahui'} • data ${quoteData.freshness || 'unknown'}`;
                    cell.textContent = `${prefix}Rp ${new Intl.NumberFormat('id-ID').format(Math.abs(profitLoss))} (${prefix}${Math.abs(persentase).toFixed(2)}%)`;
                });
            }

            const rupiah = new Intl.NumberFormat('id-ID');
            if (quoteUnavailable) {
                document.getElementById('currentPortfolioValue').textContent = 'Data quote tidak lengkap';
                document.getElementById('portfolioProfitLoss').textContent = 'Data quote tidak lengkap';
                document.getElementById('portfolioQuoteStatus').textContent = 'Sebagian quote tidak tersedia';
                return;
            }
            const uniqueDates = [...new Set(quoteDates.map(quote => `${quote.source} · ${quote.date} · ${quote.freshness}`))];
            document.getElementById('portfolioQuoteStatus').textContent = uniqueDates.length
                ? uniqueDates.join(' | ')
                : 'Tidak ada posisi untuk dinilai';
            const totalProfitLoss = totalValue - totalModal;
            document.getElementById('currentPortfolioValue').textContent = 'Rp ' + rupiah.format(totalValue);
            const profitElement = document.getElementById('portfolioProfitLoss');
            profitElement.textContent = (totalProfitLoss >= 0 ? '+' : '-') + 'Rp ' + rupiah.format(Math.abs(totalProfitLoss));
            profitElement.style.color = totalProfitLoss >= 0 ? '#34d399' : '#fb7185';
        }

        const savedTheme = localStorage.getItem('portfolio-theme');
        if (savedTheme === 'light') {
            document.getElementById('bodyEl').classList.add('light-mode');
            document.getElementById('themeBtn').textContent = '🌙 Gelap';
        } else {
            document.getElementById('themeBtn').textContent = '☀️ Terang';
        }
        sinkronkanTemaGrafik();

        gantiEmiten();
        hitungProfitLoss();
    </script>
</body>
</html>
