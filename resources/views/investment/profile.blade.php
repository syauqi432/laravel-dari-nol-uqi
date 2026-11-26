@extends('layouts.investment', ['activeNav' => 'profile'])

@section('title', 'Profil & Pengaturan')

@section('content')
    <header class="page-head"><div><h1>Profil & Pengaturan</h1><div class="muted">Status aplikasi dan batasan fitur saat ini.</div></div></header>
    <section class="panel">
        <h2>Status akun</h2>
        <p class="muted">Anda masuk sebagai <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }}). Pendaftaran akun tersedia, dan setiap akun hanya dapat mengakses catatan serta portofolionya sendiri. Jangan bagikan kata sandi Anda.</p>
    </section>
    <section class="panel">
        <h2>Koneksi market</h2>
        <p class="muted">Quote dan candlestick mencoba Alpha Vantage lalu beralih ke Yahoo Finance jika sumber utama tidak tersedia. IHSG memakai Yahoo Finance, sedangkan berita pasar dihimpun dari Google News RSS. Data dapat tertunda; periksa sumber serta tanggalnya. Orderbook, keterbukaan resmi IDX, dan feed aksi korporasi belum tersambung.</p>
    </section>
    <section class="panel">
        <h2>Transaksi & keamanan</h2>
        <p class="muted">Mode hanya paper trading. Biaya beli/jual adalah asumsi simulasi yang dapat diatur melalui PAPER_TRADING_BUY_FEE_RATE dan PAPER_TRADING_SELL_FEE_RATE di .env. Tidak ada order ke broker.</p>
        <a class="button" href="{{ route('transactions.index') }}">Buka transaksi simulasi</a>
    </section>
    <section class="panel">
        <h2>Profil risiko</h2>
        <p class="muted">Jawab lima pertanyaan untuk memahami kecenderungan toleransi risiko Anda. Hasil ini bersifat edukatif, bukan rekomendasi investasi atau jaminan keuntungan. Jawaban tidak disimpan; hanya skor ringkasan yang tersimpan di akun Anda.</p>
        <p class="caption">Rentang hasil: 5–9 konservatif, 10–15 moderat, dan 16–20 agresif. Penilaian ini sederhana dan tidak menggantikan pertimbangan pribadi atau saran profesional.</p>
        @if($riskProfile)
            <div class="risk-profile-result">
                <div>
                    <span class="muted">Profil risiko saat ini</span>
                    <h3>{{ $riskProfile['category'] }} <span class="muted">· {{ $riskProfile['score'] }} dari 20</span></h3>
                    <p class="muted">{{ $riskProfile['description'] }}</p>
                </div>
            </div>
        @else
            <div class="notice">Anda belum mengisi penilaian profil risiko.</div>
        @endif

        <form action="{{ route('risk-profile.store') }}" method="POST" class="risk-profile-form">
            @csrf
            <fieldset class="risk-question">
                <legend>1. Kapan Anda memperkirakan akan membutuhkan kembali dana ini?</legend>
                <div class="risk-options">
                    @foreach(['kurang_1_tahun' => 'Kurang dari 1 tahun', '1_3_tahun' => '1–3 tahun', '3_5_tahun' => '3–5 tahun', 'lebih_5_tahun' => 'Lebih dari 5 tahun'] as $value => $label)
                        <label class="risk-option"><input type="radio" name="jangka_waktu" value="{{ $value }}" @checked(old('jangka_waktu') === $value)>{{ $label }}</label>
                    @endforeach
                </div>
                @error('jangka_waktu') <div class="risk-error">{{ $message }}</div> @enderror
            </fieldset>

            <fieldset class="risk-question">
                <legend>2. Jika nilai investasi turun cukup banyak, apa yang paling mungkin Anda lakukan?</legend>
                <div class="risk-options">
                    @foreach(['jual_semua' => 'Menjual seluruh investasi', 'jual_sebagian' => 'Menjual sebagian', 'tahan' => 'Tetap menahan dan mengevaluasi', 'tambah_investasi' => 'Mempertimbangkan menambah investasi'] as $value => $label)
                        <label class="risk-option"><input type="radio" name="reaksi_rugi" value="{{ $value }}" @checked(old('reaksi_rugi') === $value)>{{ $label }}</label>
                    @endforeach
                </div>
                @error('reaksi_rugi') <div class="risk-error">{{ $message }}</div> @enderror
            </fieldset>

            <fieldset class="risk-question">
                <legend>3. Seberapa nyaman Anda menghadapi naik-turun nilai investasi?</legend>
                <div class="risk-options">
                    @foreach(['tidak_nyaman' => 'Tidak nyaman dengan penurunan', 'sedikit_turun' => 'Hanya nyaman dengan penurunan kecil', 'fluktuasi_wajar' => 'Dapat menerima fluktuasi wajar', 'nyaman_turun_besar' => 'Dapat menerima penurunan besar'] as $value => $label)
                        <label class="risk-option"><input type="radio" name="kenyamanan_fluktuasi" value="{{ $value }}" @checked(old('kenyamanan_fluktuasi') === $value)>{{ $label }}</label>
                    @endforeach
                </div>
                @error('kenyamanan_fluktuasi') <div class="risk-error">{{ $message }}</div> @enderror
            </fieldset>

            <fieldset class="risk-question">
                <legend>4. Kapan dana yang diinvestasikan mungkin diperlukan untuk kebutuhan penting?</legend>
                <div class="risk-options">
                    @foreach(['kurang_1_tahun' => 'Kurang dari 1 tahun', '1_3_tahun' => '1–3 tahun', '3_5_tahun' => '3–5 tahun', 'lebih_5_tahun' => 'Lebih dari 5 tahun'] as $value => $label)
                        <label class="risk-option"><input type="radio" name="kebutuhan_dana" value="{{ $value }}" @checked(old('kebutuhan_dana') === $value)>{{ $label }}</label>
                    @endforeach
                </div>
                @error('kebutuhan_dana') <div class="risk-error">{{ $message }}</div> @enderror
            </fieldset>

            <fieldset class="risk-question">
                <legend>5. Bagaimana pengalaman Anda berinvestasi saat pasar bergejolak?</legend>
                <div class="risk-options">
                    @foreach(['pemula' => 'Belum pernah berinvestasi', 'pernah' => 'Pernah beberapa kali', 'berpengalaman' => 'Sudah berpengalaman beberapa tahun', 'alami_koreksi' => 'Pernah menghadapi penurunan pasar besar'] as $value => $label)
                        <label class="risk-option"><input type="radio" name="pengalaman" value="{{ $value }}" @checked(old('pengalaman') === $value)>{{ $label }}</label>
                    @endforeach
                </div>
                @error('pengalaman') <div class="risk-error">{{ $message }}</div> @enderror
            </fieldset>
            <button type="submit">Hitung profil risiko</button>
        </form>

        <a class="button secondary" href="{{ route('guide.index') }}">Baca panduan investor pemula</a>
    </section>
@endsection
