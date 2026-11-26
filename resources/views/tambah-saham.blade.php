<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Saham - Uqi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: #0f172a; color: #e2e8f0; padding: 40px; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .card { background: #1e293b; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3); border: 1px solid #334155; width: 100%; max-width: 500px; }
        h2 { color: #f8fafc; font-size: 22px; font-weight: 700; margin-bottom: 20px; text-align: center; }
        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #94a3b8; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em; }
        input, select { width: 100%; padding: 10px 14px; background: #0f172a; border: 1px solid #334155; border-radius: 6px; color: #f8fafc; font-size: 14px; }
        input:focus { outline: none; border-color: #38bdf8; }
        .btn-simpan { background-color: #10b981; color: white; border: none; padding: 12px; width: 100%; border-radius: 6px; font-weight: 600; font-size: 14px; cursor: pointer; margin-top: 10px; transition: background 0.2s; }
        .btn-simpan:hover { background-color: #059669; }
        .btn-batal { display: block; text-align: center; margin-top: 12px; color: #94a3b8; text-decoration: none; font-size: 13px; }
        .btn-batal:hover { color: #f8fafc; }
    </style>
</head>
<body>

    <div class="card">
        <h2>➕ Tambah Saham Baru</h2>

        <form action="/saham" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label>Nama Pemilik / Investor</label>
                <input type="text" name="nama_pemilik" placeholder="Contoh: Syauqi" required>
            </div>

            <div class="form-group">
                <label>Kode Emiten</label>
                <input type="text" name="kode_emiten" placeholder="Contoh: BBCA, TLKM" required>
            </div>

            <div class="form-group">
                <label>Nama Perusahaan</label>
                <input type="text" name="nama_perusahaan" placeholder="Contoh: Bank Central Asia Tbk" required>
            </div>

            <div class="form-group">
                <label>Sektor</label>
                <input type="text" name="sektor" placeholder="Contoh: Financials" required>
            </div>
            <div class="form-group">
                <label>PER (Opsional)</label>
                <input type="number" step="0.0001" min="0" name="per" value="{{ old('per') }}" placeholder="Isi dari sumber terverifikasi">
            </div>
            <div class="form-group">
                <label>PBV (Opsional)</label>
                <input type="number" step="0.0001" min="0" name="pbv" value="{{ old('pbv') }}">
            </div>
            <div class="form-group">
                <label>ROE (%) (Opsional)</label>
                <input type="number" step="0.01" name="roe" value="{{ old('roe') }}">
            </div>
            <div class="form-group">
                <label>Pertumbuhan Pendapatan (%) (Opsional)</label>
                <input type="number" step="0.01" name="pertumbuhan_pendapatan" value="{{ old('pertumbuhan_pendapatan') }}">
            </div>
            <div class="form-group">
                <label>Rasio Utang / Ekuitas (Opsional)</label>
                <input type="number" step="0.0001" min="0" name="rasio_utang_ekuitas" value="{{ old('rasio_utang_ekuitas') }}">
            </div>
            <div class="form-group">
                <label>Kapitalisasi Pasar (Rp, Opsional)</label>
                <input type="number" step="0.01" min="0" name="kapitalisasi_pasar" value="{{ old('kapitalisasi_pasar') }}">
            </div>

            <div class="form-group">
                <label>Jumlah Lot</label>
                <input type="number" name="jumlah_lot" placeholder="Contoh: 10" required>
            </div>

            <div class="form-group">
                <label>Harga Beli (Per Lembar)</label>
                <input type="number" name="harga_beli" placeholder="Contoh: 10000" required>
                <div style="margin-bottom: 15px;">
    <label style="display: block; margin-bottom: 5px; font-weight: 600;">Dividen per Saham (Rp):</label>
    <input type="number" name="dividen_per_saham" value="{{ old('dividen_per_saham', $saham->dividen_per_saham ?? 0) }}" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #334155; background: #1e293b; color: #f8fafc;" placeholder="Contoh: 150">
    <small style="opacity: 0.7;">Masukkan estimasi dividen tahunan per lembar (opsional).</small>
</div>
            </div>

            <div class="form-group">
                <label>Tanggal Beli</label>
                <input type="date" name="tanggal_beli" required>
            </div>

            <div class="form-group">
                <label>Foto Bukti / Logo (Opsional)</label>
                <input type="file" name="foto" style="padding: 7px;">
            </div>

            <button type="submit" class="btn-simpan">Simpan Saham</button>
            <a href="/saham" class="btn-batal">Batal</a>
        </form>
    </div>

</body>
</html>
