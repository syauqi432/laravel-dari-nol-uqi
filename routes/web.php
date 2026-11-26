<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\MarketDataController;
use App\Http\Controllers\SahamController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('market.overview');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegistration'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:5,1')
        ->name('register.store');
    Route::get('/owner/setup', [AuthController::class, 'showOwnerSetup'])->name('owner.setup');
    Route::post('/owner/setup', [AuthController::class, 'createOwner'])
        ->middleware('throttle:3,1')
        ->name('owner.setup.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/market', [InvestmentController::class, 'overview'])->name('market.overview');
    Route::get('/agenda-aksi-korporasi', [InvestmentController::class, 'corporateActions'])->name('corporate-actions.index');
    Route::get('/watchlist', [InvestmentController::class, 'watchlist'])->name('watchlist.index');
    Route::post('/watchlist', [InvestmentController::class, 'storeWatchlist'])->name('watchlist.store');
    Route::get('/screening', [InvestmentController::class, 'screen'])->name('screening.index');
    Route::get('/transaksi', [InvestmentController::class, 'transactions'])->name('transactions.index');
    Route::get('/profil', [InvestmentController::class, 'profile'])->name('profile.index');
    Route::post('/profil/profil-risiko', [InvestmentController::class, 'storeRiskProfile'])->name('risk-profile.store');
    Route::get('/panduan', [InvestmentController::class, 'guide'])->name('guide.index');
    Route::get('/emiten/{id}', [InvestmentController::class, 'show'])->name('issuers.show');
    Route::post('/paper-order', [InvestmentController::class, 'placePaperOrder'])->name('paper-orders.store');
    Route::post('/auto-order', [InvestmentController::class, 'storeAutoOrder'])->name('auto-orders.store');
    Route::post('/auto-order/{autoOrder}/cancel', [InvestmentController::class, 'cancelAutoOrder'])->name('auto-orders.cancel');
    Route::post('/auto-order/check', [InvestmentController::class, 'checkMarketRules'])->name('market-rules.check');
    Route::post('/price-alert', [InvestmentController::class, 'storePriceAlert'])->name('price-alerts.store');
    Route::post('/emiten/{id}/diskusi', [InvestmentController::class, 'storePost'])->name('issuer-posts.store');
    Route::post('/emiten/{id}/laporan-keuangan', [InvestmentController::class, 'storeFinancial'])->name('issuer-financials.store');
    Route::post('/emiten/{id}/aksi-korporasi', [InvestmentController::class, 'storeCorporateAction'])->name('issuer-corporate-actions.store');

    Route::get('/api/harga-saham/{emiten}', [MarketDataController::class, 'quote'])
        ->where('emiten', '[A-Za-z0-9.]+')
        ->middleware('throttle:30,1');
    Route::get('/api/harga-saham/{emiten}/historical', [MarketDataController::class, 'history'])
        ->where('emiten', '[A-Za-z0-9.]+')
        ->middleware('throttle:30,1');
    Route::get('/api/market/index', [MarketDataController::class, 'index'])
        ->middleware('throttle:30,1');
    Route::get('/api/market/news', [MarketDataController::class, 'news'])
        ->middleware('throttle:30,1');

    Route::get('/saham', [SahamController::class, 'index'])->name('saham.index');
    Route::get('/saham/export/csv', [SahamController::class, 'exportCsv']);
    Route::get('/saham/tambah', [SahamController::class, 'create']);
    Route::post('/saham', [SahamController::class, 'store']);
    Route::delete('/saham/{id}', [SahamController::class, 'destroy']);
    Route::get('/saham/{id}/edit', [SahamController::class, 'edit']);
    Route::put('/saham/{id}', [SahamController::class, 'update']);
    Route::post('/saham/{id}/favorite', [SahamController::class, 'toggleFavorite']);
    Route::post('/saham/{id}/transaksi', [SahamController::class, 'storeTransaction']);
});
