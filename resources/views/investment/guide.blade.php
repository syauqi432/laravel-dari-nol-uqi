@extends('layouts.investment', ['activeNav' => 'profile'])

@section('title', 'Panduan Investor Pemula')

@section('content')
    <header class="page-head">
        <div><h1>Panduan Saham untuk Pemula</h1><div class="muted">Baca bisnisnya, pahami harganya, lalu pertimbangkan risikonya.</div></div>
        <a class="button secondary" href="{{ route('market.overview') }}">Kembali ke Beranda</a>
    </header>

    <div class="notice">
        <strong>Catatan data aplikasi:</strong> Quote dan chart memakai Alpha Vantage bila tersedia, lalu Yahoo Finance sebagai cadangan; IHSG bersumber dari Yahoo Finance. Data bukan jaminan real-time. Berita pasar berasal dari Google News RSS dan bukan pengumuman resmi IDX. Periksa sumber, tanggal, serta keterbukaan resmi emiten; orderbook belum tersedia dan rasio/laporan keuangan/corporate action dapat diisi manual.
    </div>

    <section class="panel">
        <h2>Mulai dari urutan sederhana ini</h2>
        <ol>
            <li><strong>Pahami bisnis dan laporan keuangannya.</strong> Apakah pendapatan, laba, dan arus kasnya masuk akal?</li>
            <li><strong>Bandingkan valuasi dan rasio.</strong> Cocokkan dengan perusahaan sejenis, jangan hanya melihat satu angka.</li>
            <li><strong>Periksa corporate action.</strong> Baca jadwal dan pengumuman resmi yang bisa memengaruhi saham.</li>
            <li><strong>Lihat harga dan likuiditas.</strong> Chart dan orderbook dapat membantu jika datanya tersedia dan mutakhir.</li>
            <li><strong>Rencanakan risiko.</strong> Tentukan jumlah yang sanggup ditanggung jika harga turun; jangan memakai uang kebutuhan dekat.</li>
        </ol>
    </section>

    <section class="panel">
        <h2>1. Orderbook — antrean beli dan jual</h2>
        <div class="grid-2">
            <div class="metric"><strong>Bid</strong><span class="muted">Antrean harga dari calon pembeli.</span></div>
            <div class="metric"><strong>Offer / Ask</strong><span class="muted">Antrean harga dari calon penjual.</span></div>
            <div class="metric"><strong>Lot</strong><span class="muted">Di BEI, 1 lot = 100 lembar saham.</span></div>
            <div class="metric"><strong>Frekuensi</strong><span class="muted">Biasanya banyaknya order pada harga itu. Cek definisi penyedia; belum tentu order sudah terjadi.</span></div>
        </div>
        <p><strong>Contoh:</strong> bid tertinggi Rp990 dan offer terendah Rp1.000 berarti selisihnya Rp10 (spread). Jika Anda memasang beli Rp990, order menunggu penjual yang bersedia melepas saham pada harga itu—belum tentu langsung terpenuhi.</p>
        <p class="muted">Gunakan orderbook untuk memahami antrean dan spread, bukan untuk menebak arah harga. Antrean dapat berubah atau dibatalkan.</p>
        <p class="caption">Di aplikasi ini, feed orderbook aktual belum tersedia.</p>
    </section>

    <section class="panel">
        <h2>2. Chart — membaca gerak harga</h2>
        <ul>
            <li><strong>Candlestick:</strong> satu batang merangkum harga buka, tertinggi, terendah, dan tutup dalam satu periode.</li>
            <li><strong>Timeframe:</strong> panjang periode untuk satu candle. Candle harian merangkum satu hari; candle mingguan merangkum satu minggu.</li>
            <li><strong>Volume:</strong> jumlah saham yang diperdagangkan. Volume membantu melihat apakah pergerakan disertai aktivitas.</li>
            <li><strong>Moving Average (MA):</strong> rata-rata harga beberapa periode untuk membantu melihat tren umum.</li>
            <li><strong>RSI:</strong> indikator momentum. Nilai tinggi/rendah bukan perintah pasti untuk membeli atau menjual.</li>
        </ul>
        <p><strong>Contoh:</strong> harga tampak naik, tetapi volume sangat tipis. Catat bahwa kenaikannya belum didukung aktivitas besar, lalu periksa informasi dan likuiditas lain.</p>
        <p class="caption">Chart OHLC historis tersedia jika API key dan simbol didukung penyedia. Data dapat tertunda; MA dan RSI belum diterapkan.</p>
    </section>

    <section class="panel">
        <h2>3. Key Stats — angka ringkas perusahaan</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Istilah</th><th>Arti singkat</th><th>Contoh cara membaca</th></tr></thead>
                <tbody>
                    <tr><td><strong>PER</strong></td><td>Harga saham dibanding laba per saham (EPS).</td><td>Harga Rp1.000 dan EPS Rp100 → PER 10x. Bandingkan dengan emiten sejenis.</td></tr>
                    <tr><td><strong>PBV</strong></td><td>Harga saham dibanding nilai buku per saham.</td><td>Menunjukkan perbandingan harga dengan nilai buku, bukan nilai “pasti” saham.</td></tr>
                    <tr><td><strong>ROE</strong></td><td>Laba bersih dibanding ekuitas, umumnya dalam persen.</td><td>ROE tinggi perlu dilihat bersama utang dan konsistensi laba.</td></tr>
                    <tr><td><strong>DER</strong></td><td>Utang dibanding ekuitas. Rumus rinci dapat berbeda antarpenyedia.</td><td>Periksa definisi dan bandingkan dalam sektor yang sama.</td></tr>
                    <tr><td><strong>Dividend Yield</strong></td><td>Dividen per saham ÷ harga saham × 100%.</td><td>Dividen Rp50 pada harga Rp1.000 → yield 5%; bukan janji dividen berikutnya.</td></tr>
                </tbody>
            </table>
        </div>
        <p class="caption">PER rendah atau yield tinggi tidak otomatis berarti saham murah atau aman. Rasio aplikasi dapat diisi manual; pastikan sumber dan tanggalnya.</p>
    </section>

    <section class="panel">
        <h2>4. Financial — membaca laporan keuangan</h2>
        <div class="grid-3">
            <div class="metric"><strong>Income Statement</strong><p class="muted">Laba rugi: pendapatan, biaya, laba operasi, dan laba bersih selama periode.</p><span class="caption">Tanya: apakah bisnis bertumbuh, dan dari mana labanya berasal?</span></div>
            <div class="metric"><strong>Balance Sheet</strong><p class="muted">Neraca: aset, kewajiban/utang, dan ekuitas pada suatu tanggal.</p><span class="caption">Tanya: apa yang dimiliki perusahaan dan berapa kewajibannya?</span></div>
            <div class="metric"><strong>Cash Flow</strong><p class="muted">Arus kas operasi, investasi, dan pendanaan.</p><span class="caption">Tanya: apakah operasional bisnis menghasilkan uang tunai?</span></div>
        </div>
        <p><strong>Contoh:</strong> laba bersih naik, tetapi arus kas operasi terus negatif. Periksa penyebabnya—misalnya piutang atau persediaan—sebelum menarik kesimpulan.</p>
        <p class="caption">Bandingkan beberapa periode dan gunakan laporan resmi. Data pada aplikasi saat ini dicatat manual.</p>
    </section>

    <section class="panel">
        <h2>5. Corporate Action — kejadian penting perusahaan</h2>
        <div class="grid-2">
            <div class="metric"><strong>Dividen</strong><p class="muted">Pembagian kas kepada pemegang saham. Periksa nilai, jadwal cum/ex, pencatatan, dan pembayaran. Dividen tidak dijamin berulang.</p></div>
            <div class="metric"><strong>Stock split</strong><p class="muted">Jumlah lembar bertambah dan harga per lembar menyesuaikan. Split sendiri tidak otomatis menambah nilai investasi.</p></div>
            <div class="metric"><strong>Right issue</strong><p class="muted">Penawaran saham baru kepada pemegang saham yang memenuhi syarat. Periksa rasio, harga pelaksanaan, tujuan dana, dan tenggat.</p></div>
            <div class="metric"><strong>RUPS</strong><p class="muted">Rapat pemegang saham untuk membahas atau memutuskan hal penting. Baca agenda dan hasil resminya.</p></div>
        </div>
        <p class="caption">Periksa pengumuman resmi emiten dan tanggalnya. Data corporate action di aplikasi belum terhubung otomatis.</p>
    </section>

    <section class="panel">
        <h2>Contoh alur sebelum membeli</h2>
        <p>Bayangkan Anda sedang mempelajari saham fiktif XYZ. Anda membandingkan PER dan ROE-nya dengan perusahaan sejenis, membaca laporan keuangan, memeriksa aksi korporasi, lalu melihat chart dan spread orderbook jika datanya tersedia. Jika ada informasi yang belum jelas, cari sumber resmi dulu—tidak perlu terburu-buru mengambil keputusan.</p>
        <div class="row">
            <a class="button" href="{{ route('screening.index') }}">Coba screener</a>
            <a class="button secondary" href="{{ route('saham.index') }}">Lihat portofolio</a>
            <a class="button secondary" href="{{ route('transactions.index') }}">Buka paper trading</a>
        </div>
        <p class="caption">Ini panduan edukasi, bukan rekomendasi beli atau jual. Harga dapat turun dan indikator tidak menjamin hasil.</p>
    </section>
@endsection
