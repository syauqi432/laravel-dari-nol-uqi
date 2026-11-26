<?php

namespace App\Http\Controllers;

use App\Models\AutoOrder;
use App\Models\PaperOrder;
use App\Models\Saham;
use App\Models\SahamCorporateAction;
use App\Models\SahamPriceAlert;
use App\Models\SahamTransaction;
use App\Models\User;
use App\Services\AlphaVantageMarketData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InvestmentController extends Controller
{
    private const RISK_PROFILE_OPTIONS = [
        'jangka_waktu' => [
            'kurang_1_tahun' => 1,
            '1_3_tahun' => 2,
            '3_5_tahun' => 3,
            'lebih_5_tahun' => 4,
        ],
        'reaksi_rugi' => [
            'jual_semua' => 1,
            'jual_sebagian' => 2,
            'tahan' => 3,
            'tambah_investasi' => 4,
        ],
        'kenyamanan_fluktuasi' => [
            'tidak_nyaman' => 1,
            'sedikit_turun' => 2,
            'fluktuasi_wajar' => 3,
            'nyaman_turun_besar' => 4,
        ],
        'kebutuhan_dana' => [
            'kurang_1_tahun' => 1,
            '1_3_tahun' => 2,
            '3_5_tahun' => 3,
            'lebih_5_tahun' => 4,
        ],
        'pengalaman' => [
            'pemula' => 1,
            'pernah' => 2,
            'berpengalaman' => 3,
            'alami_koreksi' => 4,
        ],
    ];

    public function overview(): View
    {
        $sahams = Saham::query()->orderBy('kode_emiten')->limit(20)->get();
        $favoriteCount = Saham::where('is_favorite', true)->count();
        $upcomingActionCount = SahamCorporateAction::query()
            ->whereHas('saham')
            ->where('tanggal_penting', '>=', now()->toDateString())
            ->count();

        return view('investment.market', compact('sahams', 'favoriteCount', 'upcomingActionCount'));
    }

    public function watchlist(): View
    {
        $sahams = Saham::query()->where('is_favorite', true)->orderBy('kode_emiten')->get();
        $allSahams = Saham::query()->orderBy('kode_emiten')->get();

        return view('investment.watchlist', compact('sahams', 'allSahams'));
    }

    public function screen(Request $request): View
    {
        $filters = $request->validate([
            'sektor' => ['nullable', 'string', 'max:255'],
            'per_max' => ['nullable', 'numeric', 'min:0'],
            'pbv_max' => ['nullable', 'numeric', 'min:0'],
            'roe_min' => ['nullable', 'numeric'],
            'growth_min' => ['nullable', 'numeric'],
            'sort' => ['nullable', 'in:kode,nama,roe,per,pbv'],
        ]);

        $query = Saham::query()->where(function (Builder $query) {
            $query->where('jumlah_lot', '>', 0)->orWhere('is_favorite', true);
        });

        if (! empty($filters['sektor'])) {
            $query->where('sektor', $filters['sektor']);
        }

        foreach ([
            'per_max' => ['per', '<='],
            'pbv_max' => ['pbv', '<='],
            'roe_min' => ['roe', '>='],
            'growth_min' => ['pertumbuhan_pendapatan', '>='],
        ] as $filter => [$column, $operator]) {
            if (isset($filters[$filter])) {
                $query->whereNotNull($column)->where($column, $operator, $filters[$filter]);
            }
        }

        $sorts = [
            'kode' => 'kode_emiten',
            'nama' => 'nama_perusahaan',
            'roe' => 'roe',
            'per' => 'per',
            'pbv' => 'pbv',
        ];
        $query->orderBy($sorts[$filters['sort'] ?? 'kode']);
        $sahams = $query->get();
        $sektorList = Saham::query()->select('sektor')->distinct()->orderBy('sektor')->pluck('sektor');

        return view('investment.screen', compact('sahams', 'sektorList', 'filters'));
    }

    public function show(int $id): View
    {
        $saham = Saham::with([
            'financials' => fn ($query) => $query->latest(),
            'corporateActions' => fn ($query) => $query->orderByDesc('tanggal_penting'),
            'posts' => fn ($query) => $query->latest()->limit(20),
            'priceAlerts' => fn ($query) => $query->latest(),
        ])->findOrFail($id);
        $activeAutoOrders = $saham->autoOrders()->where('status', 'aktif')->latest()->get();
        $peers = Saham::query()
            ->where('sektor', $saham->sektor)
            ->where('id', '!=', $saham->id)
            ->whereNotNull('per')
            ->whereNotNull('pbv')
            ->orderBy('kode_emiten')
            ->get(['id', 'kode_emiten', 'nama_perusahaan', 'per', 'pbv', 'roe']);
        $peerStats = [
            'count' => $peers->count(),
            'averagePer' => $peers->avg(fn (Saham $peer) => (float) $peer->per),
            'averagePbv' => $peers->avg(fn (Saham $peer) => (float) $peer->pbv),
        ];

        $latestFinancial = $saham->financials->first();
        $healthChecks = [
            ['label' => 'ROE positif', 'value' => $saham->roe, 'passed' => $saham->roe !== null && (float) $saham->roe > 0],
            ['label' => 'Pertumbuhan pendapatan tidak negatif', 'value' => $saham->pertumbuhan_pendapatan, 'passed' => $saham->pertumbuhan_pendapatan !== null && (float) $saham->pertumbuhan_pendapatan >= 0],
            ['label' => 'DER paling tinggi 1x', 'value' => $saham->rasio_utang_ekuitas, 'passed' => $saham->rasio_utang_ekuitas !== null && (float) $saham->rasio_utang_ekuitas <= 1],
            ['label' => 'Arus kas operasi positif', 'value' => $latestFinancial?->arus_kas_operasi, 'passed' => $latestFinancial?->arus_kas_operasi !== null && (float) $latestFinancial->arus_kas_operasi > 0],
        ];
        $scoredChecks = array_filter($healthChecks, fn (array $check) => $check['value'] !== null);
        $healthScore = [
            'passed' => count(array_filter($scoredChecks, fn (array $check) => $check['passed'])),
            'available' => count($scoredChecks),
            'total' => count($healthChecks),
        ];

        return view('investment.detail', compact('saham', 'activeAutoOrders', 'peers', 'peerStats', 'healthChecks', 'healthScore'));
    }

    public function transactions(): View
    {
        $sahams = Saham::query()->orderBy('kode_emiten')->get();
        $orders = PaperOrder::with('saham')->whereHas('saham')->latest()->limit(50)->get();
        $autoOrders = AutoOrder::with('saham')->whereHas('saham')->latest()->get();
        $priceAlerts = SahamPriceAlert::with('saham')->whereHas('saham')->latest()->get();
        $realizedGain = PaperOrder::whereHas('saham')->sum('laba_terealisasi');
        $feesPaid = PaperOrder::whereHas('saham')->sum('biaya_transaksi');
        $buyFeeRate = (float) config('investment.paper_trading.buy_fee_rate') * 100;
        $sellFeeRate = (float) config('investment.paper_trading.sell_fee_rate') * 100;

        return view('investment.transactions', compact(
            'sahams',
            'orders',
            'autoOrders',
            'priceAlerts',
            'realizedGain',
            'feesPaid',
            'buyFeeRate',
            'sellFeeRate',
        ));
    }

    public function profile(): View
    {
        $user = User::query()->findOrFail(auth()->id());
        $riskProfile = $this->riskProfileFor($user->risk_profile_score);

        return view('investment.profile', compact('riskProfile'));
    }

    public function corporateActions(Request $request): View
    {
        $filters = $request->validate([
            'waktu' => ['nullable', Rule::in(['all', 'upcoming', 'past'])],
        ]);
        $filter = $filters['waktu'] ?? 'upcoming';
        $today = now()->toDateString();
        $baseQuery = SahamCorporateAction::query()->whereHas('saham');
        $upcomingCount = (clone $baseQuery)
            ->where('tanggal_penting', '>=', $today)
            ->count();
        $pastCount = (clone $baseQuery)
            ->where('tanggal_penting', '<', $today)
            ->count();
        $totalCount = (clone $baseQuery)->count();
        $actionsQuery = (clone $baseQuery)->with('saham:id,kode_emiten,nama_perusahaan');

        if ($filter === 'upcoming') {
            $actionsQuery->where('tanggal_penting', '>=', $today)
                ->orderBy('tanggal_penting');
        } elseif ($filter === 'past') {
            $actionsQuery->where('tanggal_penting', '<', $today)
                ->orderByDesc('tanggal_penting');
        } else {
            $actionsQuery->orderByRaw('CASE WHEN tanggal_penting IS NULL THEN 1 ELSE 0 END')
                ->orderBy('tanggal_penting');
        }

        $actions = $actionsQuery->orderByDesc('id')->simplePaginate(20)->withQueryString();

        return view('investment.corporate-actions', compact(
            'actions',
            'filter',
            'upcomingCount',
            'pastCount',
            'totalCount',
        ));
    }

    public function storeRiskProfile(Request $request): RedirectResponse
    {
        $rules = [];
        foreach (self::RISK_PROFILE_OPTIONS as $field => $options) {
            $rules[$field] = ['required', Rule::in(array_keys($options))];
        }

        $validated = $request->validate($rules, [], [
            'jangka_waktu' => 'jangka waktu investasi',
            'reaksi_rugi' => 'reaksi terhadap kerugian',
            'kenyamanan_fluktuasi' => 'kenyamanan terhadap fluktuasi',
            'kebutuhan_dana' => 'kebutuhan dana',
            'pengalaman' => 'pengalaman investasi',
        ]);

        $score = 0;
        foreach (self::RISK_PROFILE_OPTIONS as $field => $options) {
            $score += $options[$validated[$field]];
        }

        User::query()->findOrFail(auth()->id())->forceFill([
            'risk_profile_score' => $score,
        ])->save();

        return redirect()->route('profile.index')->with('success', 'Profil risiko edukatif berhasil disimpan.');
    }

    public function guide(): View
    {
        return view('investment.guide');
    }

    public function storeWatchlist(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_emiten' => ['required', 'string', 'max:12', 'regex:/^[A-Za-z0-9.]+$/'],
            'nama_perusahaan' => ['required', 'string', 'max:255'],
            'sektor' => ['required', 'string', 'max:255'],
        ]);
        $symbol = strtoupper($validated['kode_emiten']);
        $saham = Saham::where('kode_emiten', $symbol)->first();

        if ($saham) {
            $saham->update(['is_favorite' => true]);
        } else {
            Saham::create([
                'nama_pemilik' => 'Investor',
                'kode_emiten' => $symbol,
                'nama_perusahaan' => $validated['nama_perusahaan'],
                'sektor' => $validated['sektor'],
                'jumlah_lot' => 0,
                'harga_beli' => 0,
                'tanggal_beli' => now()->toDateString(),
                'is_favorite' => true,
            ]);
        }

        return redirect()->route('watchlist.index')->with('success', "{$symbol} ditambahkan ke watchlist.");
    }

    public function placePaperOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'saham_id' => ['required', Rule::exists('sahams', 'id')->where('user_id', auth()->id())],
            'jenis' => ['required', 'in:beli,jual'],
            'jumlah_lot' => ['required', 'integer', 'min:1'],
            'harga' => ['required', 'numeric', 'gt:0'],
        ]);

        $this->executePaperOrder(
            (int) $validated['saham_id'],
            $validated['jenis'],
            (int) $validated['jumlah_lot'],
            (float) $validated['harga'],
            'manual',
        );

        return redirect()->route('transactions.index')->with('success', 'Order simulasi berhasil dieksekusi. Tidak ada order yang dikirim ke broker.');
    }

    public function storeAutoOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'saham_id' => ['required', Rule::exists('sahams', 'id')->where('user_id', auth()->id())],
            'jenis' => ['required', 'in:beli,jual'],
            'jumlah_lot' => ['required', 'integer', 'min:1'],
            'harga_pemicu' => ['required', 'numeric', 'gt:0'],
        ]);

        AutoOrder::create($validated);

        return back()->with('success', 'Aturan auto-order simulasi disimpan. Aturan dapat diperiksa dari halaman Transaksi.');
    }

    public function cancelAutoOrder(AutoOrder $autoOrder): RedirectResponse
    {
        abort_unless(Saham::query()->whereKey($autoOrder->saham_id)->exists(), 404);

        if ($autoOrder->status === 'aktif') {
            $autoOrder->update(['status' => 'dibatalkan']);
        }

        return back()->with('success', 'Aturan auto-order dibatalkan.');
    }

    public function storePriceAlert(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'saham_id' => ['required', Rule::exists('sahams', 'id')->where('user_id', auth()->id())],
            'kondisi' => ['required', 'in:di_atas,di_bawah'],
            'harga_target' => ['required', 'numeric', 'gt:0'],
        ]);

        SahamPriceAlert::create($validated);

        return back()->with('success', 'Alert harga disimpan.');
    }

    public function checkMarketRules(AlphaVantageMarketData $marketData): RedirectResponse
    {
        $sahamIds = SahamPriceAlert::where('status', 'aktif')->whereHas('saham')->pluck('saham_id')
            ->merge(AutoOrder::where('status', 'aktif')->whereHas('saham')->pluck('saham_id'))
            ->unique();
        $checked = 0;
        $triggered = 0;

        foreach ($sahamIds as $sahamId) {
            $saham = Saham::findOrFail($sahamId);
            $currentPrice = $this->fetchQuotePrice($saham->kode_emiten, $marketData);
            $checked++;

            $alerts = SahamPriceAlert::where('saham_id', $saham->id)->where('status', 'aktif')->whereHas('saham')->get();
            foreach ($alerts as $alert) {
                $isTriggered = $alert->kondisi === 'di_atas'
                    ? $currentPrice >= $alert->harga_target
                    : $currentPrice <= $alert->harga_target;

                if ($isTriggered) {
                    $alert->update(['status' => 'terpicu', 'terpicu_pada' => now()]);
                    $triggered++;
                }
            }

            $autoOrders = AutoOrder::where('saham_id', $saham->id)->where('status', 'aktif')->whereHas('saham')->get();
            foreach ($autoOrders as $autoOrder) {
                $isTriggered = $autoOrder->jenis === 'beli'
                    ? $currentPrice <= $autoOrder->harga_pemicu
                    : $currentPrice >= $autoOrder->harga_pemicu;

                if ($isTriggered) {
                    $paperOrder = $this->executePaperOrder(
                        $saham->id,
                        $autoOrder->jenis,
                        $autoOrder->jumlah_lot,
                        $currentPrice,
                        'auto_order',
                    );
                    $autoOrder->update([
                        'status' => 'tereksekusi',
                        'dijalankan_pada' => now(),
                        'paper_order_id' => $paperOrder->id,
                    ]);
                    $triggered++;
                }
            }
        }

        return back()->with('success', "{$checked} emiten diperiksa; {$triggered} aturan simulasi terpicu.");
    }

    public function storePost(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'nama_penulis' => ['required', 'string', 'max:80'],
            'isi' => ['required', 'string', 'min:3', 'max:2000'],
        ]);

        Saham::findOrFail($id)->posts()->create($validated);

        return back()->with('success', 'Diskusi berhasil dipublikasikan.');
    }

    public function storeFinancial(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'periode' => ['required', 'string', 'max:20'],
            'jenis_periode' => ['required', 'in:kuartal,tahunan'],
            'pendapatan' => ['nullable', 'numeric'],
            'laba_operasi' => ['nullable', 'numeric'],
            'laba_bersih' => ['nullable', 'numeric'],
            'total_aset' => ['nullable', 'numeric'],
            'total_liabilitas' => ['nullable', 'numeric'],
            'arus_kas_operasi' => ['nullable', 'numeric'],
            'sumber' => ['nullable', 'string', 'max:255'],
        ]);

        Saham::findOrFail($id)->financials()->updateOrCreate(
            ['periode' => $validated['periode'], 'jenis_periode' => $validated['jenis_periode']],
            $validated,
        );

        return back()->with('success', 'Data laporan keuangan disimpan. Pastikan sumber angka sudah diverifikasi.');
    }

    public function storeCorporateAction(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'jenis' => ['required', 'string', 'max:100'],
            'tanggal_penting' => ['nullable', 'date'],
            'nilai_per_saham' => ['nullable', 'numeric'],
            'status' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:2000'],
            'sumber' => ['nullable', 'string', 'max:255'],
        ]);

        Saham::findOrFail($id)->corporateActions()->create($validated);

        return back()->with('success', 'Aksi korporasi dicatat. Pastikan sumber dan tanggal telah diverifikasi.');
    }

    private function executePaperOrder(int $sahamId, string $jenis, int $jumlahLot, float $harga, string $source): PaperOrder
    {
        return DB::transaction(function () use ($sahamId, $jenis, $jumlahLot, $harga, $source): PaperOrder {
            $saham = Saham::query()->lockForUpdate()->findOrFail($sahamId);
            $realizedGain = 0;
            $tradeValue = round($harga * $jumlahLot * 100, 2);
            $feeRate = (float) config('investment.paper_trading.'.($jenis === 'beli' ? 'buy' : 'sell').'_fee_rate');

            if (! is_finite($feeRate) || $feeRate < 0 || $feeRate > 1) {
                throw new \LogicException('Tarif biaya paper trading harus berada di antara 0 dan 1.');
            }

            $transactionFee = round($tradeValue * $feeRate, 2);

            if ($jenis === 'jual') {
                if ($jumlahLot > $saham->jumlah_lot) {
                    throw ValidationException::withMessages([
                        'jumlah_lot' => 'Jumlah jual melebihi lot yang tersedia di portofolio simulasi.',
                    ]);
                }

                $realizedGain = $tradeValue - $transactionFee - ((float) $saham->harga_beli * $jumlahLot * 100);
                $saham->jumlah_lot -= $jumlahLot;
            } else {
                $totalLots = $saham->jumlah_lot + $jumlahLot;
                $existingCost = $saham->jumlah_lot * (float) $saham->harga_beli * 100;
                $saham->harga_beli = ($existingCost + $tradeValue + $transactionFee) / ($totalLots * 100);
                $saham->jumlah_lot = $totalLots;
            }

            $saham->save();
            $paperOrder = $saham->paperOrders()->create([
                'jenis' => $jenis,
                'jumlah_lot' => $jumlahLot,
                'harga' => $harga,
                'biaya_transaksi' => $transactionFee,
                'laba_terealisasi' => $realizedGain,
                'sumber_order' => $source,
            ]);

            SahamTransaction::create([
                'saham_id' => $saham->id,
                'jenis' => $jenis,
                'jumlah_lot' => $jumlahLot,
                'harga' => $harga,
                'tanggal' => now()->toDateString(),
                'catatan' => 'Paper trading (simulasi)',
            ]);

            return $paperOrder;
        }, attempts: 3);
    }

    /**
     * @return array{category: string, score: int, description: string}|null
     */
    private function riskProfileFor(?int $score): ?array
    {
        if ($score === null) {
            return null;
        }

        [$category, $description] = match (true) {
            $score <= 9 => [
                'Konservatif',
                'Anda cenderung mengutamakan kestabilan. Pertimbangkan kebutuhan dana dan risiko penurunan sebelum berinvestasi.',
            ],
            $score <= 15 => [
                'Moderat',
                'Anda menunjukkan toleransi risiko yang seimbang. Tetap sesuaikan keputusan dengan tujuan dan jangka waktu investasi.',
            ],
            default => [
                'Agresif',
                'Anda cenderung lebih toleran terhadap fluktuasi. Nilai investasi tetap dapat turun besar dan tidak cocok untuk dana kebutuhan dekat.',
            ],
        };

        return [
            'category' => $category,
            'score' => $score,
            'description' => $description,
        ];
    }

    private function fetchQuotePrice(string $symbol, AlphaVantageMarketData $marketData): float
    {
        $quote = $marketData->quote($symbol, true);
        $price = filter_var($quote['Global Quote']['05. price'] ?? null, FILTER_VALIDATE_FLOAT);

        if (
            $quote['status'] !== 'available'
            || $quote['freshness'] !== 'recent'
            || $price === false
            || $price <= 0
        ) {
            throw ValidationException::withMessages([
                'market' => 'Quote terbaru yang valid tidak tersedia; aturan belum diperiksa dan tidak ada order yang dieksekusi.',
            ]);
        }

        return (float) $price;
    }
}
