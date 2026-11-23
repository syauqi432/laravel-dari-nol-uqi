<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Saham - Portofolio Uqi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: #0f172a; color: #e2e8f0; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        .form-container { width: 100%; max-width: 480px; background: #1e293b; padding: 35px; border-radius: 12px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3); border: 1px solid #334155; }
        h2 { color: #f8fafc; font-size: 22px; font-weight: 700; margin-bottom: 20px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; font-weight: 500; font-size: 13px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; }
        input { width: 100%; padding: 10px 14px; background-color: #0f172a; border: 1px solid #334155; border-radius: 6px; color: #fff; font-size: 14px; outline: none; transition: border-color 0.2s; }
        input:focus { border-color: #10b981; }
        .btn-group { display: flex; align-items: center; gap: 12px; margin-top: 25px; }
        button { background-color: #10b981; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; transition: background 0.2s; }
        button:hover { background-color: #059669; }
        a { text-decoration: none; color: #94a3b8; font-size: 14px; font-weight: 500; transition: color 0.2s; }
        a:hover { color: #fff; }
    </style>
</head>
<body>

    <div class="form-container">
        <h2>➕ Tambah Saham Baru</h2>

        <form action="/saham" method="POST">
            @csrf
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
                <label>Jumlah Lot</label>
                <input type="number" name="jumlah_lot" placeholder="Contoh: 10" required>
            </div>

            <div class="form-group">
                <label>Harga Beli (per lembar)</label>
                <input type="number" step="any" name="harga_beli" placeholder="Contoh: 10000" required>
            </div>

            <div class="form-group">
                <label>Tanggal Beli</label>
                <input type="date" name="tanggal_beli" required>
            </div>

            <div class="btn-group">
                <button type="submit">Simpan Saham</button>
                <a href="/saham">Batal</a>
            </div>
        </form>
    </div>

</body>
</html>
