<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portofolio Saham Saya</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f4f9; }
        h2 { color: #333; }
        table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        th, td { padding: 12px 15px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #007bff; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>

    <h2>📊 Portofolio Saham Uqi</h2>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Emiten</th>
                <th>Nama Perusahaan</th>
                <th>Sektor</th>
                <th>Jumlah Lot</th>
                <th>Harga Beli</th>
                <th>Tanggal Beli</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sahams as $index => $saham)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $saham->kode_emiten }}</strong></td>
                <td>{{ $saham->nama_perusahaan }}</td>
                <td>{{ $saham->sektor }}</td>
                <td>{{ $saham->jumlah_lot }}</td>
                <td>Rp {{ number_format($saham->harga_beli, 0, ',', '.') }}</td>
                <td>{{ $saham->tanggal_beli }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
