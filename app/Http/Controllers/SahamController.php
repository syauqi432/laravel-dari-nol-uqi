<?php

namespace App\Http\Controllers;

use App\Models\Saham;
use App\Models\SahamTransaction;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SahamController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sektor = $request->input('sektor');
        $sort = $request->input('sort', 'terbaru');

        $saham = Saham::when($search, function ($query, $search) {
            return $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('kode_emiten', 'like', "%{$search}%")
                    ->orWhere('nama_perusahaan', 'like', "%{$search}%");
            });
        })->when($sektor, function ($query, $sektor) {
            return $query->where('sektor', $sektor);
        })->when($sort === 'nama', fn ($query) => $query->orderBy('nama_perusahaan'))
            ->when($sort === 'kode', fn ($query) => $query->orderBy('kode_emiten'))
            ->when($sort === 'lot-terbanyak', fn ($query) => $query->orderByDesc('jumlah_lot'))
            ->when($sort === 'modal-terbesar', fn ($query) => $query->orderByRaw('(jumlah_lot * harga_beli) DESC'))
            ->when($sort === 'terbaru', fn ($query) => $query->latest())
            ->get();

        $sektorList = Saham::select('sektor')->distinct()->pluck('sektor');
        $transactions = SahamTransaction::with('saham')
            ->whereHas('saham')
            ->latest('tanggal')
            ->latest('id')
            ->limit(20)
            ->get();

        return view('saham', compact('saham', 'sektorList', 'transactions'));
    }

    public function create()
    {
        return view('tambah-saham');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pemilik' => 'required',
            'kode_emiten' => 'required',
            'nama_perusahaan' => 'required',
            'sektor' => 'required',
            'jumlah_lot' => 'required|numeric|min:0',
            'harga_beli' => 'required|numeric|min:0',
            'dividen_per_saham' => 'nullable|numeric|min:0',
            'kapitalisasi_pasar' => 'nullable|numeric|min:0',
            'per' => 'nullable|numeric|min:0',
            'pbv' => 'nullable|numeric|min:0',
            'roe' => 'nullable|numeric',
            'pertumbuhan_pendapatan' => 'nullable|numeric',
            'rasio_utang_ekuitas' => 'nullable|numeric|min:0',
            'tanggal_beli' => 'required|date',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $input = $validated;

        if ($image = $request->file('foto')) {
            $destinationPath = 'uploads/';
            $profileImage = date('YmdHis').'.'.$image->getClientOriginalExtension();
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

        $validated = $request->validate([
            'nama_pemilik' => 'required',
            'kode_emiten' => 'required',
            'nama_perusahaan' => 'required',
            'sektor' => 'required',
            'jumlah_lot' => 'required|numeric|min:0',
            'harga_beli' => 'required|numeric|min:0',
            'kapitalisasi_pasar' => 'nullable|numeric|min:0',
            'per' => 'nullable|numeric|min:0',
            'pbv' => 'nullable|numeric|min:0',
            'roe' => 'nullable|numeric',
            'pertumbuhan_pendapatan' => 'nullable|numeric',
            'rasio_utang_ekuitas' => 'nullable|numeric|min:0',
            'dividen_per_saham' => 'nullable|numeric', // <-- Tambahan aman
            'tanggal_beli' => 'required|date',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $input = $validated;

        if ($image = $request->file('foto')) {
            $destinationPath = 'uploads/';
            $profileImage = date('YmdHis').'.'.$image->getClientOriginalExtension();
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

    public function toggleFavorite($id)
    {
        $saham = Saham::findOrFail($id);
        $saham->update(['is_favorite' => ! $saham->is_favorite]);

        return back()->with('success', $saham->is_favorite
            ? 'Saham ditambahkan ke favorit.'
            : 'Saham dihapus dari favorit.');
    }

    public function storeTransaction(Request $request, $id)
    {
        $saham = Saham::findOrFail($id);

        $validated = $request->validate([
            'jenis' => ['required', 'in:beli,jual'],
            'jumlah_lot' => ['required', 'integer', 'min:1'],
            'harga' => ['required', 'numeric', 'min:0'],
            'tanggal' => ['required', 'date'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        $saham->transactions()->create($validated);

        return back()->with('success', 'Riwayat transaksi berhasil dicatat.');
    }

    public function exportCsv(): StreamedResponse
    {
        $fileName = 'portofolio-saham-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Kode Emiten', 'Nama Perusahaan', 'Sektor', 'Jumlah Lot', 'Harga Beli', 'Modal', 'Dividen per Saham', 'Tanggal Beli', 'Favorit']);

            Saham::query()->orderBy('kode_emiten')->each(function (Saham $saham) use ($handle) {
                fputcsv($handle, [
                    $saham->kode_emiten,
                    $saham->nama_perusahaan,
                    $saham->sektor,
                    $saham->jumlah_lot,
                    $saham->harga_beli,
                    $saham->jumlah_lot * $saham->harga_beli * 100,
                    $saham->dividen_per_saham,
                    $saham->tanggal_beli,
                    $saham->is_favorite ? 'Ya' : 'Tidak',
                ]);
            });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }
}
