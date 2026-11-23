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
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --text-color: #f8fafc;
            --border-color: #334155;
            --table-hover: #273548;
        }
        .light-mode {
            --bg-color: #f1f5f9;
            --card-bg: #ffffff;
            --text-color: #0f172a;
            --border-color: #cbd5e1;
            --table-hover: #f8fafc;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 20px;
            transition: background 0.3s, color 0.3s;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            background: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .header h2 { margin: 0; }
        .btn-group { display: flex; gap: 10px; align-items: center; }
        .btn-tambah { background-color: #10b981; color: white; padding: 10px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; }
        .btn-tambah:hover { background-color: #059669; }
        .btn-theme { background-color: #64748b; color: white; border: none; padding: 10px 14px; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .top-tools { display: flex; justify-content: space-between; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
        .search-form, .filter-form { display: flex; gap: 10px; }
        input, select { padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-color); background: var(--card-bg); color: var(--text-color); }
        .btn-action { padding: 8px 16px; background: #38bdf8; border: none; border-radius: 6px; color: white; cursor: pointer; font-weight: bold; }
        .btn-reset { padding: 8px 12px; background: #64748b; border-radius: 6px; color: white; text-decoration: none; display: flex; align-items: center; }

        .summary-box, .chart-box {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #38bdf8;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--card-bg);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--border-color); font-size: 14px; }
        th { background-color: var(--border-color); font-weight: 600; }
        tr:hover { background-color: var(--table-hover); }
        .badge { background-color: #3b82f6; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .btn-edit { background-color: #0284c7; color: white; padding: 6px 10px; border-radius: 4px; text-decoration: none; font-size: 12px; }
        .btn-hapus { background-color: #ef4444; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 12px; }
    </style>
</head>
<body id="bodyEl">

    <div class="container">
        <!-- HEADER & TOOLS -->
        <div class="header">
            <div>
                <h2>📈 Portofolio Saham Ultimate Pro - Uqi</h2>
                <a href="https://github.com/syauqi432/laravel-dari-nol-uqi" target="_blank" style="color: #38bdf8; font-size: 13px; text-decoration: none; display: inline-block; margin-top: 4px;">
                    🔗 Lihat Repository GitHub
                </a>
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

        <div class="summary-box" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <span style="font-size: 13px; opacity: 0.8;">Total Modal Keseluruhan:</span>
                <h2 style="margin: 2px 0 0 0; color: #38bdf8;">Rp {{ number_format($totalModal, 0, ',', '.') }}</h2>
            </div>
            <div>
                <span style="font-size: 13px; opacity: 0.8;">Total Estimasi Dividen:</span>
                <h2 style="margin: 2px 0 0 0; color: #34d399;">Rp {{ number_format($totalDividenKeseluruhan, 0, ',', '.') }}</h2>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 13px; opacity: 0.8;">Total Emiten:</span>
                <h3 style="margin: 2px 0 0 0;">{{ count($saham) }} Emiten</h3>
            </div>
        </div>

        <!-- GRAFIK LIVE MARKET REAL-TIME (CHART.JS) -->
        <div class="chart-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span style="font-size: 14px; font-weight: bold; color: #38bdf8;">📊 Live Market Price Movement (BBCA / Real-Time Pro)</span>
                <span style="font-size: 11px; background: #059669; color: white; padding: 3px 8px; border-radius: 4px;">Live Updates Active</span>
            </div>
            <div style="position: relative; height: 200px; width: 100%;">
                <canvas id="liveMarketChart"></canvas>
            </div>
        </div>

        <!-- TABEL DATA SAHAM -->
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

    <!-- Script JavaScript untuk Grafik Live & Tema -->
    <script>
        function toggleTheme() {
            const body = document.getElementById('bodyEl');
            const btn = document.getElementById('themeBtn');
            body.classList.toggle('light-mode');
            btn.textContent = body.classList.contains('light-mode') ? '☀️ Terang' : '🌙 Tema';
        }

        // Inisialisasi Chart.js Live Market
        const ctx = document.getElementById('liveMarketChart').getContext('2d');
        const liveChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['10:00', '10:05', '10:10', '10:15', '10:20', '10:25'],
                datasets: [{
                    label: 'Harga BBCA (IDR)',
                    data: [9800, 9900, 9850, 10000, 10150, 10350],
                    borderColor: '#34d399',
                    backgroundColor: 'rgba(52, 211, 153, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(51, 65, 85, 0.2)' } },
                    y: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(51, 65, 85, 0.2)' } }
                }
            }
        });

        // Simulasi pergerakan harga real-time
        setInterval(() => {
            const now = new Date();
            const timeString = now.getHours().toString().padStart(2, '0') + ':' +
                               now.getMinutes().toString().padStart(2, '0') + ':' +
                               now.getSeconds().toString().padStart(2, '0');

            const lastPrice = liveChart.data.datasets[0].data[liveChart.data.datasets[0].data.length - 1];
            const fluctuation = Math.floor(Math.random() * 60) - 28;
            const newPrice = lastPrice + fluctuation;

            liveChart.data.labels.push(timeString);
            liveChart.data.datasets[0].data.push(newPrice);

            if (liveChart.data.labels.length > 8) {
                liveChart.data.labels.shift();
                liveChart.data.datasets[0].data.shift();
            }

            liveChart.update('none');
        }, 2000);
    </script>
</body>
</html>
