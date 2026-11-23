<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portofolio Saham - Uqi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: #0f172a; color: #e2e8f0; padding: 40px; }
        .container { max-width: 1200px; margin: 0 auto; background: #1e293b; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3); border: 1px solid #334155; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        h2 { color: #f8fafc; font-size: 24px; font-weight: 700; }
        .btn-tambah { background-color: #10b981; color: white; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px; transition: background 0.2s; }
        .btn-tambah:hover { background-color: #059669; }

        /* Kartu Ringkasan */
        .summary-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .card { background: #0f172a; padding: 20px; border-radius: 8px; border: 1px solid #334155; }
        .card-title { font-size: 12px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; }
        .card-value { font-size: 22px; font-weight: 700; color: #34d399; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; background: #0f172a; border-radius: 8px; overflow: hidden; }
        th, td { padding: 14px 16px; text-align: left; border-bottom: 1px solid #334155; font-size: 14px; }
        th { background-color: #1e293b; color: #94a3b8; font-weight: 600; text-transform: uppercase; font-size: 12px; letter-spacing: 0.05em; }
        tr:last-child td { border-bottom: none; }
        tr:hover { background-color: rgba(51, 65, 85, 0.4); }
        .badge { background-color: #334155; color: #38bdf8; padding: 4px 8px; border-radius: 4px; font-weight: 600; }
        .profit { color: #34d399; font-weight: 600; }

        /* Tombol Aksi */
        .btn-edit { background-color: #0284c7; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: 600; display: inline-block; transition: background 0.2s; }
        .btn-edit:hover { background-color: #0369a1; }
        .btn-hapus { background-color: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600; transition: background 0.2s; }
        .btn-hapus:hover { background-color: #dc2626; }
    </style>
</head>
<body>

    <div class="container">
       <div class="header">
            <div>
                <h2>📈 Portofolio Saham Uqi</h2>
                <a href="https://github.com/syauqi432/laravel-dari-nol-uqi" target="_blank" style="color: #38bdf8; font-size: 13px; text-decoration: none; display: inline-block; margin-top: 4px;">
                    🔗 Lihat Repository GitHub
                </a>
            </div>
            <a href="/saham/tambah" class="btn-tambah">+ Tambah Saham</a>
        </div>

        <!-- Bagian Kotak Ringkasan -->
        <div class="summary-cards">
            <div class="card">
                <div class="card-title">Total Nilai Portofolio</div>
                <div class="card-value">Rp {{ number_format($totalInvestasi, 0, ',', '.') }}</div>
            </div>
            <div class="card">
                <div class="card-title">Total Jenis Emiten</div>
                <div class="card-value" style="color: #38bdf8;">{{ count($sahams) }} Saham</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Foto</th>
                    <th>Nama Pemilik</th>
                    <th>Kode Emiten</th>
                    <th>Nama Perusahaan</th>
                    <th>Sektor</th>
                    <th>Lot</th>
                    <th>Harga Beli</th>
                    <th>Tanggal Beli</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sahams as $index => $saham)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        @if($saham->foto)
                            <img src="{{ asset('uploads/' . $saham->foto) }}" alt="Foto" width="40" height="40" style="object-fit: cover; border-radius: 4px;">
                        @else
                            <span style="color: #64748b; font-size: 12px;">Tidak ada</span>
                        @endif
                    </td>
                    <td style="font-weight: 600; color: #f1f5f9;">{{ $saham->nama_pemilik }}</td>
                    <td><span class="badge">{{ $saham->kode_emiten }}</span></td>
                    <td style="font-weight: 500; color: #f1f5f9;">{{ $saham->nama_perusahaan }}</td>
                    <td style="color: #94a3b8;">{{ $saham->sektor }}</td>
                    <td>{{ $saham->jumlah_lot }}</td>
                    <td class="profit">Rp {{ number_format($saham->harga_beli, 0, ',', '.') }}</td>
                    <td style="color: #94a3b8;">{{ $saham->tanggal_beli }}</td>
                    <td style="text-align: center; white-space: nowrap;">
                        <a href="/saham/{{ $saham->id }}/edit" class="btn-edit" style="margin-right: 4px;">Edit</a>

                        <form action="/saham/{{ $saham->id }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus saham ini?');" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-hapus">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</body>
</html>
