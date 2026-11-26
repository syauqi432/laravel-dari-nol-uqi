<?php

namespace Tests\Feature;

use App\Models\AutoOrder;
use App\Models\PaperOrder;
use App\Models\Saham;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PortfolioFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_favorite_toggle_updates_the_stock(): void
    {
        $this->signInAsOwner();
        $saham = Saham::create([
            'nama_pemilik' => 'Uqi',
            'kode_emiten' => 'BBCA',
            'nama_perusahaan' => 'Bank Central Asia',
            'sektor' => 'Keuangan',
            'jumlah_lot' => 10,
            'harga_beli' => 10000,
            'tanggal_beli' => '2026-01-01',
        ]);

        $response = $this->post('/saham/'.$saham->id.'/favorite');

        $response->assertRedirect();
        $this->assertTrue($saham->refresh()->is_favorite);
    }

    public function test_valid_transaction_is_saved_for_the_stock(): void
    {
        $this->signInAsOwner();
        $saham = Saham::create([
            'nama_pemilik' => 'Uqi',
            'kode_emiten' => 'TLKM',
            'nama_perusahaan' => 'Telkom Indonesia',
            'sektor' => 'Infrastruktur',
            'jumlah_lot' => 5,
            'harga_beli' => 3000,
            'tanggal_beli' => '2026-01-01',
        ]);

        $response = $this->post('/saham/'.$saham->id.'/transaksi', [
            'jenis' => 'beli',
            'jumlah_lot' => 2,
            'harga' => 3100,
            'tanggal' => '2026-11-26',
            'catatan' => 'Pembelian rutin',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('saham_transactions', [
            'saham_id' => $saham->id,
            'jenis' => 'beli',
            'jumlah_lot' => 2,
            'harga' => 3100,
        ]);
    }

    public function test_portfolio_page_accepts_sort_and_sector_filters(): void
    {
        $this->signInAsOwner();
        Saham::create([
            'nama_pemilik' => 'Uqi',
            'kode_emiten' => 'BBCA',
            'nama_perusahaan' => 'Bank Central Asia',
            'sektor' => 'Keuangan',
            'jumlah_lot' => 10,
            'harga_beli' => 10000,
            'tanggal_beli' => '2026-01-01',
        ]);

        $response = $this->get('/saham?sektor=Keuangan&sort=lot-terbanyak');

        $response->assertOk()->assertSee('Bank Central Asia');
    }

    public function test_export_returns_csv_download(): void
    {
        $this->signInAsOwner();
        Saham::create([
            'nama_pemilik' => 'Uqi',
            'kode_emiten' => 'BBRI',
            'nama_perusahaan' => 'Bank Rakyat Indonesia',
            'sektor' => 'Keuangan',
            'jumlah_lot' => 8,
            'harga_beli' => 5000,
            'tanggal_beli' => '2026-01-01',
        ]);

        $response = $this->get('/saham/export/csv');

        $response->assertOk();
        $response->assertDownload();
        $this->assertStringContainsString('Kode Emiten', $response->streamedContent());
    }

    public function test_paper_buy_updates_position_and_weighted_average_cost(): void
    {
        $saham = $this->createSaham(['jumlah_lot' => 10, 'harga_beli' => 100]);

        $response = $this->post(route('paper-orders.store'), [
            'saham_id' => $saham->id,
            'jenis' => 'beli',
            'jumlah_lot' => 10,
            'harga' => 200,
        ]);

        $response->assertRedirect(route('transactions.index'));
        $this->assertSame(20, $saham->refresh()->jumlah_lot);
        $this->assertSame('150.15', $saham->harga_beli);
        $this->assertDatabaseHas('paper_orders', [
            'saham_id' => $saham->id,
            'jenis' => 'beli',
            'jumlah_lot' => 10,
            'harga' => 200,
            'biaya_transaksi' => 300,
        ]);
    }

    public function test_paper_sell_rejects_quantity_above_current_holding(): void
    {
        $saham = $this->createSaham(['jumlah_lot' => 3]);

        $response = $this->from(route('transactions.index'))->post(route('paper-orders.store'), [
            'saham_id' => $saham->id,
            'jenis' => 'jual',
            'jumlah_lot' => 4,
            'harga' => 110,
        ]);

        $response->assertRedirect(route('transactions.index'))->assertSessionHasErrors('jumlah_lot');
        $this->assertSame(3, $saham->refresh()->jumlah_lot);
        $this->assertDatabaseCount('paper_orders', 0);
    }

    public function test_paper_sell_calculates_realized_gain_and_reduces_position(): void
    {
        $saham = $this->createSaham(['jumlah_lot' => 10, 'harga_beli' => 100]);

        $response = $this->post(route('paper-orders.store'), [
            'saham_id' => $saham->id,
            'jenis' => 'jual',
            'jumlah_lot' => 4,
            'harga' => 130,
        ]);

        $response->assertRedirect(route('transactions.index'));
        $this->assertSame(6, $saham->refresh()->jumlah_lot);
        $this->assertSame('11870.00', PaperOrder::firstOrFail()->laba_terealisasi);
    }

    public function test_auto_order_executes_as_paper_trade_when_quote_reaches_trigger(): void
    {
        config(['services.alpha_vantage.api_key' => 'test-key']);
        Cache::flush();
        Http::preventStrayRequests();
        Http::fake([
            'https://www.alphavantage.co/query*' => Http::response([
                'Global Quote' => [
                    '05. price' => '90.0000',
                    '07. latest trading day' => now(config('app.timezone'))->toDateString(),
                ],
            ]),
        ]);
        $saham = $this->createSaham(['jumlah_lot' => 2, 'harga_beli' => 100]);
        $rule = AutoOrder::create([
            'saham_id' => $saham->id,
            'jenis' => 'beli',
            'jumlah_lot' => 3,
            'harga_pemicu' => 95,
        ]);

        $response = $this->post(route('market-rules.check'));

        $response->assertRedirect();
        $this->assertSame('tereksekusi', $rule->refresh()->status);
        $this->assertSame(5, $saham->refresh()->jumlah_lot);
        $this->assertSame('auto_order', PaperOrder::firstOrFail()->sumber_order);
    }

    public function test_auto_order_fetches_a_fresh_quote_instead_of_using_the_cached_display_quote(): void
    {
        config(['services.alpha_vantage.api_key' => 'test-key']);
        Cache::flush();
        Http::preventStrayRequests();
        Http::fake([
            'https://www.alphavantage.co/query*' => Http::sequence()
                ->push([
                    'Global Quote' => [
                        '05. price' => '100.0000',
                        '07. latest trading day' => now(config('app.timezone'))->toDateString(),
                    ],
                ])
                ->push([
                    'Global Quote' => [
                        '05. price' => '90.0000',
                        '07. latest trading day' => now(config('app.timezone'))->toDateString(),
                    ],
                ]),
        ]);
        $saham = $this->createSaham(['jumlah_lot' => 2]);
        $this->getJson('/api/harga-saham/BBCA')->assertOk();
        $rule = AutoOrder::create([
            'saham_id' => $saham->id,
            'jenis' => 'beli',
            'jumlah_lot' => 3,
            'harga_pemicu' => 95,
        ]);

        $this->post(route('market-rules.check'))->assertRedirect();

        $this->assertSame('tereksekusi', $rule->refresh()->status);
        $this->assertSame('90.00', PaperOrder::firstOrFail()->harga);
        Http::assertSentCount(2);
    }

    public function test_screener_applies_fundamental_filters_to_available_stocks(): void
    {
        $this->createSaham([
            'kode_emiten' => 'GOOD',
            'nama_perusahaan' => 'Good Company',
            'per' => 12,
            'pbv' => 2,
            'roe' => 18,
            'pertumbuhan_pendapatan' => 10,
        ]);
        $this->createSaham([
            'kode_emiten' => 'HIGH',
            'nama_perusahaan' => 'High Multiple Company',
            'per' => 30,
            'pbv' => 5,
            'roe' => 5,
            'pertumbuhan_pendapatan' => 1,
        ]);

        $response = $this->get(route('screening.index', ['per_max' => 20, 'roe_min' => 10]));

        $response->assertOk()->assertSee('GOOD')->assertDontSee('HIGH');
    }

    public function test_corporate_action_agenda_shows_upcoming_actions_by_default(): void
    {
        $saham = $this->createSaham();
        $saham->corporateActions()->create([
            'jenis' => 'Dividen interim',
            'tanggal_penting' => now()->addDays(3)->toDateString(),
            'status' => 'Diumumkan',
        ]);
        $saham->corporateActions()->create([
            'jenis' => 'RUPS tahun lalu',
            'tanggal_penting' => now()->subDays(3)->toDateString(),
        ]);

        $response = $this->get(route('corporate-actions.index'));

        $response->assertOk()
            ->assertSee('Dividen interim')
            ->assertDontSee('RUPS tahun lalu')
            ->assertSee('Catatan akan datang');
    }

    public function test_corporate_action_agenda_requires_authentication(): void
    {
        $this->get(route('corporate-actions.index'))
            ->assertRedirect(route('login'));
    }

    public function test_corporate_action_agenda_filters_past_and_undated_records(): void
    {
        $saham = $this->createSaham();
        $saham->corporateActions()->create([
            'jenis' => 'Stock split selesai',
            'tanggal_penting' => now()->subDays(2)->toDateString(),
        ]);
        $saham->corporateActions()->create([
            'jenis' => 'RUPS tanpa tanggal',
        ]);

        $pastResponse = $this->get(route('corporate-actions.index', ['waktu' => 'past']));
        $pastResponse->assertOk()
            ->assertSee('Stock split selesai')
            ->assertDontSee('RUPS tanpa tanggal');

        $allResponse = $this->get(route('corporate-actions.index', ['waktu' => 'all']));
        $allResponse->assertOk()
            ->assertSee('Stock split selesai')
            ->assertSee('RUPS tanpa tanggal')
            ->assertSee('Tanggal belum ditentukan');
    }

    public function test_corporate_action_agenda_excludes_actions_owned_by_another_user(): void
    {
        $owner = $this->signInAsOwner();
        $ownSaham = $this->createSaham(['kode_emiten' => 'OWN']);
        $ownSaham->corporateActions()->create([
            'jenis' => 'Agenda milik saya',
            'tanggal_penting' => now()->addDays(2)->toDateString(),
        ]);

        $this->actingAs(User::factory()->create());
        $otherSaham = Saham::create([
            'nama_pemilik' => 'Investor lain',
            'kode_emiten' => 'OTHER',
            'nama_perusahaan' => 'Perusahaan lain',
            'sektor' => 'Teknologi',
            'jumlah_lot' => 0,
            'harga_beli' => 0,
            'tanggal_beli' => now()->toDateString(),
        ]);
        $otherSaham->corporateActions()->create([
            'jenis' => 'Agenda milik orang lain',
            'tanggal_penting' => now()->addDays(2)->toDateString(),
        ]);
        $this->actingAs($owner);

        $response = $this->get(route('corporate-actions.index'));

        $response->assertOk()
            ->assertSee('Agenda milik saya')
            ->assertDontSee('Agenda milik orang lain')
            ->assertDontSee('OTHER');
    }

    public function test_market_overview_shows_corporate_action_agenda_entry(): void
    {
        $saham = $this->createSaham();
        $saham->corporateActions()->create([
            'jenis' => 'Dividen final',
            'tanggal_penting' => now()->addDays(5)->toDateString(),
        ]);

        $response = $this->get(route('market.overview'));

        $response->assertOk()
            ->assertSee('Agenda aksi korporasi')
            ->assertSee('1 catatan akan datang')
            ->assertSee(route('corporate-actions.index'));
    }

    public function test_corporate_action_agenda_rejects_unknown_time_filter(): void
    {
        $this->signInAsOwner();

        $this->from(route('market.overview'))
            ->get(route('corporate-actions.index', ['waktu' => 'future-ish']))
            ->assertRedirect(route('market.overview'))
            ->assertSessionHasErrors('waktu');
    }

    public function test_issuer_detail_renders_market_tabs_and_data_entry_sections(): void
    {
        $saham = $this->createSaham();

        $response = $this->get(route('issuers.show', $saham));

        $response->assertOk()
            ->assertSee('Orderbook')
            ->assertSee('Financials')
            ->assertSee('Analysis & Valuation')
            ->assertSee('News & Disclosure')
            ->assertSee('Running Trade')
            ->assertSee('SIMULASI SAJA')
            ->assertSee('Candlestick OHLC')
            ->assertSee('Grafik OHLC historis');
    }

    public function test_issuer_detail_calculates_a_transparent_checklist_and_peer_comparison(): void
    {
        $saham = $this->createSaham([
            'roe' => 14,
            'pertumbuhan_pendapatan' => 5,
            'rasio_utang_ekuitas' => 0.8,
            'per' => 10,
            'pbv' => 1.5,
        ]);
        $saham->financials()->create([
            'periode' => '2026-Q3',
            'jenis_periode' => 'kuartal',
            'arus_kas_operasi' => 100000,
            'sumber' => 'Laporan resmi',
        ]);
        $this->createSaham([
            'kode_emiten' => 'TLKM',
            'nama_perusahaan' => 'Telkom Indonesia',
            'per' => 15,
            'pbv' => 2,
            'roe' => 12,
        ]);

        $response = $this->get(route('issuers.show', $saham));

        $response->assertOk()
            ->assertSee('4')
            ->assertSee('Rata-rata peer tersimpan')
            ->assertSee('2026-Q3')
            ->assertSee('TLKM');
    }

    public function test_community_post_is_saved_for_issuer(): void
    {
        $saham = $this->createSaham();

        $response = $this->post(route('issuer-posts.store', $saham), [
            'nama_penulis' => 'Investor',
            'isi' => 'Diskusi tentang laporan kuartal emiten.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('saham_posts', [
            'saham_id' => $saham->id,
            'nama_penulis' => 'Investor',
        ]);
    }

    public function test_new_watchlist_symbol_is_saved_without_creating_a_stock_position(): void
    {
        $this->signInAsOwner();
        $response = $this->post(route('watchlist.store'), [
            'kode_emiten' => 'ABCD',
            'nama_perusahaan' => 'Example Company',
            'sektor' => 'Teknologi',
        ]);

        $response->assertRedirect(route('watchlist.index'));
        $this->assertDatabaseHas('sahams', [
            'kode_emiten' => 'ABCD',
            'jumlah_lot' => 0,
            'is_favorite' => true,
        ]);
    }

    public function test_main_investment_pages_render_successfully(): void
    {
        $this->get(route('saham.index'))
            ->assertRedirect(route('login'));

        $this->signInAsOwner();
        $this->get(route('saham.index'))
            ->assertOk()
            ->assertSee('Candlestick Historis')
            ->assertSee('chartTimeframeSelector');

        foreach ([
            route('market.overview'),
            route('watchlist.index'),
            route('screening.index'),
            route('transactions.index'),
            route('profile.index'),
            route('guide.index'),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_investor_guide_explains_market_terms_and_current_data_limits(): void
    {
        $this->signInAsOwner();
        $response = $this->get(route('guide.index'));

        $response->assertOk()
            ->assertSee('Orderbook')
            ->assertSee('Dividend Yield')
            ->assertSee('Balance Sheet')
            ->assertSee('Right issue')
            ->assertSee('feed orderbook aktual belum tersedia');
    }

    #[DataProvider('riskProfileScoreBoundaries')]
    public function test_risk_profile_assessment_saves_the_correct_category_at_score_boundaries(
        array $answers,
        int $expectedScore,
        string $expectedCategory,
    ): void {
        $user = $this->signInAsOwner();

        $response = $this->post(route('risk-profile.store'), $answers);

        $response->assertRedirect(route('profile.index'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'risk_profile_score' => $expectedScore,
        ]);
        $this->get(route('profile.index'))
            ->assertOk()
            ->assertSee($expectedCategory)
            ->assertSee("{$expectedScore} dari 20");
    }

    public static function riskProfileScoreBoundaries(): array
    {
        return [
            'conservative maximum' => [
                [
                    'jangka_waktu' => 'kurang_1_tahun',
                    'reaksi_rugi' => 'jual_sebagian',
                    'kenyamanan_fluktuasi' => 'sedikit_turun',
                    'kebutuhan_dana' => '1_3_tahun',
                    'pengalaman' => 'pernah',
                ],
                9,
                'Konservatif',
            ],
            'moderate minimum' => [
                [
                    'jangka_waktu' => '1_3_tahun',
                    'reaksi_rugi' => 'jual_sebagian',
                    'kenyamanan_fluktuasi' => 'sedikit_turun',
                    'kebutuhan_dana' => '1_3_tahun',
                    'pengalaman' => 'pernah',
                ],
                10,
                'Moderat',
            ],
            'moderate maximum' => [
                [
                    'jangka_waktu' => '3_5_tahun',
                    'reaksi_rugi' => 'tahan',
                    'kenyamanan_fluktuasi' => 'fluktuasi_wajar',
                    'kebutuhan_dana' => '3_5_tahun',
                    'pengalaman' => 'berpengalaman',
                ],
                15,
                'Moderat',
            ],
            'aggressive minimum' => [
                [
                    'jangka_waktu' => 'lebih_5_tahun',
                    'reaksi_rugi' => 'tahan',
                    'kenyamanan_fluktuasi' => 'fluktuasi_wajar',
                    'kebutuhan_dana' => '3_5_tahun',
                    'pengalaman' => 'berpengalaman',
                ],
                16,
                'Agresif',
            ],
        ];
    }

    public function test_risk_profile_rejects_invalid_answers_without_changing_saved_score(): void
    {
        $user = $this->signInAsOwner();
        $user->forceFill(['risk_profile_score' => 12])->save();

        $response = $this->from(route('profile.index'))->post(route('risk-profile.store'), [
            'jangka_waktu' => 'invalid',
            'reaksi_rugi' => 'jual_semua',
            'kenyamanan_fluktuasi' => 'tidak_nyaman',
            'kebutuhan_dana' => 'kurang_1_tahun',
            'pengalaman' => 'pemula',
        ]);

        $response->assertRedirect(route('profile.index'))
            ->assertSessionHasErrors('jangka_waktu');
        $this->assertSame(12, $user->fresh()->risk_profile_score);
    }

    public function test_risk_profile_assessment_requires_authentication(): void
    {
        $this->post(route('risk-profile.store'), [])
            ->assertRedirect(route('login'));
    }

    public function test_auto_order_does_not_execute_without_a_valid_market_quote(): void
    {
        config(['services.alpha_vantage.api_key' => 'test-key']);
        Http::preventStrayRequests();
        Http::fake([
            'https://www.alphavantage.co/query*' => Http::response([
                'Note' => 'API call frequency is limited.',
            ]),
            'https://query1.finance.yahoo.com/v8/finance/chart/*' => Http::response([
                'chart' => [
                    'result' => null,
                    'error' => ['description' => 'No data available for symbol.'],
                ],
            ], 404),
        ]);
        $saham = $this->createSaham(['jumlah_lot' => 2]);
        $rule = AutoOrder::create([
            'saham_id' => $saham->id,
            'jenis' => 'beli',
            'jumlah_lot' => 3,
            'harga_pemicu' => 95,
        ]);

        $response = $this->from(route('transactions.index'))->post(route('market-rules.check'));

        $response->assertRedirect(route('transactions.index'))->assertSessionHasErrors('market');
        $this->assertSame('aktif', $rule->refresh()->status);
        $this->assertDatabaseCount('paper_orders', 0);
    }

    private function createSaham(array $overrides = []): Saham
    {
        $this->signInAsOwner();

        return Saham::create(array_merge([
            'nama_pemilik' => 'Uqi',
            'kode_emiten' => 'BBCA',
            'nama_perusahaan' => 'Bank Central Asia',
            'sektor' => 'Keuangan',
            'jumlah_lot' => 10,
            'harga_beli' => 10000,
            'tanggal_beli' => '2026-01-01',
        ], $overrides));
    }

    private function signInAsOwner(): User
    {
        if (auth()->check()) {
            return auth()->user();
        }

        $owner = User::factory()->create();
        $this->actingAs($owner);

        return $owner;
    }
}
