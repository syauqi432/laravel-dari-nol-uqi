<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
use App\Http\Controllers\SahamController;

// Route untuk menampilkan tabel saham (yang sudah ada)
Route::get('/saham', [SahamController::class, 'index']);

// [BARU] Route untuk menampilkan form tambah saham
Route::get('/saham/tambah', [SahamController::class, 'create']);

// [BARU] Route untuk menyimpan data dari form ke database
Route::post('/saham', [SahamController::class, 'store']);

// Route untuk menghapus data saham berdasarkan ID
Route::delete('/saham/{id}', [SahamController::class, 'destroy']);
