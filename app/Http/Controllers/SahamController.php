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
        return view('saham', compact('sahams'));
    }

    // [BARU] Menampilkan form tambah saham
    public function create()
    {
        return view('tambah-saham');
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
