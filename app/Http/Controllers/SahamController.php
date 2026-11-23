<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Saham;

class SahamController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sektor = $request->input('sektor');

        $saham = Saham::when($search, function ($query, $search) {
            return $query->where('kode_emiten', 'like', "%{$search}%")
                         ->orWhere('nama_perusahaan', 'like', "%{$search}%");
        })->when($sektor, function ($query, $sektor) {
            return $query->where('sektor', $sektor);
        })->get();

        $sektorList = Saham::select('sektor')->distinct()->pluck('sektor');

        return view('saham', compact('saham', 'sektorList'));
    }

    public function create()
    {
        return view('tambah-saham');
    }

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

        $input = $request->all();

        if ($image = $request->file('foto')) {
            $destinationPath = 'uploads/';
            $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
            $image->move($destinationPath, $profileImage);
            $input['foto'] = "$profileImage";
        }

        Saham::create($input);

        return redirect('/saham')->with('success', 'Data saham berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $saham = Saham::findOrFail($id);
        return view('edit-saham', compact('saham'));
    }

    public function update(Request $request, $id)
    {
        $saham = Saham::findOrFail($id);

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

        $input = $request->all();

        if ($image = $request->file('foto')) {
            $destinationPath = 'uploads/';
            $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
            $image->move($destinationPath, $profileImage);
            $input['foto'] = "$profileImage";
        } else {
            unset($input['foto']);
        }

        $saham->update($input);

        return redirect('/saham')->with('success', 'Data saham berhasil diubah!');
    }

    public function destroy($id)
    {
        $saham = Saham::findOrFail($id);
        $saham->delete();

        return redirect('/saham')->with('success', 'Data saham berhasil dihapus!');
    }
}
