@extends('layouts.investment', ['activeNav' => 'market'])

@section('title', 'Agenda Aksi Korporasi')

@section('content')
    <header class="page-head">
        <div>
            <h1>Agenda aksi korporasi</h1>
            <div class="muted">Pantau jadwal dividen, RUPS, right issue, stock split, dan catatan lain.</div>
        </div>
        <a class="button secondary" href="{{ route('market.overview') }}">Kembali ke Market</a>
    </header>

    <div class="notice">
        Agenda ini hanya menggabungkan catatan yang dimasukkan ke aplikasi. Bukan feed resmi atau notifikasi dari bursa; selalu verifikasi jadwal dan sumber ke keterbukaan informasi emiten.
    </div>

    <section class="metrics">
        <div class="metric"><span class="muted">Catatan akan datang</span><strong>{{ $upcomingCount }}</strong></div>
        <div class="metric"><span class="muted">Catatan sudah lewat</span><strong>{{ $pastCount }}</strong></div>
        <div class="metric"><span class="muted">Total catatan</span><strong>{{ $totalCount }}</strong></div>
    </section>

    <nav class="subnav" aria-label="Filter agenda">
        <a href="{{ route('corporate-actions.index', ['waktu' => 'upcoming']) }}" class="{{ $filter === 'upcoming' ? 'active' : '' }}">Akan datang</a>
        <a href="{{ route('corporate-actions.index', ['waktu' => 'past']) }}" class="{{ $filter === 'past' ? 'active' : '' }}">Sudah lewat</a>
        <a href="{{ route('corporate-actions.index', ['waktu' => 'all']) }}" class="{{ $filter === 'all' ? 'active' : '' }}">Semua catatan</a>
    </nav>

    <section class="panel">
        <h2>
            @if($filter === 'upcoming')
                Aksi korporasi mendatang
            @elseif($filter === 'past')
                Aksi korporasi yang sudah lewat
            @else
                Semua aksi korporasi
            @endif
        </h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Tanggal</th><th>Emiten</th><th>Jenis</th><th>Status</th><th>Nilai per saham</th><th>Keterangan</th><th>Sumber</th></tr>
                </thead>
                <tbody>
                    @forelse($actions as $action)
                        <tr>
                            <td>{{ $action->tanggal_penting?->format('d/m/Y') ?? 'Tanggal belum ditentukan' }}</td>
                            <td><a href="{{ route('issuers.show', $action->saham) }}"><strong>{{ $action->saham->kode_emiten }}</strong></a></td>
                            <td>{{ $action->jenis }}</td>
                            <td>{{ $action->status ?: '—' }}</td>
                            <td>{{ $action->nilai_per_saham === null ? '—' : 'Rp '.number_format((float) $action->nilai_per_saham, 2, ',', '.') }}</td>
                            <td>{{ $action->keterangan ?: '—' }}</td>
                            <td>{{ $action->sumber ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <span class="eyebrow">Belum ada agenda</span>
                                    <h3>{{ $filter === 'upcoming' ? 'Tidak ada catatan mendatang' : ($filter === 'past' ? 'Tidak ada catatan lampau' : 'Belum ada catatan aksi korporasi') }}</h3>
                                    <p>Catatan aksi korporasi tersimpan per emiten dan perlu ditambahkan dari tab News &amp; Disclosure. Verifikasi setiap tanggal dengan pengumuman resmi.</p>
                                    <a class="button secondary" href="{{ route('watchlist.index') }}">Pilih emiten dari Watchlist</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($actions->previousPageUrl() || $actions->nextPageUrl())
            <div class="row" style="justify-content:space-between;margin-top:14px">
                @if($actions->previousPageUrl())
                    <a class="button secondary" href="{{ $actions->previousPageUrl() }}">Catatan lebih baru</a>
                @else
                    <span></span>
                @endif
                @if($actions->nextPageUrl())
                    <a class="button secondary" href="{{ $actions->nextPageUrl() }}">Catatan lebih lama</a>
                @endif
            </div>
        @endif
    </section>
@endsection
