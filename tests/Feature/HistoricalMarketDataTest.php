<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HistoricalMarketDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_chronological_alpha_vantage_daily_ohlc_with_source_metadata(): void
    {
        config(['services.alpha_vantage.api_key' => 'test-key']);
        Cache::flush();
        $this->actingAs(User::factory()->create());
        Http::preventStrayRequests();

        $latestDate = now(config('app.timezone'))->toDateString();
        $previousDate = now(config('app.timezone'))->subDay()->toDateString();
        Http::fake([
            'https://www.alphavantage.co/query*' => Http::response([
                'Time Series (Daily)' => [
                    $latestDate => [
                        '1. open' => '100',
                        '2. high' => '110',
                        '3. low' => '90',
                        '4. close' => '105',
                        '5. volume' => '1200',
                    ],
                    $previousDate => [
                        '1. open' => '95',
                        '2. high' => '105',
                        '3. low' => '90',
                        '4. close' => '100',
                        '5. volume' => '1000',
                    ],
                    '2026-02-31' => [
                        '1. open' => '95',
                        '2. high' => '105',
                        '3. low' => '90',
                        '4. close' => '100',
                        '5. volume' => '1000',
                    ],
                ],
            ]),
        ]);

        $response = $this->getJson('/api/harga-saham/BBCA/historical?timeframe=daily');

        $response->assertOk()
            ->assertJsonPath('source', 'Alpha Vantage')
            ->assertJsonPath('status', 'available')
            ->assertJsonPath('freshness', 'recent')
            ->assertJsonPath('candles.0.time', $previousDate)
            ->assertJsonPath('candles.1.time', $latestDate)
            ->assertJsonPath('candles.1.open', 100)
            ->assertJsonCount(2, 'candles');

        $this->getJson('/api/harga-saham/BBCA/historical?timeframe=daily')->assertOk();
        Http::assertSentCount(1);

        Http::assertSent(function (Request $request): bool {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return ($query['function'] ?? null) === 'TIME_SERIES_DAILY'
                && ($query['symbol'] ?? null) === 'BBCA.JK'
                && ($query['outputsize'] ?? null) === 'compact'
                && ($query['apikey'] ?? null) === 'test-key';
        });
    }

    public function test_uses_yahoo_finance_history_when_alpha_vantage_daily_quota_is_exhausted(): void
    {
        config(['services.alpha_vantage.api_key' => 'test-key']);
        Cache::flush();
        $this->actingAs(User::factory()->create());
        Http::preventStrayRequests();

        $previousTimestamp = now(config('app.timezone'))->subDay()->timestamp;
        $latestTimestamp = now(config('app.timezone'))->timestamp;
        Http::fake([
            'https://www.alphavantage.co/query*' => Http::response([
                'Information' => 'The 25 requests per day limit has been reached.',
            ]),
            'https://query1.finance.yahoo.com/v8/finance/chart/*' => Http::response([
                'chart' => [
                    'result' => [[
                        'meta' => ['symbol' => 'BBCA.JK'],
                        'timestamp' => [$previousTimestamp, $latestTimestamp],
                        'indicators' => [
                            'quote' => [[
                                'open' => [95, 101],
                                'high' => [105, 110],
                                'low' => [90, 100],
                                'close' => [100, 105],
                                'volume' => [1000, 1200],
                            ]],
                        ],
                    ]],
                    'error' => null,
                ],
            ]),
        ]);

        $response = $this->getJson('/api/harga-saham/BBCA/historical?timeframe=daily');

        $response->assertOk()
            ->assertJsonPath('source', 'Yahoo Finance')
            ->assertJsonPath('status', 'available')
            ->assertJsonPath('candles.0.close', 100)
            ->assertJsonPath('candles.1.close', 105)
            ->assertJsonCount(2, 'candles');
        Http::assertSentCount(2);
    }

    public function test_marks_old_candles_stale_and_returns_explicit_unavailable_state(): void
    {
        config(['services.alpha_vantage.api_key' => 'test-key']);
        Cache::flush();
        $this->actingAs(User::factory()->create());
        Http::preventStrayRequests();

        $oldDate = now(config('app.timezone'))->subDays(8)->toDateString();
        Http::fake([
            'https://www.alphavantage.co/query*' => Http::sequence()
                ->push([
                    'Time Series (Daily)' => [
                        $oldDate => [
                            '1. open' => '100',
                            '2. high' => '110',
                            '3. low' => '90',
                            '4. close' => '105',
                            '5. volume' => '1200',
                        ],
                    ],
                ])
                ->push(['Information' => 'Rate limit: test-key reached the 25 requests per day limit.']),
            'https://query1.finance.yahoo.com/v8/finance/chart/*' => Http::response([
                'chart' => [
                    'result' => null,
                    'error' => ['description' => 'symbol not found'],
                ],
            ], 404),
        ]);

        $this->getJson('/api/harga-saham/BBCA/historical?timeframe=daily')
            ->assertOk()
            ->assertJsonPath('freshness', 'stale');

        $this->getJson('/api/harga-saham/TLKM/historical?timeframe=daily')
            ->assertServiceUnavailable()
            ->assertJsonPath('status', 'unavailable')
            ->assertJsonPath('source', 'Yahoo Finance')
            ->assertJsonPath('candles', [])
            ->assertJsonPath('message', 'Alpha Vantage: Kuota harian Alpha Vantage tercapai. Coba lagi setelah kuota direset. Yahoo Finance: symbol not found')
            ->assertDontSee('test-key');
    }
}
