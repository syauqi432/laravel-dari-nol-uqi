@extends('layouts.investment', ['activeNav' => 'market'])

@section('title', $saham->kode_emiten.' — Detail Emiten')

@section('content')
    <header class="page-head issuer-heading">
        <div>
            <div class="row"><span class="badge">{{ $saham->kode_emiten }}</span><span class="muted">{{ $saham->sektor }}</span></div>
            <h1>{{ $saham->nama_perusahaan }}</h1>
            <div class="muted">Quote terakhir <strong id="detail-quote">Memuat...</strong> <span id="quote-time"></span></div>
        </div>
        <div class="row">
            <form action="{{ url('/saham/'.$saham->id.'/favorite') }}" method="POST">@csrf<button class="secondary" type="submit">{{ $saham->is_favorite ? '★ Watchlist' : '☆ Watchlist' }}</button></form>
            <a class="button green" href="#paper-trade">Paper trade</a>
        </div>
    </header>

    <nav class="issuer-tabs" role="tablist" aria-label="Detail emiten">
        <button type="button" role="tab" id="tab-overview" aria-controls="panel-overview" aria-selected="true" data-panel="panel-overview">Overview</button>
        <button type="button" role="tab" id="tab-orderbook" aria-controls="panel-orderbook" aria-selected="false" data-panel="panel-orderbook">Orderbook &amp; Trade Book</button>
        <button type="button" role="tab" id="tab-financials" aria-controls="panel-financials" aria-selected="false" data-panel="panel-financials">Financials</button>
        <button type="button" role="tab" id="tab-analysis" aria-controls="panel-analysis" aria-selected="false" data-panel="panel-analysis">Analysis &amp; Valuation</button>
        <button type="button" role="tab" id="tab-news" aria-controls="panel-news" aria-selected="false" data-panel="panel-news">News &amp; Disclosure</button>
    </nav>

    <section class="panel tab-panel" id="panel-overview" role="tabpanel" aria-labelledby="tab-overview">
        <div class="issuer-overview">
            <div>
                <div class="section-heading">
                    <div>
                        <h2>Grafik OHLC historis</h2>
                        <p class="caption">Candlestick berasal dari data historis penyedia, bukan tick harga real-time.</p>
                        <p class="caption" id="chart-status" aria-live="polite">Memuat data historis...</p>
                    </div>
                    <label class="chart-style">Periode
                        <select id="chart-timeframe">
                            <option value="daily">Harian</option>
                            <option value="weekly">Mingguan</option>
                            <option value="monthly">Bulanan</option>
                        </select>
                    </label>
                    <label class="chart-style">Gaya grafik
                        <select id="chart-style">
                            <option value="candlestick">Candlestick OHLC</option>
                            <option value="line">Garis penutupan</option>
                            <option value="bar">Batang penutupan</option>
                        </select>
                    </label>
                </div>
                <div class="issuer-chart"><canvas id="issuer-chart" aria-label="Grafik candlestick OHLC historis"></canvas></div>
                <div class="quote-summary">
                    <div><span class="muted">Buka</span><strong id="quote-open">—</strong></div>
                    <div><span class="muted">Tertinggi</span><strong id="quote-high">—</strong></div>
                    <div><span class="muted">Terendah</span><strong id="quote-low">—</strong></div>
                    <div><span class="muted">Perubahan</span><strong id="quote-change">—</strong></div>
                </div>
            </div>
            <aside class="issuer-profile">
                <h2>Profil perusahaan</h2>
                <dl>
                    <div><dt>Kode emiten</dt><dd>{{ $saham->kode_emiten }}</dd></div>
                    <div><dt>Nama perusahaan</dt><dd>{{ $saham->nama_perusahaan }}</dd></div>
                    <div><dt>Sektor</dt><dd>{{ $saham->sektor }}</dd></div>
                    <div><dt>Lot di portofolio</dt><dd>{{ number_format($saham->jumlah_lot, 0, ',', '.') }} lot</dd></div>
                    <div><dt>Rata-rata harga beli</dt><dd>Rp {{ number_format((float) $saham->harga_beli, 0, ',', '.') }}</dd></div>
                    <div><dt>Dividen per saham</dt><dd>Rp {{ number_format((float) ($saham->dividen_per_saham ?? 0), 0, ',', '.') }}</dd></div>
                </dl>
                <p class="caption">Profil dan statistik fundamental dicatat manual; belum ada profil emiten otomatis dari penyedia data.</p>
            </aside>
        </div>
    </section>

    <section class="panel tab-panel" id="panel-orderbook" role="tabpanel" aria-labelledby="tab-orderbook" hidden>
        <div class="section-heading"><div><h2>Orderbook</h2><p class="caption">Antrean bid/offer, lot, dan frekuensi.</p></div><span class="data-badge">Feed belum tersedia</span></div>
        <div class="notice">Data Level II tidak disediakan oleh koneksi quote saat ini. Tabel sengaja tidak diisi dengan angka simulasi.</div>
        <div class="grid-2">
            <div class="table-wrap">
                <table><thead><tr><th>Bid</th><th>Lot</th><th>Frekuensi</th></tr></thead><tbody><tr><td colspan="3" class="empty">Menunggu integrasi feed orderbook.</td></tr></tbody></table>
            </div>
            <div class="table-wrap">
                <table><thead><tr><th>Offer</th><th>Lot</th><th>Frekuensi</th></tr></thead><tbody><tr><td colspan="3" class="empty">Menunggu integrasi feed orderbook.</td></tr></tbody></table>
            </div>
        </div>
        <div class="section-heading" style="margin-top:22px"><div><h2>Trade Book / Running Trade</h2><p class="caption">Daftar transaksi yang benar-benar terjadi di pasar.</p></div></div>
        <div class="table-wrap">
            <table><thead><tr><th>Waktu</th><th>Harga</th><th>Lot</th><th>Arah</th></tr></thead><tbody><tr><td colspan="4" class="empty">Feed running trade belum dikonfigurasi.</td></tr></tbody></table>
        </div>
    </section>

    <section class="panel tab-panel" id="panel-financials" role="tabpanel" aria-labelledby="tab-financials" hidden>
        <div class="section-heading"><div><h2>Financials</h2><p class="caption">Bandingkan laporan kuartalan dan tahunan yang telah dicatat.</p></div></div>
        <div class="notice">Masukkan angka dari laporan resmi dan pastikan satuan serta periodenya sama. Aplikasi belum menarik laporan secara otomatis.</div>
        <div class="row period-tabs" role="group" aria-label="Pilih periode laporan">
            <button type="button" class="secondary period-button active" data-period="kuartal">Kuartalan</button>
            <button type="button" class="secondary period-button" data-period="tahunan">Tahunan</button>
        </div>
        <div class="table-wrap" style="margin-top:12px">
            <table id="financial-table">
                <thead><tr><th>Periode</th><th>Pendapatan</th><th>Laba operasi</th><th>Laba bersih</th><th>Total aset</th><th>Liabilitas</th><th>Arus kas operasi</th><th>Sumber</th></tr></thead>
                <tbody>
                    @forelse($saham->financials as $financial)
                        <tr data-period="{{ $financial->jenis_periode }}">
                            <td>{{ $financial->periode }}</td>
                            <td>{{ $financial->pendapatan === null ? '—' : number_format((float) $financial->pendapatan, 0, ',', '.') }}</td>
                            <td>{{ $financial->laba_operasi === null ? '—' : number_format((float) $financial->laba_operasi, 0, ',', '.') }}</td>
                            <td>{{ $financial->laba_bersih === null ? '—' : number_format((float) $financial->laba_bersih, 0, ',', '.') }}</td>
                            <td>{{ $financial->total_aset === null ? '—' : number_format((float) $financial->total_aset, 0, ',', '.') }}</td>
                            <td>{{ $financial->total_liabilitas === null ? '—' : number_format((float) $financial->total_liabilitas, 0, ',', '.') }}</td>
                            <td>{{ $financial->arus_kas_operasi === null ? '—' : number_format((float) $financial->arus_kas_operasi, 0, ',', '.') }}</td>
                            <td>{{ $financial->sumber ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr class="financial-empty"><td colspan="8" class="empty">Belum ada data laporan keuangan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <details class="entry-form">
            <summary>Tambah / perbarui laporan keuangan</summary>
            <form action="{{ route('issuer-financials.store', $saham->id) }}" method="POST" class="grid-3">
                @csrf
                <label>Periode<input name="periode" placeholder="2026-Q3 / 2025" required maxlength="20"></label>
                <label>Tipe periode<select name="jenis_periode"><option value="kuartal">Kuartal</option><option value="tahunan">Tahunan</option></select></label>
                <label>Pendapatan<input type="number" step="0.01" name="pendapatan"></label>
                <label>Laba operasi<input type="number" step="0.01" name="laba_operasi"></label>
                <label>Laba bersih<input type="number" step="0.01" name="laba_bersih"></label>
                <label>Total aset<input type="number" step="0.01" name="total_aset"></label>
                <label>Total liabilitas<input type="number" step="0.01" name="total_liabilitas"></label>
                <label>Arus kas operasi<input type="number" step="0.01" name="arus_kas_operasi"></label>
                <label>Sumber data<input name="sumber" placeholder="URL / dokumen resmi"></label>
                <div><button type="submit">Simpan data periode</button></div>
            </form>
        </details>
    </section>

    <section class="panel tab-panel" id="panel-analysis" role="tabpanel" aria-labelledby="tab-analysis" hidden>
        <div class="section-heading"><div><h2>Analysis &amp; Valuation</h2><p class="caption">Checklist edukatif, bukan rekomendasi investasi.</p></div></div>
        <div class="grid-2">
            <div class="score-card">
                <span class="muted">Checklist kesehatan</span>
                @if($healthScore['available'] > 0)
                    <strong class="score-number">{{ $healthScore['passed'] }}<span> / {{ $healthScore['available'] }}</span></strong>
                @else
                    <strong class="score-number">Belum cukup data</strong>
                @endif
                <span class="caption">Poin yang lolos dari indikator yang datanya tersedia; {{ $healthScore['total'] - $healthScore['available'] }} indikator belum diisi.</span>
            </div>
            <div class="metric">
                <span class="muted">Cakupan peer sektor {{ $saham->sektor }}</span>
                <strong>{{ $peerStats['count'] }} emiten</strong>
                <span class="caption">Hanya emiten sektor sama yang sudah tercatat dan memiliki PER serta PBV.</span>
            </div>
        </div>
        <div class="grid-2 score-checks">
            @foreach($healthChecks as $check)
                <div class="metric">
                    <span class="check-state {{ $check['value'] === null ? 'unknown' : ($check['passed'] ? 'passed' : 'not-passed') }}">
                        {{ $check['value'] === null ? 'Belum ada data' : ($check['passed'] ? 'Memenuhi cek sederhana' : 'Perlu ditinjau') }}
                    </span>
                    <strong>{{ $check['label'] }}</strong>
                    <span class="muted">Nilai: {{ $check['value'] ?? '—' }}</span>
                </div>
            @endforeach
        </div>
        <p class="caption">Checklist memberi bobot sama pada ROE positif, pertumbuhan pendapatan tidak negatif, DER ≤ 1x, dan arus kas operasi positif. Ambang ini sederhana, bukan standar universal; sektor, tren, dan kualitas data tetap perlu diperiksa.</p>

        <h3 style="margin-top:24px">Perbandingan valuasi sektor</h3>
        <div class="table-wrap">
            <table><thead><tr><th>Emiten</th><th>PER</th><th>PBV</th><th>ROE</th></tr></thead><tbody>
                <tr><td><strong>{{ $saham->kode_emiten }} (pilihan)</strong></td><td>{{ $saham->per ?? '—' }}</td><td>{{ $saham->pbv ?? '—' }}</td><td>{{ $saham->roe === null ? '—' : $saham->roe.'%' }}</td></tr>
                @forelse($peers as $peer)
                    <tr><td>{{ $peer->kode_emiten }}</td><td>{{ $peer->per }}</td><td>{{ $peer->pbv }}</td><td>{{ $peer->roe === null ? '—' : $peer->roe.'%' }}</td></tr>
                @empty
                    <tr><td colspan="4" class="empty">Belum ada pembanding sektor yang cukup.</td></tr>
                @endforelse
                @if($peerStats['count'] > 0)
                    <tr><td><strong>Rata-rata peer tersimpan</strong></td><td>{{ number_format((float) $peerStats['averagePer'], 2) }}</td><td>{{ number_format((float) $peerStats['averagePbv'], 2) }}</td><td>—</td></tr>
                @endif
            </tbody></table>
        </div>
        <p class="caption">Peer hanya berasal dari emiten sektor sama yang tersimpan di aplikasi; bukan seluruh kompetitor di bursa. PER/PBV yang lebih rendah sendiri tidak berarti saham lebih baik.</p>
    </section>

    <section class="panel tab-panel" id="panel-news" role="tabpanel" aria-labelledby="tab-news" hidden>
        <div class="section-heading"><div><h2>News &amp; Disclosure</h2><p class="caption">Bedakan informasi resmi emiten dari berita dan opini komunitas.</p></div></div>
        <section>
            <h3>Keterbukaan informasi &amp; corporate action</h3>
            <p class="muted">Catatan yang dimasukkan pengguna; verifikasi kembali ke sumber resmi.</p>
            <a class="button secondary" href="{{ route('corporate-actions.index') }}">Lihat kalender semua emiten</a>
            <div class="table-wrap">
                <table><thead><tr><th>Jenis</th><th>Tanggal penting</th><th>Status</th><th>Keterangan</th><th>Sumber tercatat</th></tr></thead><tbody>
                    @forelse($saham->corporateActions as $action)
                        <tr><td>{{ $action->jenis }}</td><td>{{ $action->tanggal_penting?->format('d/m/Y') ?? '—' }}</td><td>{{ $action->status ?? '—' }}</td><td>{{ $action->keterangan ?? '—' }}</td><td>{{ $action->sumber ?? '—' }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="empty">Belum ada disclosure tercatat. Feed bursa belum tersambung.</td></tr>
                    @endforelse
                </tbody></table>
            </div>
            <details class="entry-form">
                <summary>Catat disclosure / corporate action</summary>
                <form action="{{ route('issuer-corporate-actions.store', $saham->id) }}" method="POST" class="grid-3">
                    @csrf
                    <label>Jenis<input name="jenis" placeholder="Dividen / stock split / RUPS" required></label>
                    <label>Tanggal penting<input type="date" name="tanggal_penting"></label>
                    <label>Nilai per saham<input type="number" step="0.01" name="nilai_per_saham"></label>
                    <label>Status<input name="status" placeholder="Diumumkan / selesai"></label>
                    <label>Keterangan<input name="keterangan" maxlength="2000"></label>
                    <label>Sumber<input name="sumber" placeholder="URL keterbukaan informasi"></label>
                    <div><button type="submit">Simpan disclosure</button></div>
                </form>
            </details>
        </section>

        <section style="margin-top:24px">
            <h3>Berita terbaru</h3>
            <div class="empty">Feed berita belum dikonfigurasi. Periksa kanal resmi bursa dan emiten untuk informasi terbaru.</div>
        </section>

        <section style="margin-top:24px">
            <h3>Stream / Komunitas</h3>
            <p class="notice">Posting komunitas adalah opini, bukan informasi terverifikasi atau rekomendasi. Jangan membagikan data pribadi.</p>
            <form action="{{ route('issuer-posts.store', $saham->id) }}" method="POST" class="grid-2">
                @csrf
                <label>Nama tampilan<input name="nama_penulis" required maxlength="80"></label>
                <label>Diskusi<textarea name="isi" minlength="3" maxlength="2000" required placeholder="Bagikan analisis atau pertanyaan dengan sopan."></textarea></label>
                <div><button type="submit">Publikasikan</button></div>
            </form>
            @forelse($saham->posts as $post)
                <article class="metric post-card"><strong>{{ $post->nama_penulis }}</strong><span class="caption"> · {{ $post->created_at->format('d/m/Y H:i') }}</span><p>{{ $post->isi }}</p></article>
            @empty
                <div class="empty">Belum ada diskusi.</div>
            @endforelse
        </section>
    </section>

    <section class="panel" id="paper-trade">
        <div class="section-heading"><div><h2>Paper trading</h2><p class="caption">Uji alur transaksi tanpa uang sungguhan.</p></div></div>
        <div class="notice">SIMULASI SAJA — transaksi tidak dikirim ke broker dan tidak melibatkan uang sungguhan.</div>
        <form action="{{ route('paper-orders.store') }}" method="POST" class="grid-3" onsubmit="return confirm('Jalankan order simulasi ini? Tidak ada order sungguhan yang dikirim.');">
            @csrf<input type="hidden" name="saham_id" value="{{ $saham->id }}">
            <label>Sisi<select name="jenis"><option value="beli">Beli</option><option value="jual">Jual</option></select></label>
            <label>Jumlah lot<input type="number" min="1" name="jumlah_lot" required></label>
            <label>Harga simulasi per lembar (Rp)<input type="number" min="1" step="0.01" name="harga" value="{{ $saham->harga_beli ?: '' }}" required></label>
            <div><button class="green" type="submit">Eksekusi simulasi</button></div>
        </form>
        <details class="entry-form">
            <summary>Buat aturan auto-order simulasi</summary>
            <p class="caption">Aturan dicek saat Anda menekan “Periksa aturan” di halaman Transaksi. Otomatisasi latar-belakang/broker belum aktif.</p>
            <form action="{{ route('auto-orders.store') }}" method="POST" class="grid-3">
                @csrf<input type="hidden" name="saham_id" value="{{ $saham->id }}">
                <label>Sisi<select name="jenis"><option value="beli">Beli jika harga ≤ pemicu</option><option value="jual">Jual jika harga ≥ pemicu</option></select></label>
                <label>Jumlah lot<input type="number" min="1" name="jumlah_lot" required></label>
                <label>Harga pemicu (Rp)<input type="number" min="1" step="0.01" name="harga_pemicu" required></label>
                <div><button type="submit">Simpan aturan</button></div>
            </form>
        </details>
        <details class="entry-form">
            <summary>Buat alert harga</summary>
            <form action="{{ route('price-alerts.store') }}" method="POST" class="grid-3">
                @csrf<input type="hidden" name="saham_id" value="{{ $saham->id }}">
                <label>Kondisi<select name="kondisi"><option value="di_atas">Harga di atas</option><option value="di_bawah">Harga di bawah</option></select></label>
                <label>Harga target (Rp)<input type="number" min="1" step="0.01" name="harga_target" required></label>
                <div><button class="secondary" type="submit">Buat alert</button></div>
            </form>
        </details>
    </section>
@endsection

@push('scripts')
<script>
    const tabs = [...document.querySelectorAll('.issuer-tabs [role="tab"]')];
    const panels = [...document.querySelectorAll('.tab-panel')];
    function activateTab(panelId, updateHash) {
        const selectedTab = tabs.find(tab => tab.dataset.panel === panelId) || tabs[0];
        tabs.forEach(tab => {
            const selected = tab === selectedTab;
            tab.setAttribute('aria-selected', String(selected));
            tab.tabIndex = selected ? 0 : -1;
        });
        panels.forEach(panel => { panel.hidden = panel.id !== selectedTab.dataset.panel; });
        if (updateHash) history.replaceState(null, '', '#' + selectedTab.id);
    }
    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => activateTab(tab.dataset.panel, true));
        tab.addEventListener('keydown', event => {
            if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
            event.preventDefault();
            const direction = event.key === 'ArrowRight' ? 1 : -1;
            const nextTab = tabs[(index + direction + tabs.length) % tabs.length];
            activateTab(nextTab.dataset.panel, true);
            nextTab.focus();
        });
    });
    const initialTab = tabs.find(tab => tab.id === window.location.hash.slice(1));
    if (initialTab) activateTab(initialTab.dataset.panel, false);

    const chartCanvas = document.getElementById('issuer-chart');
    const candles = [];
    let historyRequestId = 0;
    let selectedChartType = 'candlestick';

    const candlestickPlugin = {
        id: 'issuerCandlesticks',
        afterDatasetsDraw(chart) {
            if (selectedChartType !== 'candlestick' || candles.length === 0 || !chart.chartArea) return;

            const { ctx, chartArea, scales } = chart;
            const xScale = scales.x;
            const yScale = scales.y;
            const candleWidth = Math.max(3, Math.min(12, (chartArea.width / candles.length) * 0.55));

            candles.forEach((candle, index) => {
                const x = xScale.getPixelForTick(index);
                const openY = yScale.getPixelForValue(candle.open);
                const closeY = yScale.getPixelForValue(candle.close);
                const color = candle.close >= candle.open ? '#34d399' : '#fb7185';

                ctx.strokeStyle = color;
                ctx.fillStyle = color;
                ctx.lineWidth = 1.5;
                ctx.beginPath();
                ctx.moveTo(x, yScale.getPixelForValue(candle.high));
                ctx.lineTo(x, yScale.getPixelForValue(candle.low));
                ctx.stroke();
                ctx.fillRect(x - candleWidth / 2, Math.min(openY, closeY), candleWidth, Math.max(1, Math.abs(closeY - openY)));
            });
        }
    };

    const issuerChart = new Chart(chartCanvas, {
        type: 'line',
        data: { labels: [], datasets: [{ label: 'OHLC historis', data: [], borderColor: '#38bdf8', backgroundColor: 'rgba(56,189,248,.12)', fill: true, tension: .2, pointRadius: 0 }] },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: { legend: { labels: { color: '#94a3b8' } } },
            scales: { x: { ticks: { color: '#94a3b8' } }, y: { ticks: { color: '#94a3b8' } } }
        },
        plugins: [candlestickPlugin]
    });

    const formatPrice = value => Number.isFinite(value)
        ? 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(value)
        : '—';

    const syncChart = () => {
        issuerChart.data.labels = candles.map(candle => candle.time);
        issuerChart.data.datasets[0].data = candles.map(candle => candle.close);
        issuerChart.data.datasets[0].label = `OHLC historis • ${selectedChartType}`;
        issuerChart.data.datasets[0].borderColor = selectedChartType === 'candlestick' ? 'transparent' : '#38bdf8';
        issuerChart.data.datasets[0].backgroundColor = selectedChartType === 'candlestick' ? 'transparent' : 'rgba(56,189,248,.12)';
        issuerChart.data.datasets[0].pointRadius = selectedChartType === 'candlestick' ? 0 : 2;

        const prices = candles.flatMap(candle => [candle.low, candle.high]);
        if (prices.length > 0) {
            const padding = Math.max(1, (Math.max(...prices) - Math.min(...prices)) * 0.1);
            issuerChart.options.scales.y.min = Math.max(0, Math.min(...prices) - padding);
            issuerChart.options.scales.y.max = Math.max(...prices) + padding;
        } else {
            delete issuerChart.options.scales.y.min;
            delete issuerChart.options.scales.y.max;
        }

        issuerChart.update('none');
    };

    const freshnessLabel = freshness => ({
        recent: 'relatif baru',
        stale: 'usang',
        unknown: 'tidak diketahui'
    }[freshness] || 'tidak diketahui');

    const loadHistoricalCandles = async () => {
        const requestId = ++historyRequestId;
        const timeframe = document.getElementById('chart-timeframe').value;
        const chartStatus = document.getElementById('chart-status');
        candles.length = 0;
        chartStatus.textContent = 'Memuat OHLC historis dari penyedia data...';
        syncChart();

        try {
            const symbol = @json($saham->kode_emiten);
            const response = await fetch('/api/harga-saham/' + encodeURIComponent(symbol)
                + '/historical?timeframe=' + encodeURIComponent(timeframe));
            const payload = await response.json();

            if (!response.ok || payload.status !== 'available' || !Array.isArray(payload.candles)) {
                throw new Error(payload.message || 'Penyedia tidak mengembalikan OHLC yang valid.');
            }
            if (requestId !== historyRequestId) return;

            candles.push(...payload.candles);
            syncChart();
            const retrievedAt = payload.retrieved_at
                ? new Date(payload.retrieved_at).toLocaleString('id-ID')
                : 'waktu pengambilan tidak tersedia';
            chartStatus.textContent = `${payload.source} • candle terakhir ${payload.last_updated} • data ${freshnessLabel(payload.freshness)} • diambil ${retrievedAt}`;
        } catch (error) {
            if (requestId !== historyRequestId) return;
            candles.length = 0;
            syncChart();
            chartStatus.textContent = `OHLC historis tidak tersedia • ${error.message}`;
        }
    };

    document.getElementById('chart-style').addEventListener('change', event => {
        selectedChartType = event.target.value;
        issuerChart.config.type = selectedChartType === 'bar' ? 'bar' : 'line';
        syncChart();
    });
    document.getElementById('chart-timeframe').addEventListener('change', loadHistoricalCandles);
    loadHistoricalCandles();

    fetch('/api/harga-saham/{{ rawurlencode($saham->kode_emiten) }}').then(response => {
        return response.json().then(payload => {
            if (!response.ok || payload.status !== 'available') {
                throw new Error(payload.message || 'Quote tidak tersedia');
            }

            return payload;
        });
    }).then(payload => {
        const quote = payload['Global Quote'] || {};
        const price = Number(quote['05. price']);
        if (!Number.isFinite(price) || price <= 0) throw new Error('Quote tidak valid');
        const open = Number(quote['02. open']);
        const high = Number(quote['03. high']);
        const low = Number(quote['04. low']);
        const changePercent = Number.parseFloat(String(quote['10. change percent'] || '').replace('%', ''));
        document.getElementById('detail-quote').textContent = formatPrice(price);
        const retrievedAt = payload.retrieved_at
            ? new Date(payload.retrieved_at).toLocaleString('id-ID')
            : 'waktu pengambilan tidak tersedia';
        const providerSymbol = String(quote['01. symbol'] || '').toUpperCase();
        const requestedSymbol = @json(strtoupper($saham->kode_emiten));
        const normalizedProviderSymbol = providerSymbol.replace(/\.JK$/, '');
        const normalizedRequestedSymbol = String(requestedSymbol).toUpperCase().replace(/\.JK$/, '');
        const symbolNote = normalizedProviderSymbol && normalizedProviderSymbol !== normalizedRequestedSymbol
            ? ` · simbol data ${providerSymbol}`
            : '';
        document.getElementById('quote-time').textContent = `· ${payload.source} · ${payload.last_updated || 'tanggal tidak tersedia'} · ${freshnessLabel(payload.freshness)} · diambil ${retrievedAt}${symbolNote}`;
        document.getElementById('quote-open').textContent = formatPrice(open);
        document.getElementById('quote-high').textContent = formatPrice(high);
        document.getElementById('quote-low').textContent = formatPrice(low);
        const change = document.getElementById('quote-change');
        change.textContent = Number.isFinite(changePercent) ? `${changePercent >= 0 ? '+' : ''}${changePercent.toFixed(2)}%` : '—';
        change.classList.toggle('positive', changePercent >= 0);
        change.classList.toggle('negative', changePercent < 0);
    }).catch(error => {
        document.getElementById('detail-quote').textContent = 'Tidak tersedia';
        document.getElementById('quote-time').textContent = error.message;
    });

    const periodButtons = [...document.querySelectorAll('.period-button')];
    periodButtons.forEach(button => button.addEventListener('click', () => {
        periodButtons.forEach(option => option.classList.toggle('active', option === button));
        const rows = [...document.querySelectorAll('#financial-table tbody tr[data-period]')];
        rows.forEach(row => { row.hidden = row.dataset.period !== button.dataset.period; });
        const anyVisible = rows.some(row => !row.hidden);
        const emptyRow = document.querySelector('.financial-empty');
        if (emptyRow) emptyRow.hidden = false;
        else if (!anyVisible) {
            const body = document.querySelector('#financial-table tbody');
            let noPeriodRow = body.querySelector('.period-empty');
            if (!noPeriodRow) {
                noPeriodRow = document.createElement('tr');
                noPeriodRow.className = 'period-empty';
                noPeriodRow.innerHTML = '<td colspan="8" class="empty">Belum ada laporan untuk periode ini.</td>';
                body.append(noPeriodRow);
            }
            noPeriodRow.hidden = false;
        } else {
            const noPeriodRow = document.querySelector('.period-empty');
            if (noPeriodRow) noPeriodRow.hidden = true;
        }
    }));
    document.querySelector('.period-button[data-period="kuartal"]').click();
</script>
@endpush
