<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Saham;

class SahamController extends Controller
{
    // Menampilkan data (Sudah ada)
   public function index()
{
    $sahams = Saham::all();

    // Hitung total nilai investasi (Jumlah Lot * 100 * Harga Beli)
    $totalInvestasi = 0;
    foreach($sahams as $saham) {
        $totalInvestasi += $saham->jumlah_lot * 100 * $saham->harga_beli;
    }

    return view('saham', compact('sahams', 'totalInvestasi'));
}

    // [BARU] Menyimpan data inputan ke database
    public function store(Request $request)
    {
        // Validasi data yang diinput
        $request->validate([
            'kode_emiten' => 'required',
            'nama_perusahaan' => 'required',
            'sektor' => 'required',
            'jumlah_lot' => 'required|numeric',
            'harga_beli' => 'required|numeric',
            'tanggal_beli' => 'required|date',
        ]);

        // Masukkan data ke database
        Saham::create($request->all());

        // Arahkan kembali ke halaman tabel saham
        return redirect('/saham');
    }
}
