<?php

use App\Http\Controllers\SahamController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/harga-saham/{emiten}', function (string $emiten) {
    $symbol = str_contains($emiten, '.') ? $emiten : $emiten.'.JK';

    $response = Http::timeout(10)->get('https://www.alphavantage.co/query', [
        'function' => 'GLOBAL_QUOTE',
        'symbol' => $symbol,
        'apikey' => config('services.alpha_vantage.api_key'),
    ]);

    return response()->json($response->json(), $response->status());
})->where('emiten', '[A-Za-z0-9.]+')->middleware('throttle:30,1');

// Route untuk menampilkan tabel saham (yang sudah ada)
Route::get('/saham', [SahamController::class, 'index']);

// [BARU] Route untuk menampilkan form tambah saham
Route::get('/saham/tambah', [SahamController::class, 'create']);

// [BARU] Route untuk menyimpan data dari form ke database
Route::post('/saham', [SahamController::class, 'store']);

// Route untuk menghapus data saham berdasarkan ID
Route::delete('/saham/{id}', [SahamController::class, 'destroy']);
// Route untuk menampilkan form edit saham berdasarkan ID
Route::get('/saham/{id}/edit', [SahamController::class, 'edit']);

// Route untuk menyimpan perubahan data edit ke database
Route::put('/saham/{id}', [SahamController::class, 'update']);
