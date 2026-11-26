<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SahamPriceProxyTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_alpha_vantage_quote_through_the_server_proxy(): void
    {
        config(['services.alpha_vantage.api_key' => 'test-key']);
        $this->actingAs(User::factory()->create());
        Http::preventStrayRequests();
        Http::fake([
            'https://www.alphavantage.co/query*' => Http::response([
                'Global Quote' => [
                    '01. symbol' => 'BBCA.JK',
                    '05. price' => '10350.0000',
                    '07. latest trading day' => now(config('app.timezone'))->toDateString(),
                ],
            ]),
        ]);

        $response = $this->getJson('/api/harga-saham/BBCA');

        $response->assertOk()->assertJson([
            'Global Quote' => [
                '01. symbol' => 'BBCA.JK',
                '05. price' => '10350.0000',
            ],
        ])->assertJsonPath('source', 'Alpha Vantage')
            ->assertJsonPath('status', 'available')
            ->assertJsonPath('freshness', 'recent');

        Http::assertSent(function (Request $request): bool {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return $request->url() === 'https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol=BBCA.JK&apikey='.config('services.alpha_vantage.api_key')
                && ($query['apikey'] ?? null) === config('services.alpha_vantage.api_key');
        });
    }

    public function test_keeps_exchange_qualified_symbols_unchanged(): void
    {
        config(['services.alpha_vantage.api_key' => 'test-key']);
        $this->actingAs(User::factory()->create());
        Http::preventStrayRequests();
        Http::fake([
            'https://www.alphavantage.co/query*' => Http::response([
                'Global Quote' => [
                    '01. symbol' => 'BBCA.JK',
                    '05. price' => '10350.0000',
                    '07. latest trading day' => now(config('app.timezone'))->toDateString(),
                ],
            ]),
        ]);

        $response = $this->getJson('/api/harga-saham/BBCA.JK');

        $response->assertOk();
        Http::assertSent(function (Request $request): bool {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return ($query['symbol'] ?? null) === 'BBCA.JK';
        });
    }

    public function test_caches_successful_quotes_to_conserve_the_provider_daily_quota(): void
    {
        config(['services.alpha_vantage.api_key' => 'test-key']);
        Cache::flush();
        $this->actingAs(User::factory()->create());
        Http::preventStrayRequests();
        Http::fake([
            'https://www.alphavantage.co/query*' => Http::response([
                'Global Quote' => [
                    '01. symbol' => 'BBCA.JK',
                    '05. price' => '10350.0000',
                    '07. latest trading day' => now(config('app.timezone'))->toDateString(),
                ],
            ]),
        ]);

        $this->getJson('/api/harga-saham/BBCA')->assertOk();
        $this->getJson('/api/harga-saham/BBCA')->assertOk();

        Http::assertSentCount(1);
    }

    public function test_uses_yahoo_finance_after_alpha_vantage_reaches_its_daily_quota(): void
    {
        config(['services.alpha_vantage.api_key' => 'test-key']);
        Cache::flush();
        $this->actingAs(User::factory()->create());
        Http::preventStrayRequests();
        Http::fake([
            'https://www.alphavantage.co/query*' => Http::response([
                'Information' => 'The 25 requests per day limit has been reached.',
            ]),
            'https://query1.finance.yahoo.com/v8/finance/chart/*' => Http::response($this->yahooChartPayload()),
        ]);

        $firstQuote = $this->getJson('/api/harga-saham/BBCA');
        $secondQuote = $this->getJson('/api/harga-saham/TLKM');

        $firstQuote->assertOk()
            ->assertJsonPath('source', 'Yahoo Finance')
            ->assertJson([
                'Global Quote' => [
                    '05. price' => '105',
                    '09. change' => '5',
                    '10. change percent' => '5.0000%',
                ],
            ]);
        $secondQuote->assertOk()->assertJsonPath('source', 'Yahoo Finance');
        Http::assertSentCount(3);
    }

    public function test_uses_yahoo_finance_when_alpha_vantage_has_no_api_key(): void
    {
        config(['services.alpha_vantage.api_key' => null]);
        $this->actingAs(User::factory()->create());
        Http::preventStrayRequests();
        Http::fake([
            'https://query1.finance.yahoo.com/v8/finance/chart/*' => Http::response($this->yahooChartPayload()),
        ]);

        $response = $this->getJson('/api/harga-saham/BBCA');

        $response->assertOk()->assertJsonPath('source', 'Yahoo Finance');
        Http::assertSentCount(1);
    }

    public function test_resolves_bsi_alias_to_the_listed_bris_ticker(): void
    {
        config(['services.alpha_vantage.api_key' => null]);
        $this->actingAs(User::factory()->create());
        Http::preventStrayRequests();
        Http::fake([
            'https://query1.finance.yahoo.com/v8/finance/chart/BRIS.JK*' => Http::response($this->yahooChartPayload()),
        ]);

        $response = $this->getJson('/api/harga-saham/bsi');

        $response->assertOk()
            ->assertJsonPath('source', 'Yahoo Finance')
            ->assertJson([
                'Global Quote' => [
                    '01. symbol' => 'BRIS.JK',
                ],
            ]);
        Http::assertSentCount(1);
    }

    /**
     * @return array<string, mixed>
     */
    private function yahooChartPayload(): array
    {
        return [
            'chart' => [
                'result' => [[
                    'meta' => [
                        'symbol' => 'BBCA.JK',
                        'regularMarketPrice' => 105,
                        'chartPreviousClose' => 100,
                    ],
                    'timestamp' => [
                        now(config('app.timezone'))->subDay()->timestamp,
                        now(config('app.timezone'))->timestamp,
                    ],
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
        ];
    }
}
