@extends('layouts.investment', ['activeNav' => 'watchlist'])

@section('title', 'Watchlist')

@section('content')
    <header class="page-head">
        <div><h1>Watchlist</h1><div class="muted">Pantau emiten yang Anda tandai sebagai favorit.</div></div>
        <a class="button secondary" href="{{ route('screening.index') }}">Screening saham</a>
    </header>

    <section class="panel">
        <h2>Tambah emiten ke watchlist</h2>
        <form action="{{ route('watchlist.store') }}" method="POST" class="grid-3">
            @csrf
            <label>Kode emiten<input name="kode_emiten" maxlength="12" placeholder="BBCA" required></label>
            <label>Nama perusahaan<input name="nama_perusahaan" maxlength="255" required></label>
            <label>Sektor<input name="sektor" maxlength="255" required></label>
            <div><button type="submit">Tambah pantauan</button></div>
        </form>
        <p class="caption">Emiten baru ditambahkan sebagai pantauan tanpa posisi atau modal.</p>
    </section>
    <section class="panel">
        <h2>Daftar pantauan</h2>
        <div class="table-wrap">
            <table><thead><tr><th>Emiten</th><th>Perusahaan</th><th>Sektor</th><th>Harga terakhir</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($sahams as $saham)
                        <tr data-symbol="{{ $saham->kode_emiten }}">
                            <td><strong>{{ $saham->kode_emiten }}</strong></td><td>{{ $saham->nama_perusahaan }}</td><td>{{ $saham->sektor }}</td>
                            <td class="quote-price muted">Mengambil quote...</td>
                            <td class="row"><a href="{{ route('issuers.show', $saham->id) }}">Detail</a>
                                <form action="{{ url('/saham/'.$saham->id.'/favorite') }}" method="POST">@csrf<button class="secondary" type="submit">Hapus favorit</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state"><span class="eyebrow">Mulai memantau</span><h3>Watchlist Anda masih kosong</h3><p>Isi kode emiten, nama perusahaan, dan sektor pada formulir di atas. Saham akan ditambahkan sebagai pantauan tanpa membuat posisi investasi.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    <section class="panel">
        <h2>Alert harga</h2>
        <p class="muted">Atur target di halaman detail emiten. Alert diperiksa saat Anda memilih “Periksa aturan” pada halaman Transaksi. Notifikasi latar-belakang belum aktif.</p>
        <a href="{{ route('transactions.index') }}">Kelola alert dan aturan</a>
    </section>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('tr[data-symbol]').forEach(async row => {
        try {
            const response = await fetch('/api/harga-saham/' + encodeURIComponent(row.dataset.symbol));
            const payload = await response.json();
            if (!response.ok || payload.status !== 'available') {
                throw new Error(payload.message || 'Quote tidak tersedia');
            }
            const quote = payload['Global Quote'] || {};
            const price = Number(quote['05. price']);
            row.querySelector('.quote-price').textContent = Number.isFinite(price) && price > 0
                ? `Rp ${new Intl.NumberFormat('id-ID').format(price)} · ${payload.source} · ${payload.last_updated || 'tanggal tidak diketahui'} (${payload.freshness || 'kesegaran tidak diketahui'})`
                : 'Quote tidak tersedia';
        } catch (error) {
            row.querySelector('.quote-price').textContent = error.message || 'Quote tidak tersedia';
        }
    });
</script>
@endpush
