@extends('layouts.investment', ['activeNav' => 'watchlist'])

@section('title', 'Stock Screener')

@section('content')
    <header class="page-head"><div><h1>Stock Screener</h1><div class="muted">Penyaringan hanya mencakup emiten yang sudah dicatat di aplikasi.</div></div></header>
    <div class="notice">Metrik fundamental harus Anda masukkan atau verifikasi dari laporan resmi. Data teknikal (RSI, moving average, tren) dan historis belum tersedia dari feed saat ini; screener tidak membuat nilai otomatis.</div>
    <section class="panel">
        <form method="GET" action="{{ route('screening.index') }}" class="grid-3">
            <label>Sektor<select name="sektor"><option value="">Semua sektor</option>@foreach($sektorList as $sector)<option value="{{ $sector }}" @selected(($filters['sektor'] ?? '') === $sector)>{{ $sector }}</option>@endforeach</select></label>
            <label>PER maksimum<input type="number" step="0.01" min="0" name="per_max" value="{{ $filters['per_max'] ?? '' }}" placeholder="contoh 20"></label>
            <label>PBV maksimum<input type="number" step="0.01" min="0" name="pbv_max" value="{{ $filters['pbv_max'] ?? '' }}" placeholder="contoh 3"></label>
            <label>ROE minimum (%)<input type="number" step="0.01" name="roe_min" value="{{ $filters['roe_min'] ?? '' }}"></label>
            <label>Pertumbuhan pendapatan min. (%)<input type="number" step="0.01" name="growth_min" value="{{ $filters['growth_min'] ?? '' }}"></label>
            <label>Urutkan<select name="sort">@foreach(['kode'=>'Kode','nama'=>'Nama','roe'=>'ROE','per'=>'PER','pbv'=>'PBV'] as $value=>$label)<option value="{{ $value }}" @selected(($filters['sort'] ?? 'kode') === $value)>{{ $label }}</option>@endforeach</select></label>
            <div class="row"><button type="submit">Terapkan filter</button><a class="button secondary" href="{{ route('screening.index') }}">Reset</a></div>
        </form>
    </section>
    <section class="panel">
        <div class="table-wrap"><table><thead><tr><th>Emiten</th><th>Sektor</th><th>PER</th><th>PBV</th><th>ROE</th><th>Pertumbuhan pendapatan</th></tr></thead><tbody>
            @forelse($sahams as $saham)
                <tr><td><a href="{{ route('issuers.show', $saham->id) }}"><strong>{{ $saham->kode_emiten }}</strong></a></td><td>{{ $saham->sektor }}</td><td>{{ $saham->per ?? '—' }}</td><td>{{ $saham->pbv ?? '—' }}</td><td>{{ $saham->roe === null ? '—' : $saham->roe.'%' }}</td><td>{{ $saham->pertumbuhan_pendapatan === null ? '—' : $saham->pertumbuhan_pendapatan.'%' }}</td></tr>
            @empty
                <tr><td colspan="6"><div class="empty-state"><span class="eyebrow">Belum ada hasil</span><h3>Belum ada emiten yang cocok</h3><p>Ubah filter atau tambahkan emiten dan data fundamentalnya. Nilai yang kosong tidak dianggap nol oleh screener.</p><a class="button secondary" href="{{ route('watchlist.index') }}">Kelola emiten pantauan</a></div></td></tr>
            @endforelse
        </tbody></table></div>
    </section>
@endsection
