@extends('layouts.investment', ['activeNav' => 'market'])

@section('title', 'Pasar & Pantauan')

@section('content')
    <header class="page-head">
        <div>
            <span class="eyebrow">Ringkasan harian · {{ now()->format('d/m/Y') }}</span>
            <h1>Pasar &amp; pantauan</h1>
            <div class="muted">Lihat saham pilihan Anda, bandingkan pergerakan, dan lanjutkan riset dari satu tempat.</div>
        </div>
        <div class="row"><a class="button secondary" href="{{ route('guide.index') }}">Panduan investor</a><a class="button" href="{{ route('screening.index') }}">Saring saham</a></div>
    </header>

    <div class="notice"><strong>Transparansi data:</strong> quote dan IHSG memakai Alpha Vantage bila tersedia, dengan Yahoo Finance sebagai cadangan. Berita dihimpun Google News RSS; bukan keterbukaan resmi IDX. Data dapat tertunda, dan orderbook belum tersedia.</div>

    @if($sahams->isEmpty())
        <section class="panel empty-state">
            <span class="eyebrow">Mulai dari sini</span>
            <h2>Bangun daftar pantauan pertama Anda</h2>
            <p>Tambahkan emiten untuk melihat quote, perubahan harga, dan ringkasan informasi yang tersimpan di akun Anda.</p>
            <div class="row"><a class="button" href="{{ route('watchlist.index') }}">Tambah emiten</a><a class="button secondary" href="{{ route('guide.index') }}">Pelajari dasar saham</a></div>
        </section>
    @endif

    <section class="metrics">
        <div class="metric"><span class="muted">Emiten dipantau</span><strong>{{ $sahams->count() }}</strong></div>
        <div class="metric"><span class="muted">Dalam watchlist</span><strong>{{ $favoriteCount }}</strong></div>
        <div class="metric"><span class="muted">Indeks pasar · IHSG</span><strong id="market-index-value" aria-live="polite">Memuat...</strong><span id="market-index-change" class="caption">Mengambil data indeks...</span><span id="market-index-meta" class="caption">Yahoo Finance</span></div>
    </section>

    <div class="grid-2">
        <section class="panel">
            <div class="section-heading"><div><h2>Penguat pantauan</h2><p class="caption">Perubahan positif dari emiten yang Anda simpan.</p></div><span class="badge">Maks. 6 quote</span></div>
            <div class="table-wrap compact-table"><table><thead><tr><th>Emiten</th><th>Harga</th><th>Perubahan</th></tr></thead><tbody id="gainers"><tr><td colspan="3" class="empty">Memuat quote...</td></tr></tbody></table></div>
        </section>
        <section class="panel">
            <div class="section-heading"><div><h2>Pelemah pantauan</h2><p class="caption">Perubahan negatif dari emiten yang Anda simpan.</p></div><span class="badge">Maks. 6 quote</span></div>
            <div class="table-wrap compact-table"><table><thead><tr><th>Emiten</th><th>Harga</th><th>Perubahan</th></tr></thead><tbody id="losers"><tr><td colspan="3" class="empty">Memuat quote...</td></tr></tbody></table></div>
        </section>
    </div>
    <section class="panel"><div class="section-heading"><div><h2>Harga terakhir</h2><p class="caption">Hanya emiten yang tersimpan di akun Anda, bukan seluruh saham di bursa.</p></div></div><div id="quote-list" class="grid-3"><span class="muted">Memuat quote...</span></div></section>

    <div class="grid-2">
        <section class="panel">
            <div class="section-heading"><h2>Berita pasar</h2><span id="market-news-status" class="badge" aria-live="polite">Memuat</span></div>
            <div id="market-news-list" class="market-news-list" aria-live="polite"><p class="muted">Mengambil berita pasar...</p></div>
            <p class="caption">Sumber: Google News RSS. Buka pengumuman resmi emiten/IDX sebelum mengambil keputusan.</p>
        </section>
        <section class="panel">
            <div class="section-heading"><h2>Agenda aksi korporasi</h2><span class="badge">{{ $upcomingActionCount }} mendatang</span></div>
            <p class="muted">{{ $upcomingActionCount }} catatan akan datang dari aksi korporasi yang tersimpan di aplikasi. Data ini dimasukkan pengguna dan bukan feed resmi bursa.</p>
            <a class="button secondary" href="{{ route('corporate-actions.index') }}">Buka kalender aksi</a>
        </section>
    </div>
@endsection

@push('scripts')
<script>
    const formatIDR = value => 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(value);
    const formatIndex = value => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(value);
    const indexValue = document.getElementById('market-index-value');
    const indexChange = document.getElementById('market-index-change');
    const indexMeta = document.getElementById('market-index-meta');
    fetch('/api/market/index', { headers: { Accept: 'application/json' } }).then(async response => {
        const payload = await response.json();
        if (!response.ok || payload.status !== 'available') throw new Error(payload.message || 'IHSG tidak tersedia.');
        indexValue.textContent = formatIndex(Number(payload.value));
        if (payload.change_percent !== null && Number.isFinite(Number(payload.change_percent))) {
            const changePercent = Number(payload.change_percent);
            const changePoints = Number(payload.change);
            indexChange.className = `caption ${changePercent >= 0 ? 'positive' : 'negative'}`;
            indexChange.textContent = `${changePoints >= 0 ? '+' : ''}${formatIndex(changePoints)} poin (${changePercent >= 0 ? '+' : ''}${changePercent.toFixed(2)}%)`;
        } else {
            indexChange.textContent = 'Perubahan indeks tidak tersedia';
        }
        indexMeta.textContent = `${payload.source} · ${payload.last_updated} · ${payload.freshness}`;
    }).catch(error => {
        indexValue.textContent = 'Tidak tersedia';
        indexChange.textContent = error.message || 'IHSG tidak dapat dimuat.';
        indexMeta.textContent = 'Coba muat ulang halaman.';
    });

    const newsList = document.getElementById('market-news-list');
    const newsStatus = document.getElementById('market-news-status');
    fetch('/api/market/news', { headers: { Accept: 'application/json' } }).then(async response => {
        const payload = await response.json();
        if (!response.ok || payload.status !== 'available') throw new Error(payload.message || 'Berita pasar tidak tersedia.');
        newsStatus.textContent = payload.source;
        newsList.replaceChildren();
        payload.headlines.forEach(headline => {
            const article = document.createElement('article');
            article.className = 'market-news-item';
            const link = document.createElement('a');
            link.href = headline.url;
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            link.textContent = headline.title;
            const metadata = document.createElement('div');
            metadata.className = 'market-news-meta';
            const source = document.createElement('span');
            source.textContent = headline.source;
            metadata.append(source);
            if (headline.published_at) {
                const date = document.createElement('time');
                date.dateTime = headline.published_at;
                date.textContent = new Date(headline.published_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
                metadata.append(date);
            }
            article.append(link, metadata);
            newsList.append(article);
        });
    }).catch(error => {
        newsStatus.textContent = 'Tidak tersedia';
        newsStatus.classList.add('unavailable');
        const message = document.createElement('p');
        message.className = 'muted';
        message.textContent = error.message || 'Berita pasar tidak dapat dimuat saat ini.';
        newsList.replaceChildren(message);
    });

    const trackedStocks = @json($sahams->take(6)->map(fn ($saham) => ['id' => $saham->id, 'symbol' => $saham->kode_emiten, 'name' => $saham->nama_perusahaan]));
    const quotes = [];
    const quoteList = document.getElementById('quote-list');
    quoteList.replaceChildren();
    if (!trackedStocks.length) {
        const emptyState = document.createElement('div');
        emptyState.className = 'empty-state';
        const message = document.createElement('p');
        message.textContent = 'Belum ada emiten dalam pantauan. Tambahkan saham pilihan untuk mulai melihat quote.';
        const link = document.createElement('a');
        link.className = 'button secondary';
        link.href = @json(route('watchlist.index'));
        link.textContent = 'Buka Watchlist';
        emptyState.append(message, link);
        quoteList.append(emptyState);
    }

    Promise.all(trackedStocks.map(async stock => {
        const card = document.createElement('a');
        card.className = 'quote-card';
        card.href = `/emiten/${stock.id}`;
        const heading = document.createElement('div');
        heading.className = 'quote-card-heading';
        const identity = document.createElement('div');
        identity.className = 'quote-card-identity';
        const symbol = document.createElement('span');
        symbol.className = 'quote-card-symbol';
        symbol.textContent = stock.symbol;
        const company = document.createElement('span');
        company.className = 'quote-card-company';
        company.textContent = stock.name;
        identity.append(symbol, company);
        const status = document.createElement('span');
        status.className = 'badge quote-card-status';
        status.setAttribute('aria-live', 'polite');
        status.textContent = 'Memuat';
        heading.append(identity, status);
        const priceDisplay = document.createElement('strong');
        priceDisplay.className = 'quote-card-price';
        priceDisplay.textContent = '—';
        const changeDisplay = document.createElement('span');
        changeDisplay.className = 'quote-card-change';
        changeDisplay.textContent = 'Mengambil quote...';
        const meta = document.createElement('span');
        meta.className = 'quote-card-meta';
        meta.textContent = 'Menunggu data penyedia';
        card.append(heading, priceDisplay, changeDisplay, meta);
        quoteList.append(card);
        try {
            const response = await fetch('/api/harga-saham/' + encodeURIComponent(stock.symbol));
            if (!response.ok) throw new Error('Quote tidak tersedia');
            const payload = await response.json();
            if (payload.status !== 'available') throw new Error(payload.message || 'Quote tidak tersedia');
            const quote = payload['Global Quote'] || {};
            const price = Number(quote['05. price']);
            const changePercent = Number.parseFloat(String(quote['10. change percent'] || '').replace('%', ''));
            if (!Number.isFinite(price) || price <= 0) throw new Error('Quote tidak valid');
            status.textContent = 'Tersedia';
            priceDisplay.textContent = formatIDR(price);
            if (Number.isFinite(changePercent)) {
                changeDisplay.className = `quote-card-change ${changePercent >= 0 ? 'positive' : 'negative'}`;
                changeDisplay.textContent = `${changePercent >= 0 ? '+' : ''}${changePercent.toFixed(2)}% hari ini`;
                quotes.push({ ...stock, price, changePercent });
            } else {
                changeDisplay.textContent = 'Perubahan harian tidak tersedia';
            }
            const providerSymbol = String(quote['01. symbol'] || '').toUpperCase();
            const requestedSymbol = String(stock.symbol || '').toUpperCase().replace(/\.JK$/, '');
            const normalizedProviderSymbol = providerSymbol.replace(/\.JK$/, '');
            const symbolNote = normalizedProviderSymbol && normalizedProviderSymbol !== requestedSymbol
                ? ` · simbol data ${providerSymbol}`
                : '';
            meta.textContent = `${payload.source || 'Sumber tidak diketahui'} · ${payload.last_updated || 'Waktu tidak diketahui'} · ${payload.freshness || 'Kesegaran tidak diketahui'}${symbolNote}`;
        } catch (error) {
            status.classList.add('unavailable');
            status.textContent = 'Belum tersedia';
            changeDisplay.textContent = 'Quote belum tersedia dari penyedia.';
            meta.textContent = error.message || 'Coba lagi nanti atau buka detail emiten untuk status data.';
        }
    })).then(() => {
        const descending = [...quotes].sort((a, b) => b.changePercent - a.changePercent);
        const ascending = [...quotes].sort((a, b) => a.changePercent - b.changePercent);
        const renderRows = (target, items, direction) => {
            target.replaceChildren();
            if (!items.length) {
                const row = document.createElement('tr');
                const cell = document.createElement('td');
                cell.colSpan = 3;
                cell.className = 'empty';
                cell.textContent = !trackedStocks.length
                    ? 'Tambahkan emiten ke pantauan untuk melihat perubahan.'
                    : !quotes.length
                        ? 'Quote perubahan belum tersedia dari penyedia saat ini.'
                        : `Belum ada emiten dengan perubahan ${direction} pada quote yang tersedia.`;
                row.append(cell);
                target.append(row);
                return;
            }
            items.slice(0, 5).forEach(item => {
                const row = document.createElement('tr');
                const issuer = document.createElement('td');
                const link = document.createElement('a');
                link.href = `/emiten/${item.id}`;
                link.textContent = item.symbol;
                issuer.append(link);
                const price = document.createElement('td');
                price.textContent = formatIDR(item.price);
                const change = document.createElement('td');
                change.textContent = `${item.changePercent >= 0 ? '+' : ''}${item.changePercent.toFixed(2)}%`;
                change.className = item.changePercent >= 0 ? 'positive' : 'negative';
                row.append(issuer, price, change);
                target.append(row);
            });
        };
        renderRows(document.getElementById('gainers'), descending.filter(item => item.changePercent > 0), 'positif');
        renderRows(document.getElementById('losers'), ascending.filter(item => item.changePercent < 0), 'negatif');
    });
</script>
@endpush
