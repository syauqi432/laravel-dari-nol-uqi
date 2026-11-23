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

        $totalInvestasi = 0;
        foreach($sahams as $saham) {
            $totalInvestasi += $saham->jumlah_lot * 100 * $saham->harga_beli;
        }

        return view('saham', compact('sahams', 'totalInvestasi'));
    }

    // Menampilkan form tambah saham
    public function create()
    {
        return view('tambah-saham');
    }

    // Menyimpan data inputan baru ke database
    public function store(Request $request)
    {
        $request->validate([
            'nama_pemilik' => 'required',
            'kode_emiten' => 'required',
            'nama_perusahaan' => 'required',
            'sektor' => 'required',
            'jumlah_lot' => 'required|numeric',
            'harga_beli' => 'required|numeric',
            'tanggal_beli' => 'required|date',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $filename);
            $data['foto'] = $filename;
        }

        Saham::create($data);

        return redirect('/saham');
    }

    // Menampilkan form edit saham berdasarkan ID
    public function edit($id)
    {
        $saham = Saham::findOrFail($id);
        return view('edit-saham', compact('saham'));
    }

    // Menyimpan perubahan data edit ke database
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_pemilik' => 'required',
            'kode_emiten' => 'required',
            'nama_perusahaan' => 'required',
            'sektor' => 'required',
            'jumlah_lot' => 'required|numeric',
            'harga_beli' => 'required|numeric',
            'tanggal_beli' => 'required|date',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $saham = Saham::findOrFail($id);
        $data = $request->all();

        if ($request->hasFile('foto')) {
            if ($saham->foto && file_exists(public_path('uploads/' . $saham->foto))) {
                unlink(public_path('uploads/' . $saham->foto));
            }

            $file = $request->file('foto');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $filename);
            $data['foto'] = $filename;
        }

        $saham->update($data);

        return redirect('/saham');
    }

    // Menghapus data saham dari database
    public function destroy($id)
    {
        $saham = Saham::findOrFail($id);

        if ($saham->foto && file_exists(public_path('uploads/' . $saham->foto))) {
            unlink(public_path('uploads/' . $saham->foto));
        }

        $saham->delete();

        return redirect('/saham');
    }
}
