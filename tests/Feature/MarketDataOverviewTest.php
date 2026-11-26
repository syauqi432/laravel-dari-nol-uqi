<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MarketDataOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_ihsg_index_data_from_yahoo_finance(): void
    {
        Cache::flush();
        $this->actingAs(User::factory()->create());
        Http::preventStrayRequests();
        Http::fake([
            'https://query1.finance.yahoo.com/v8/finance/chart/*' => Http::response($this->yahooIndexPayload()),
        ]);

        $response = $this->getJson('/api/market/index');

        $response->assertOk()
            ->assertJsonPath('status', 'available')
            ->assertJsonPath('name', 'IHSG')
            ->assertJsonPath('source', 'Yahoo Finance')
            ->assertJsonPath('value', 5200)
            ->assertJsonPath('change', 50)
            ->assertJsonPath('change_percent', 0.97);
        Http::assertSentCount(1);
    }

    public function test_returns_google_news_rss_headlines_for_the_market_dashboard(): void
    {
        Cache::flush();
        $this->actingAs(User::factory()->create());
        Http::preventStrayRequests();
        Http::fake([
            'https://news.google.com/rss/search*' => Http::response(
                '<?xml version="1.0" encoding="UTF-8"?>'
                    .'<rss version="2.0"><channel>'
                    .'<item><title>IHSG dibuka menguat &amp; saham bergerak</title>'
                    .'<link>https://news.google.com/rss/articles/story-id</link>'
                    .'<pubDate>Thu, 26 Nov 2026 05:04:47 GMT</pubDate>'
                    .'<source url="https://www.antaranews.com">ANTARA News</source></item>'
                    .'</channel></rss>',
                200,
                ['Content-Type' => 'application/rss+xml'],
            ),
        ]);

        $response = $this->getJson('/api/market/news');

        $response->assertOk()
            ->assertJsonPath('status', 'available')
            ->assertJsonPath('source', 'Google News RSS')
            ->assertJsonPath('headlines.0.title', 'IHSG dibuka menguat & saham bergerak')
            ->assertJsonPath('headlines.0.source', 'ANTARA News')
            ->assertJsonPath('headlines.0.url', 'https://news.google.com/rss/articles/story-id');
        Http::assertSentCount(1);
    }

    /**
     * @return array<string, mixed>
     */
    private function yahooIndexPayload(): array
    {
        return [
            'chart' => [
                'result' => [[
                    'meta' => [
                        'symbol' => '^JKSE',
                        'regularMarketPrice' => 5200,
                    ],
                    'timestamp' => [
                        now(config('app.timezone'))->subDay()->timestamp,
                        now(config('app.timezone'))->timestamp,
                    ],
                    'indicators' => [
                        'quote' => [[
                            'close' => [5150, 5200],
                        ]],
                    ],
                ]],
                'error' => null,
            ],
        ];
    }
}
