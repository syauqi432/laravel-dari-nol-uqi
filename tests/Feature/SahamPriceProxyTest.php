<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SahamPriceProxyTest extends TestCase
{
    public function test_returns_alpha_vantage_quote_through_the_server_proxy(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://www.alphavantage.co/query*' => Http::response([
                'Global Quote' => [
                    '01. symbol' => 'BBCA.JK',
                    '05. price' => '10350.0000',
                ],
            ]),
        ]);

        $response = $this->getJson('/api/harga-saham/BBCA');

        $response->assertOk()->assertJson([
            'Global Quote' => [
                '01. symbol' => 'BBCA.JK',
                '05. price' => '10350.0000',
            ],
        ]);

        Http::assertSent(function (Request $request): bool {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return $request->url() === 'https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol=BBCA.JK&apikey='.config('services.alpha_vantage.api_key')
                && ($query['apikey'] ?? null) === config('services.alpha_vantage.api_key');
        });
    }

    public function test_keeps_exchange_qualified_symbols_unchanged(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://www.alphavantage.co/query*' => Http::response([
                'Global Quote' => [
                    '01. symbol' => 'BBCA.JK',
                    '05. price' => '10350.0000',
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
}
