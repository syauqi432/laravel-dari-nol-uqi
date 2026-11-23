<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Saham;

class SahamController extends Controller
{
    // Menampilkan data dan total nilai investasi
    public function index()
    {
        $sahams = Saham::all();

        // Hitung total nilai investasi (Jumlah Lot * 100 * Harga Beli)
        $totalInvestasi = 0;
        foreach($sahams as $saham) {$totalInvestasi += $saham->jumlah_lot * 100 * $saham->harga_beli;
        }

        return view('saham', compact('sahams', 'totalInvestasi'));
    }

    // Menampilkan form tambah saham
    public function create()
    {
        return view('tambah-saham');
    }

    // Menyimpan data inputan ke database
    public function store(Request $request)
    {
        $request->validate([
            'kode_emiten' => 'required',
            'nama_perusahaan' => 'required',
            'sektor' => 'required',
            'jumlah_lot' => 'required|numeric',
            'harga_beli' => 'required|numeric',
            'tanggal_beli' => 'required|date',
        ]);

        Saham::create($request->all());

        return redirect('/saham');
    }

    // Menghapus data saham dari database
    public function destroy($id)
    {
        $saham = Saham::findOrFail($id);$saham->delete();

        return redirect('/saham');
    }
}
