<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Saham - Uqi</title>
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
        .btn-simpan { background-color: #0284c7; color: white; border: none; padding: 12px; width: 100%; border-radius: 6px; font-weight: 600; font-size: 14px; cursor: pointer; margin-top: 10px; transition: background 0.2s; }
        .btn-simpan:hover { background-color: #0369a1; }
        .btn-batal { display: block; text-align: center; margin-top: 12px; color: #94a3b8; text-decoration: none; font-size: 13px; }
        .btn-batal:hover { color: #f8fafc; }
    </style>
</head>
<body>

    <div class="card">
        <h2>✏️ Edit Data Saham</h2>

        <form action="/saham/{{ $saham->id }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Nama Pemilik / Investor</label>
                <input type="text" name="nama_pemilik" value="{{ $saham->nama_pemilik }}" required>
            </div>

            <div class="form-group">
                <label>Kode Emiten</label>
                <input type="text" name="kode_emiten" value="{{ $saham->kode_emiten }}" required>
            </div>

            <div class="form-group">
                <label>Nama Perusahaan</label>
                <input type="text" name="nama_perusahaan" value="{{ $saham->nama_perusahaan }}" required>
            </div>

            <div class="form-group">
                <label>Sektor</label>
                <input type="text" name="sektor" value="{{ $saham->sektor }}" required>
            </div>

            <div class="form-group">
                <label>Jumlah Lot</label>
                <input type="number" name="jumlah_lot" value="{{ $saham->jumlah_lot }}" required>
            </div>

            <div class="form-group">
                <label>Harga Beli (Per Lembar)</label>
                <input type="number" name="harga_beli" value="{{ $saham->harga_beli }}" required>
                <div style="margin-bottom: 15px;">
    <label style="display: block; margin-bottom: 5px; font-weight: 600;">Dividen per Saham (Rp):</label>
    <input type="number" name="dividen_per_saham" value="{{ old('dividen_per_saham', $saham->dividen_per_saham ?? 0) }}" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #334155; background: #1e293b; color: #f8fafc;" placeholder="Contoh: 150">
    <small style="opacity: 0.7;">Masukkan estimasi dividen tahunan per lembar (opsional).</small>
</div>
            </div>

            <div class="form-group">
                <label>Tanggal Beli</label>
                <input type="date" name="tanggal_beli" value="{{ $saham->tanggal_beli }}" required>
            </div>

            <div class="form-group">
                <label>Foto Bukti / Logo (Opsional)</label>
                @if($saham->foto)
                    <div style="margin-bottom: 8px;">
                        <img src="{{ asset('uploads/' . $saham->foto) }}" alt="Foto" width="50" style="border-radius: 4px;">
                    </div>
                @endif
                <input type="file" name="foto" style="padding: 7px;">
            </div>

            <button type="submit" class="btn-simpan">Perbarui Saham</button>
            <a href="/saham" class="btn-batal">Batal</a>
        </form>
    </div>

</body>
</html>
