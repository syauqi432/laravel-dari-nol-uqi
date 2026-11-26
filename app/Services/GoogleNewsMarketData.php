<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GoogleNewsMarketData
{
    /**
     * @return array<string, mixed>
     */
    public function headlines(): array
    {
        $cacheKey = 'market-news:ihsg';
        $cachedHeadlines = Cache::get($cacheKey);

        if (is_array($cachedHeadlines)) {
            return $cachedHeadlines;
        }

        $response = Http::withHeaders([
            'Accept' => 'application/rss+xml, application/xml, text/xml',
            'User-Agent' => 'Mozilla/5.0',
        ])
            ->connectTimeout(3)
            ->timeout(10)
            ->get(config('services.google_news.rss_url'), [
                'q' => 'IHSG OR saham Indonesia',
                'hl' => 'id',
                'gl' => 'ID',
                'ceid' => 'ID:id',
            ]);

        if (! $response->successful()) {
            $unavailable = $this->unavailable('Feed berita Google News tidak dapat diakses.');
            Cache::put($cacheKey, $unavailable, now()->addMinute());

            return $unavailable;
        }

        $previousInternalErrors = libxml_use_internal_errors(true);
        $feed = simplexml_load_string($response->body(), \SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);
        libxml_clear_errors();
        libxml_use_internal_errors($previousInternalErrors);

        if (! $feed instanceof \SimpleXMLElement || ! isset($feed->channel->item)) {
            $unavailable = $this->unavailable('Feed berita tidak memiliki format RSS yang valid.');
            Cache::put($cacheKey, $unavailable, now()->addMinute());

            return $unavailable;
        }

        $headlines = [];

        foreach ($feed->channel->item as $item) {
            $title = trim((string) $item->title);
            $url = trim((string) $item->link);
            $parsedUrl = parse_url($url);

            if (
                $title === '' || ! is_array($parsedUrl)
                || ($parsedUrl['scheme'] ?? null) !== 'https'
                || filter_var($url, FILTER_VALIDATE_URL) === false
            ) {
                continue;
            }

            $publishedTimestamp = strtotime((string) $item->pubDate);
            $source = trim((string) $item->source);
            $headlines[] = [
                'title' => $title,
                'url' => $url,
                'source' => $source !== '' ? $source : 'Google News',
                'published_at' => $publishedTimestamp === false
                    ? null
                    : Carbon::createFromTimestamp($publishedTimestamp, 'UTC')->toIso8601String(),
            ];

            if (count($headlines) === 6) {
                break;
            }
        }

        if ($headlines === []) {
            $unavailable = $this->unavailable('Belum ada berita pasar yang dapat ditampilkan.');
            Cache::put($cacheKey, $unavailable, now()->addMinute());

            return $unavailable;
        }

        $data = [
            'status' => 'available',
            'source' => 'Google News RSS',
            'retrieved_at' => now(config('app.timezone'))->toIso8601String(),
            'headlines' => $headlines,
        ];
        Cache::put($cacheKey, $data, now()->addMinutes(10));

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function unavailable(string $message): array
    {
        return [
            'status' => 'unavailable',
            'source' => 'Google News RSS',
            'message' => $message,
            'headlines' => [],
        ];
    }
}
