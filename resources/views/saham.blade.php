<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portofolio Saham Ultimate Pro - Uqi</title>
    <!-- CDN Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg-color: #090e1a;
            --bg-secondary: #111827;
            --card-bg: rgba(20, 29, 48, 0.92);
            --card-solid: #141d30;
            --text-color: #eef4ff;
            --muted-color: #94a3b8;
            --border-color: rgba(148, 163, 184, 0.14);
            --table-hover: rgba(56, 189, 248, 0.06);
            --accent: #38bdf8;
            --accent-soft: rgba(56, 189, 248, 0.12);
            --success: #34d399;
            --success-soft: rgba(52, 211, 153, 0.12);
            --danger: #fb7185;
            --danger-soft: rgba(251, 113, 133, 0.12);
            --chart-bg: #111827;
            --shadow: 0 18px 45px rgba(2, 6, 23, 0.28);
        }
        .light-mode {
            --bg-color: #f4f7fb;
            --bg-secondary: #eaf0f7;
            --card-bg: rgba(255, 255, 255, 0.94);
            --card-solid: #ffffff;
            --text-color: #172033;
            --muted-color: #64748b;
            --border-color: rgba(100, 116, 139, 0.16);
            --table-hover: #f3f7fb;
            --accent-soft: rgba(14, 165, 233, 0.10);
            --success-soft: rgba(16, 185, 129, 0.10);
            --danger-soft: rgba(244, 63, 94, 0.10);
            --chart-bg: #f8fafc;
            --shadow: 0 18px 45px rgba(51, 65, 85, 0.10);
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at 85% 5%, rgba(56, 189, 248, 0.10), transparent 24rem),
                radial-gradient(circle at 10% 80%, rgba(52, 211, 153, 0.05), transparent 20rem),
                var(--bg-color);
            color: var(--text-color);
            margin: 0;
            min-height: 100vh;
            padding: 28px 20px;
            transition: background 0.25s, color 0.25s;
        }
        .container { max-width: 1280px; margin: 0 auto; }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            background: var(--card-bg);
            backdrop-filter: blur(14px);
            padding: 18px 20px;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            box-shadow: var(--shadow);
        }
        .header h2 { margin: 0; font-size: 21px; letter-spacing: -0.02em; }
        .header-subtitle { color: var(--muted-color); font-size: 12px; margin-top: 5px; }
        .btn-group { display: flex; gap: 10px; align-items: center; }
        .btn-tambah { background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 10px 14px; border-radius: 10px; text-decoration: none; font-weight: 700; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.18); }
        .btn-tambah:hover { transform: translateY(-1px); }
        .btn-theme { background: var(--bg-secondary); color: var(--text-color); border: 1px solid var(--border-color); padding: 10px 12px; border-radius: 10px; cursor: pointer; font-weight: 700; }
        .top-tools { display: grid; grid-template-columns: minmax(280px, 1fr) auto; gap: 12px; margin-bottom: 18px; }
        .search-form, .filter-form { display: flex; gap: 8px; }
        .search-form { max-width: 480px; }
        input, select { padding: 10px 12px; border-radius: 10px; border: 1px solid var(--border-color); background: var(--card-solid); color: var(--text-color); outline: none; }
        input:focus, select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft); }
        .btn-action { padding: 10px 16px; background: #0ea5e9; border: none; border-radius: 10px; color: white; cursor: pointer; font-weight: 800; }
        .btn-reset { padding: 10px 12px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-color); text-decoration: none; display: flex; align-items: center; }

        .summary-box, .chart-box {
            background: var(--card-bg);
            backdrop-filter: blur(14px);
            padding: 18px;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            box-shadow: var(--shadow);
            margin-bottom: 18px;
        }
        .summary-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
        .metric-card { min-height: 78px; padding: 16px; border: 1px solid var(--border-color); border-radius: 12px; background: var(--card-solid); position: relative; overflow: hidden; }
        .metric-card::after { content: ''; position: absolute; width: 70px; height: 70px; right: -35px; bottom: -35px; border-radius: 50%; background: var(--accent-soft); }
        .metric-label { color: var(--muted-color); font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
        .metric-value { margin-top: 8px; font-size: 20px; font-weight: 800; letter-spacing: -0.02em; }
        .chart-box { padding: 0; overflow: hidden; }
        .chart-heading { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 16px 18px; border-bottom: 1px solid var(--border-color); flex-wrap: wrap; }
        .chart-controls { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .chart-title { font-size: 14px; font-weight: 800; color: var(--accent); }
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
        th { background: var(--bg-secondary); color: var(--muted-color); font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; font-size: 11px; }
        tr:hover { background-color: var(--table-hover); }
        tbody tr:last-child td { border-bottom: 0; }
        .badge { background: var(--accent-soft); color: var(--accent); border: 1px solid rgba(56, 189, 248, 0.16); padding: 5px 8px; border-radius: 7px; font-size: 12px; font-weight: 800; }
        .btn-edit { background: var(--accent-soft); color: var(--accent); padding: 7px 9px; border-radius: 7px; text-decoration: none; font-size: 12px; font-weight: 800; }
        .btn-hapus { background: var(--danger-soft); color: var(--danger); border: 1px solid transparent; padding: 7px 9px; border-radius: 7px; cursor: pointer; font-size: 12px; font-weight: 800; }
        .table-wrap { overflow-x: auto; border-radius: 16px; }
        @media (max-width: 760px) {
            body { padding: 16px 12px; }
            .header, .chart-heading { align-items: flex-start; }
            .header { flex-direction: column; gap: 12px; }
            .top-tools, .summary-grid { grid-template-columns: 1fr; }
            .filter-form { width: 100%; }
            .filter-form select { flex: 1; }
            .search-form { max-width: none; width: 100%; }
            .search-form input { min-width: 0; flex: 1; }
            .chart-container { height: 230px; }
        }
    </style>
</head>
<body id="bodyEl">

    <div class="container">
        <!-- HEADER & TOOLS -->
        <div class="header">
            <div>
                <h2>📈 Portofolio Saham Ultimate Pro - Uqi</h2>
                <div class="header-subtitle">Dashboard investasi • Pantau modal, dividen, dan pergerakan emiten</div>
            </div>
            <div class="btn-group">
                <button onclick="toggleTheme()" class="btn-theme" id="themeBtn">🌙 Tema</button>
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
            </div>
        </div>

        <!-- GRAFIK LIVE MARKET INTERAKTIF (BISA GANTI EMITEN & TIPE GRAFIK) -->
        <div class="chart-box">
            <div class="chart-heading">
                <div class="chart-controls">
                    <span class="chart-title">📊 Live Market</span>

                    <!-- Dropdown Pilih Emiten -->
                    <select id="emitenSelector" onchange="gantiEmiten()" aria-label="Pilih emiten">
                        <option value="BBCA" data-harga="10350">BBCA (Bank Central Asia)</option>
                        @foreach($saham as $item)
                            <option value="{{ $item->kode_emiten }}" data-harga="{{ $item->harga_beli }}">
                                {{ $item->kode_emiten }} ({{ $item->nama_perusahaan }})
                            </option>
                        @endforeach
                    </select>

                    <!-- Dropdown Ganti Model Grafik -->
                    <select id="chartTypeSelector" onchange="gantiTipeGrafik()" aria-label="Pilih tipe grafik">
                        <option value="candlestick">🕯️ Candlestick</option>
                        <option value="line">📈 Grafik Garis (Line)</option>
                        <option value="bar">📊 Grafik Batang (Bar)</option>
                    </select>
                </div>

                <span class="live-status" id="marketStatus">Live Update</span>
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
                    <th>Perusahaan</th>
                    <th>Sektor</th>
                    <th>Lot</th>
                    <th>Harga Beli</th>
                    <th>Total Modal</th>
                    <th>Total Dividen</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($saham as $index => $item)
                @php
                    $modalItem = $item->jumlah_lot * $item->harga_beli * 100;
                    $totalDividenItem = $item->jumlah_lot * 100 * ($item->dividen_per_saham ?? 0);
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        @if($item->foto)
                            <img src="{{ asset('uploads/' . $item->foto) }}" alt="Foto" width="35" height="35" style="object-fit: cover; border-radius: 4px;">
                        @else
                            <span style="font-size: 11px; opacity: 0.6;">N/A</span>
                        @endif
                    </td>
                    <td><span class="badge">{{ $item->kode_emiten }}</span></td>
                    <td style="font-weight: 600;">{{ $item->nama_perusahaan }}</td>
                    <td style="opacity: 0.8;">{{ $item->sektor }}</td>
                    <td>{{ $item->jumlah_lot }}</td>
                    <td>Rp {{ number_format($item->harga_beli, 0, ',', '.') }}</td>
                    <td style="font-weight: 600; color: #38bdf8;">Rp {{ number_format($modalItem, 0, ',', '.') }}</td>
                    <td style="font-weight: 600; color: #34d399;">Rp {{ number_format($totalDividenItem, 0, ',', '.') }}</td>
                    <td style="text-align: center; white-space: nowrap;">
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
                    <td colspan="10" style="text-align: center; opacity: 0.7; padding: 30px;">Tidak ada data saham yang ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
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
        const candleCount = 24;
        const candles = [];
        let selectedChartType = 'candlestick';
        let basePrice = 10350;
        let activeEmiten = 'BBCA';
        let emitenRequestId = 0;

        function waktuCandlestick(date) {
            return date.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }

        function buatCandlestickAwal(price) {
            candles.length = 0;
            const now = Date.now();
            let previousClose = price - (candleCount - 1) * 8;

            for (let index = 0; index < candleCount; index++) {
                const close = index === candleCount - 1
                    ? price
                    : previousClose + Math.floor(Math.random() * 61) - 30;
                const open = previousClose;
                const spread = Math.floor(Math.random() * 36) + 8;

                candles.push({
                    open,
                    high: Math.max(open, close) + Math.floor(Math.random() * spread),
                    low: Math.min(open, close) - Math.floor(Math.random() * spread),
                    close,
                    time: waktuCandlestick(new Date(now - (candleCount - index - 1) * 2000))
                });
                previousClose = close;
            }
        }

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
                    const x = xScale.getPixelForValue(index);
                    const nextX = index < candles.length - 1
                        ? xScale.getPixelForValue(index + 1)
                        : x;
                    const previousX = index > 0
                        ? xScale.getPixelForValue(index - 1)
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

        buatCandlestickAwal(basePrice);
        let liveChart;
        renderChart();
        sinkronkanChart();

        async function gantiEmiten() {
            const selector = document.getElementById('emitenSelector');
            const selectedEmiten = selector.value;
            const selectedOption = selector.options[selector.selectedIndex];
            const fallbackPrice = Number.parseFloat(selectedOption.dataset.harga);
            const requestId = ++emitenRequestId;
            const emitenChanged = selectedEmiten !== activeEmiten;
            const marketStatus = document.getElementById('marketStatus');
            activeEmiten = selectedEmiten;
            basePrice = Number.isFinite(fallbackPrice) && fallbackPrice > 0 ? fallbackPrice : 10350;

            if (emitenChanged) {
                buatCandlestickAwal(basePrice);
            }
            chartData.datasets[0].label = `Harga Simulasi ${activeEmiten} (IDR) - Database`;
            marketStatus.textContent = 'Sinkronisasi...';
            sinkronkanChart();

            try {
                const response = await fetch('/api/harga-saham/' + encodeURIComponent(activeEmiten));
                if (!response.ok) {
                    throw new Error(`Permintaan harga saham gagal (${response.status}).`);
                }

                const result = await response.json();
                const apiPrice = Number.parseFloat(result?.['Global Quote']?.['05. price']);

                if (requestId !== emitenRequestId) {
                    return;
                }

                if (Number.isFinite(apiPrice) && apiPrice > 0) {
                    basePrice = apiPrice;
                    chartData.datasets[0].label = `Harga Asli ${activeEmiten} (IDR) - Alpha Vantage`;
                    const currentCandle = candles[candles.length - 1];
                    currentCandle.close = basePrice;
                    currentCandle.high = Math.max(currentCandle.high, basePrice);
                    currentCandle.low = Math.min(currentCandle.low, basePrice);
                    sinkronkanChart();
                    marketStatus.textContent = 'Live • Alpha Vantage';
                } else {
                    marketStatus.textContent = 'Mode Simulasi';
                }
            } catch (error) {
                if (requestId === emitenRequestId) {
                    marketStatus.textContent = 'Mode Simulasi';
                    console.warn('Menggunakan harga simulasi dari database.', error);
                }
            }
        }

        function gantiTipeGrafik() {
            const chartType = document.getElementById('chartTypeSelector').value;
            if (!['candlestick', 'line', 'bar'].includes(chartType)) {
                return;
            }

            selectedChartType = chartType;
            renderChart();
            sinkronkanChart();
        }

        const savedTheme = localStorage.getItem('portfolio-theme');
        if (savedTheme === 'light') {
            document.getElementById('bodyEl').classList.add('light-mode');
            document.getElementById('themeBtn').textContent = '🌙 Gelap';
        } else {
            document.getElementById('themeBtn').textContent = '☀️ Terang';
        }
        sinkronkanTemaGrafik();

        // Simulasi pergerakan harga real-time
        setInterval(() => {
            const now = new Date();
            const timeString = now.getHours().toString().padStart(2, '0') + ':' +
                               now.getMinutes().toString().padStart(2, '0') + ':' +
                               now.getSeconds().toString().padStart(2, '0');

            const lastCandle = candles[candles.length - 1];
            const lastPrice = lastCandle.close;
            const fluctuation = Math.floor(Math.random() * 60) - 28;
            const newPrice = Math.max(1, lastPrice + fluctuation);
            const spread = Math.floor(Math.random() * 36) + 8;

            candles.push({
                open: lastPrice,
                high: Math.max(lastPrice, newPrice) + Math.floor(Math.random() * spread),
                low: Math.min(lastPrice, newPrice) - Math.floor(Math.random() * spread),
                close: newPrice,
                time: timeString
            });

            if (candles.length > candleCount) {
                candles.shift();
            }

            sinkronkanChart();
        }, 2000);

        gantiEmiten();
    </script>
</body>
</html>
