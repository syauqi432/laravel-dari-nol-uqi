@extends('layouts.investment', ['activeNav' => 'transactions'])

@section('title', 'Transaksi & Portofolio')

@section('content')
    <header class="page-head"><div><h1>Transaksi & Portofolio</h1><div class="muted">Semua order di halaman ini adalah paper trading.</div></div><a class="button secondary" href="{{ route('saham.index') }}">Lihat ringkasan portofolio</a></header>
    <div class="notice">Mode SIMULASI — tidak ada order yang dikirim ke broker. Biaya asumsi: beli {{ number_format($buyFeeRate, 2, ',', '.') }}%, jual {{ number_format($sellFeeRate, 2, ',', '.') }}%. Atur melalui PAPER_TRADING_BUY_FEE_RATE dan PAPER_TRADING_SELL_FEE_RATE di .env; tarif broker sebenarnya berbeda-beda.</div>

    <section class="metrics">
        <div class="metric"><span class="muted">Emiten tersedia untuk simulasi</span><strong>{{ $sahams->count() }}</strong></div>
        <div class="metric"><span class="muted">Realized gain/loss paper</span><strong style="color:{{ $realizedGain >= 0 ? '#34d399' : '#fb7185' }}">Rp {{ number_format((float) $realizedGain, 0, ',', '.') }}</strong></div>
        <div class="metric"><span class="muted">Total biaya simulasi</span><strong>Rp {{ number_format((float) $feesPaid, 0, ',', '.') }}</strong></div>
        <div class="metric"><span class="muted">Order simulasi tersimpan</span><strong>{{ $orders->count() }}</strong></div>
    </section>

    <section class="panel" style="margin-top:16px">
        <h2>Buat order paper</h2>
        @if($sahams->isEmpty())
            <div class="notice">Tambahkan emiten ke akun Anda terlebih dahulu untuk memulai simulasi. <a href="{{ route('watchlist.index') }}">Buka Watchlist</a>.</div>
        @endif
        <form action="{{ route('paper-orders.store') }}" method="POST" class="grid-3" onsubmit="return confirm('Order ini hanya simulasi dan tidak mengirim order sungguhan. Lanjutkan?');">
            @csrf
            <label>Emiten<select name="saham_id" required>@foreach($sahams as $saham)<option value="{{ $saham->id }}">{{ $saham->kode_emiten }} · {{ $saham->nama_perusahaan }} ({{ $saham->jumlah_lot }} lot)</option>@endforeach</select></label>
            <label>Sisi<select name="jenis"><option value="beli">Beli</option><option value="jual">Jual</option></select></label>
            <label>Jumlah lot<input type="number" name="jumlah_lot" min="1" required></label>
            <label>Harga simulasi per lembar (Rp)<input type="number" name="harga" min="1" step="0.01" required></label>
            <div><button class="green" type="submit" @disabled($sahams->isEmpty())>Eksekusi paper order</button></div>
        </form>
    </section>

    <section class="panel">
        <h2>Auto-order & alert harga</h2>
        <p class="muted">Pemeriksaan menggunakan quote dari Alpha Vantage atau Yahoo Finance sebagai cadangan. Aturan berjalan hanya ketika tombol ditekan, dan tidak menjamin harga eksekusi nyata.</p>
        <form action="{{ route('market-rules.check') }}" method="POST" onsubmit="return confirm('Ambil quote dari penyedia dan periksa semua aturan aktif? Aturan yang terpenuhi dapat mengubah posisi paper trading.');">
            @csrf<button type="submit" class="secondary">Periksa aturan sekarang</button>
        </form>
        <div class="table-wrap" style="margin-top:14px"><table><thead><tr><th>Emiten</th><th>Jenis</th><th>Lot / target</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
            @foreach($autoOrders as $rule)
                <tr><td>{{ $rule->saham->kode_emiten }}</td><td>Auto {{ strtoupper($rule->jenis) }}</td><td>{{ $rule->jumlah_lot }} lot · Rp {{ number_format((float) $rule->harga_pemicu, 0, ',', '.') }}</td><td>{{ ucfirst($rule->status) }}</td><td>@if($rule->status === 'aktif')<form action="{{ route('auto-orders.cancel', $rule) }}" method="POST">@csrf<button class="red" type="submit">Batalkan</button></form>@else{{ $rule->dijalankan_pada?->format('d/m/Y H:i') ?? '—' }}@endif</td></tr>
            @endforeach
            @foreach($priceAlerts as $alert)
                <tr><td>{{ $alert->saham->kode_emiten }}</td><td>Alert {{ str_replace('_', ' ', $alert->kondisi) }}</td><td>Rp {{ number_format((float) $alert->harga_target, 0, ',', '.') }}</td><td>{{ ucfirst($alert->status) }}{{ $alert->terpicu_pada ? ' · '.$alert->terpicu_pada->format('d/m/Y H:i') : '' }}</td><td>—</td></tr>
            @endforeach
            @if($autoOrders->isEmpty() && $priceAlerts->isEmpty())<tr><td colspan="5" class="empty">Belum ada auto-order atau alert. Buat aturan dari halaman detail emiten.</td></tr>@endif
        </tbody></table></div>
    </section>

    <section class="panel">
        <h2>Riwayat order simulasi</h2>
        <div class="table-wrap"><table><thead><tr><th>Waktu</th><th>Emiten</th><th>Sisi</th><th>Lot</th><th>Harga</th><th>Nilai kotor</th><th>Biaya</th><th>Realized P/L bersih</th><th>Sumber</th></tr></thead><tbody>
            @forelse($orders as $order)
                <tr><td>{{ $order->created_at->format('d/m/Y H:i') }}</td><td>{{ $order->saham->kode_emiten }}</td><td>{{ strtoupper($order->jenis) }}</td><td>{{ $order->jumlah_lot }}</td><td>Rp {{ number_format((float) $order->harga, 0, ',', '.') }}</td><td>Rp {{ number_format($order->jumlah_lot * (float) $order->harga * 100, 0, ',', '.') }}</td><td>Rp {{ number_format((float) $order->biaya_transaksi, 0, ',', '.') }}</td><td style="color:{{ $order->laba_terealisasi >= 0 ? '#34d399' : '#fb7185' }}">Rp {{ number_format((float) $order->laba_terealisasi, 0, ',', '.') }}</td><td>{{ str_replace('_', ' ', $order->sumber_order) }}</td></tr>
            @empty
                <tr><td colspan="9" class="empty">Belum ada paper order.</td></tr>
            @endforelse
        </tbody></table></div>
    </section>
@endsection
