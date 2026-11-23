<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Portofolio Saham</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f4f9; }
        .form-container { max-width: 500px; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h2 { color: #333; margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #218838; }
        a { text-decoration: none; color: #666; margin-left: 10px; }
    </style>
</head>
<body>

    <div class="form-container">
        <h2>➕ Tambah Saham Baru</h2>

        <form action="/saham" method="POST">
            @csrf
            <div class="form-group">
                <label>Kode Emiten:</label>
                <input type="text" name="kode_emiten" placeholder="Contoh: TLKM, ASII" required>
            </div>

            <div class="form-group">
                <label>Nama Perusahaan:</label>
                <input type="text" name="nama_perusahaan" placeholder="Contoh: Telkom Indonesia Tbk" required>
            </div>

            <div class="form-group">
                <label>Sektor:</label>
                <input type="text" name="sektor" placeholder="Contoh: Infrastructure" required>
            </div>

            <div class="form-group">
                <label>Jumlah Lot:</label>
                <input type="number" name="jumlah_lot" placeholder="Contoh: 10" required>
            </div>

            <div class="form-group">
                <label>Harga Beli (per lembar):</label>
                <input type="number" step="any" name="harga_beli" placeholder="Contoh: 4000" required>
            </div>

            <div class="form-group">
                <label>Tanggal Beli:</label>
                <input type="date" name="tanggal_beli" required>
            </div>

            <button type="submit">Simpan Saham</button>
            <a href="/saham">Batal</a>
        </form>
    </div>

</body>
</html>
