<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class YahooFinanceMarketData
{
    private const SOURCE = 'Yahoo Finance';

    /**
     * @return array<string, mixed>
     */
    public function quote(string $symbol, bool $forceRefresh = false): array
    {
        $cacheKey = 'market-quote:yahoo:'.strtoupper($symbol);
        $cachedQuote = $forceRefresh ? null : Cache::get($cacheKey);

        if (is_array($cachedQuote)) {
            return $cachedQuote;
        }

        $chart = $this->fetchChart($symbol, '1d', '5d');

        if ($chart['status'] !== 'available') {
            Cache::put($cacheKey, $chart, now()->addMinute());

            return $chart;
        }

        $result = $chart['result'];
        $timestamps = $result['timestamp'] ?? [];
        $dailyQuote = $result['indicators']['quote'][0] ?? [];
        $lastIndex = count($timestamps) - 1;
        $price = $this->numericValue($result['meta']['regularMarketPrice'] ?? null)
            ?? $this->numericValue($dailyQuote['close'][$lastIndex] ?? null);
        $lastUpdated = $this->dateFromTimestamp($timestamps[$lastIndex] ?? null);

        if ($lastIndex < 0 || $price === null || $price <= 0 || $lastUpdated === null) {
            $unavailable = $this->unavailable('Yahoo Finance tidak mengembalikan quote yang valid.');
            Cache::put($cacheKey, $unavailable, now()->addMinute());

            return $unavailable;
        }

        $previousClose = null;
        for ($index = $lastIndex - 1; $index >= 0; $index--) {
            $previousClose = $this->numericValue($dailyQuote['close'][$index] ?? null);

            if ($previousClose !== null && $previousClose > 0) {
                break;
            }
        }
        $previousClose ??= $this->numericValue($result['meta']['chartPreviousClose'] ?? null);

        $change = $previousClose !== null ? $price - $previousClose : null;
        $changePercent = $previousClose ? ($change / $previousClose) * 100 : null;

        $quote = [
            'status' => 'available',
            'freshness' => $this->freshness($lastUpdated),
            'source' => self::SOURCE,
            'retrieved_at' => now(config('app.timezone'))->toIso8601String(),
            'last_updated' => $lastUpdated,
            'Global Quote' => [
                '01. symbol' => $this->providerSymbol($symbol),
                '02. open' => $this->numberString($dailyQuote['open'][$lastIndex] ?? null),
                '03. high' => $this->numberString($dailyQuote['high'][$lastIndex] ?? null),
                '04. low' => $this->numberString($dailyQuote['low'][$lastIndex] ?? null),
                '05. price' => $this->numberString($price),
                '06. volume' => $dailyQuote['volume'][$lastIndex] ?? '',
                '07. latest trading day' => $lastUpdated,
                '08. previous close' => $this->numberString($previousClose),
                '09. change' => $this->numberString($change),
                '10. change percent' => $changePercent === null ? '' : number_format($changePercent, 4, '.', '').'%',
            ],
        ];

        Cache::put($cacheKey, $quote, now()->addMinutes(2));

        return $quote;
    }

    /**
     * @return array<string, mixed>
     */
    public function historical(string $symbol, string $timeframe): array
    {
        [$interval, $range, $cacheMinutes] = match ($timeframe) {
            'daily' => ['1d', '6mo', 15],
            'weekly' => ['1wk', '5y', 60],
            'monthly' => ['1mo', '10y', 180],
        };
        $cacheKey = 'market-history:yahoo:'.strtoupper($symbol).':'.$timeframe;
        $cachedHistory = Cache::get($cacheKey);

        if (is_array($cachedHistory)) {
            return $cachedHistory;
        }

        $chart = $this->fetchChart($symbol, $interval, $range);

        if ($chart['status'] !== 'available') {
            $unavailable = [...$chart, 'candles' => []];
            Cache::put($cacheKey, $unavailable, now()->addMinute());

            return $unavailable;
        }

        $result = $chart['result'];
        $timestamps = $result['timestamp'] ?? [];
        $dailyQuote = $result['indicators']['quote'][0] ?? [];
        $candles = [];

        foreach (array_slice($timestamps, -100, null, true) as $index => $timestamp) {
            $open = $this->numericValue($dailyQuote['open'][$index] ?? null);
            $high = $this->numericValue($dailyQuote['high'][$index] ?? null);
            $low = $this->numericValue($dailyQuote['low'][$index] ?? null);
            $close = $this->numericValue($dailyQuote['close'][$index] ?? null);
            $date = $this->dateFromTimestamp($timestamp);

            if (
                $open === null || $high === null || $low === null || $close === null || $date === null
                || $open <= 0 || $high <= 0 || $low <= 0 || $close <= 0
                || $high < max($open, $close) || $low > min($open, $close) || $low > $high
            ) {
                continue;
            }

            $volume = filter_var($dailyQuote['volume'][$index] ?? null, FILTER_VALIDATE_INT);
            $candles[] = [
                'time' => $date,
                'open' => $open,
                'high' => $high,
                'low' => $low,
                'close' => $close,
                'volume' => $volume === false ? null : $volume,
            ];
        }

        if ($candles === []) {
            $unavailable = $this->unavailable(
                'Yahoo Finance tidak mengembalikan candle OHLC yang valid untuk simbol ini.',
                ['candles' => []],
            );
            Cache::put($cacheKey, $unavailable, now()->addMinute());

            return $unavailable;
        }

        $lastUpdated = $candles[array_key_last($candles)]['time'];
        $maxAgeDays = match ($timeframe) {
            'daily' => 4,
            'weekly' => 12,
            'monthly' => 40,
        };
        $history = [
            'status' => 'available',
            'freshness' => $this->freshness($lastUpdated, $maxAgeDays),
            'source' => self::SOURCE,
            'timeframe' => $timeframe,
            'last_updated' => $lastUpdated,
            'retrieved_at' => now(config('app.timezone'))->toIso8601String(),
            'candles' => $candles,
        ];

        Cache::put($cacheKey, $history, now()->addMinutes($cacheMinutes));

        return $history;
    }

    /**
     * @return array<string, mixed>
     */
    public function index(): array
    {
        $cacheKey = 'market-index:yahoo:jkse';
        $cachedIndex = Cache::get($cacheKey);

        if (is_array($cachedIndex)) {
            return $cachedIndex;
        }

        $chart = $this->fetchChart('^JKSE', '1d', '5d');

        if ($chart['status'] !== 'available') {
            Cache::put($cacheKey, $chart, now()->addMinute());

            return $chart;
        }

        $result = $chart['result'];
        $timestamps = $result['timestamp'] ?? [];
        $closes = $result['indicators']['quote'][0]['close'] ?? [];
        $lastIndex = count($timestamps) - 1;
        $value = $this->numericValue($result['meta']['regularMarketPrice'] ?? null)
            ?? $this->numericValue($closes[$lastIndex] ?? null);
        $lastUpdated = $this->dateFromTimestamp($timestamps[$lastIndex] ?? null);
        $previousClose = null;

        for ($index = $lastIndex - 1; $index >= 0; $index--) {
            $previousClose = $this->numericValue($closes[$index] ?? null);

            if ($previousClose !== null && $previousClose > 0) {
                break;
            }
        }

        if ($lastIndex < 0 || $value === null || $value <= 0 || $lastUpdated === null) {
            $unavailable = $this->unavailable('Data IHSG tidak tersedia dari Yahoo Finance.');
            Cache::put($cacheKey, $unavailable, now()->addMinute());

            return $unavailable;
        }

        $change = $previousClose !== null ? $value - $previousClose : null;
        $index = [
            'status' => 'available',
            'name' => 'IHSG',
            'symbol' => '^JKSE',
            'value' => round($value, 2),
            'change' => $change === null ? null : round($change, 2),
            'change_percent' => $previousClose ? round(($change / $previousClose) * 100, 2) : null,
            'freshness' => $this->freshness($lastUpdated),
            'source' => self::SOURCE,
            'last_updated' => $lastUpdated,
            'retrieved_at' => now(config('app.timezone'))->toIso8601String(),
        ];

        Cache::put($cacheKey, $index, now()->addMinutes(2));

        return $index;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchChart(string $symbol, string $interval, string $range): array
    {
        $url = rtrim(config('services.yahoo_finance.chart_url'), '/').'/'.rawurlencode($this->providerSymbol($symbol));
        $response = Http::acceptJson()
            ->withHeaders([
                'Referer' => 'https://finance.yahoo.com/',
                'User-Agent' => 'Mozilla/5.0',
            ])
            ->connectTimeout(3)
            ->timeout(10)
            ->get($url, [
                'interval' => $interval,
                'range' => $range,
            ]);

        $payload = $response->json();
        $result = is_array($payload) ? ($payload['chart']['result'][0] ?? null) : null;

        if (! $response->successful() || ! is_array($result)) {
            $message = is_array($payload)
                ? ($payload['chart']['error']['description'] ?? 'Yahoo Finance tidak mengembalikan data yang valid.')
                : 'Yahoo Finance mengembalikan respons yang tidak dapat dibaca.';

            return $this->unavailable($message);
        }

        return [
            'status' => 'available',
            'result' => $result,
        ];
    }

    private function providerSymbol(string $symbol): string
    {
        $symbol = strtoupper($symbol);

        if ($symbol === 'BSI') {
            return 'BRIS.JK';
        }

        return str_contains($symbol, '.') || str_starts_with($symbol, '^') ? $symbol : $symbol.'.JK';
    }

    private function dateFromTimestamp(mixed $timestamp): ?string
    {
        $timestamp = filter_var($timestamp, FILTER_VALIDATE_INT);

        if ($timestamp === false) {
            return null;
        }

        return Carbon::createFromTimestampUTC($timestamp)
            ->setTimezone(config('app.timezone'))
            ->toDateString();
    }

    private function freshness(string $date, int $maxAgeDays = 4): string
    {
        $date = Carbon::createFromFormat('Y-m-d', $date, config('app.timezone'))->startOfDay();
        $today = now(config('app.timezone'))->startOfDay();

        if ($date->greaterThan($today)) {
            return 'unknown';
        }

        return $date->diffInDays($today) <= $maxAgeDays ? 'recent' : 'stale';
    }

    private function numericValue(mixed $value): ?float
    {
        $number = filter_var($value, FILTER_VALIDATE_FLOAT);

        return $number !== false && is_finite((float) $number) ? (float) $number : null;
    }

    private function numberString(mixed $value): string
    {
        $number = $this->numericValue($value);

        return $number === null ? '' : (string) $number;
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function unavailable(string $message, array $extra = []): array
    {
        return [
            'status' => 'unavailable',
            'freshness' => 'unknown',
            'source' => self::SOURCE,
            'message' => $message,
            ...$extra,
        ];
    }
}
