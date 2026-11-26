<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AlphaVantageMarketData
{
    private const SOURCE = 'Alpha Vantage';

    public function __construct(private YahooFinanceMarketData $yahooFinance) {}

    /**
     * @return array<string, mixed>
     */
    public function quote(string $symbol, bool $forceRefresh = false): array
    {
        $apiKey = config('services.alpha_vantage.api_key');

        if (! $apiKey || Cache::has('market-data:alpha:cooldown')) {
            return $this->yahooFinance->quote($symbol, $forceRefresh);
        }

        $cacheKey = 'market-quote:alpha:v2:'.strtoupper($symbol);
        $cachedQuote = $forceRefresh ? null : Cache::get($cacheKey);

        if (is_array($cachedQuote) && ($cachedQuote['freshness'] ?? null) !== 'stale') {
            return $cachedQuote;
        }

        try {
            $quote = $this->fetchQuote($symbol, $apiKey);
        } catch (ConnectionException $exception) {
            report($exception);
            Cache::put('market-data:alpha:cooldown', true, now()->addMinutes(10));
            $quote = $this->unavailable('Alpha Vantage tidak dapat dihubungi.');
        }

        if ($quote['status'] === 'available') {
            if (! $forceRefresh) {
                Cache::put($cacheKey, $quote, now()->addMinutes(5));
            }

            return $quote;
        }

        return $this->fallbackToYahooQuote($symbol, $forceRefresh, $quote);
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchQuote(string $symbol, string $apiKey): array
    {
        $response = Http::connectTimeout(3)
            ->timeout(8)
            ->get(config('services.alpha_vantage.base_url'), [
                'function' => 'GLOBAL_QUOTE',
                'symbol' => $this->providerSymbol($symbol),
                'apikey' => $apiKey,
            ]);

        $payload = $response->json();
        $quote = is_array($payload) ? ($payload['Global Quote'] ?? null) : null;
        $price = is_array($quote) ? filter_var($quote['05. price'] ?? null, FILTER_VALIDATE_FLOAT) : false;

        if (! $response->successful() || ! is_array($quote) || $price === false || $price <= 0) {
            return $this->unavailable($this->providerFailureMessage(
                $payload,
                'Quote tidak tersedia dari penyedia; periksa simbol, API key, atau kuota.',
            ));
        }

        $tradingDate = $quote['07. latest trading day'] ?? null;

        return [
            'status' => 'available',
            'freshness' => $this->freshness($tradingDate),
            'source' => self::SOURCE,
            'retrieved_at' => now(config('app.timezone'))->toIso8601String(),
            'last_updated' => $tradingDate,
            'Global Quote' => $quote,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function historical(string $symbol, string $timeframe): array
    {
        $apiKey = config('services.alpha_vantage.api_key');

        if (! $apiKey || Cache::has('market-data:alpha:cooldown')) {
            return $this->yahooFinance->historical($symbol, $timeframe);
        }

        $cacheMinutes = match ($timeframe) {
            'daily' => 1440,
            'weekly' => 10080,
            'monthly' => 44640,
        };
        $cacheKey = 'market-history:alpha:'.strtoupper($symbol).':'.$timeframe;
        $history = Cache::get($cacheKey);

        if (is_array($history)) {
            return $history;
        }

        try {
            $history = $this->fetchHistorical($symbol, $timeframe, $apiKey);
        } catch (ConnectionException $exception) {
            report($exception);
            Cache::put('market-data:alpha:cooldown', true, now()->addMinutes(10));
            $history = $this->unavailable('Alpha Vantage tidak dapat dihubungi.', ['candles' => []]);
        }

        if ($history['status'] === 'available') {
            Cache::put($cacheKey, $history, now()->addMinutes($cacheMinutes));

            return $history;
        }

        return $this->fallbackToYahooHistory($symbol, $timeframe, $history);
    }

    /**
     * @param  array<string, mixed>  $alphaQuote
     * @return array<string, mixed>
     */
    private function fallbackToYahooQuote(string $symbol, bool $forceRefresh, array $alphaQuote): array
    {
        $message = $alphaQuote['message'] ?? 'Quote Alpha Vantage tidak tersedia.';

        if (str_contains(strtolower($message), 'kuota harian')) {
            Cache::put('market-data:alpha:cooldown', true, now()->addDay());
        }

        $quote = $this->yahooFinance->quote($symbol, $forceRefresh);

        if ($quote['status'] === 'available') {
            return $quote;
        }

        $quote['message'] = 'Alpha Vantage: '.$message.' Yahoo Finance: '.($quote['message'] ?? 'quote tidak tersedia.');

        return $quote;
    }

    /**
     * @param  array<string, mixed>  $alphaHistory
     * @return array<string, mixed>
     */
    private function fallbackToYahooHistory(string $symbol, string $timeframe, array $alphaHistory): array
    {
        $message = $alphaHistory['message'] ?? 'Data historis Alpha Vantage tidak tersedia.';

        if (str_contains(strtolower($message), 'kuota harian')) {
            Cache::put('market-data:alpha:cooldown', true, now()->addDay());
        }

        $history = $this->yahooFinance->historical($symbol, $timeframe);

        if ($history['status'] === 'available') {
            return $history;
        }

        $history['message'] = 'Alpha Vantage: '.$message.' Yahoo Finance: '.($history['message'] ?? 'data historis tidak tersedia.');

        return $history;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchHistorical(string $symbol, string $timeframe, string $apiKey): array
    {
        [$function, $seriesName] = match ($timeframe) {
            'daily' => ['TIME_SERIES_DAILY', 'Time Series (Daily)'],
            'weekly' => ['TIME_SERIES_WEEKLY', 'Weekly Time Series'],
            'monthly' => ['TIME_SERIES_MONTHLY', 'Monthly Time Series'],
        };

        $response = Http::connectTimeout(3)
            ->timeout(10)
            ->get(config('services.alpha_vantage.base_url'), [
                'function' => $function,
                'symbol' => $this->providerSymbol($symbol),
                'apikey' => $apiKey,
                ...($timeframe === 'daily' ? ['outputsize' => 'compact'] : []),
            ]);
        $payload = $response->json();
        $series = is_array($payload) ? ($payload[$seriesName] ?? null) : null;

        if (! $response->successful() || ! is_array($series) || $series === []) {
            return $this->unavailable(
                $this->providerFailureMessage(
                    $payload,
                    'OHLC historis tidak tersedia; periksa dukungan simbol, API key, atau kuota.',
                ),
                ['candles' => []],
            );
        }

        $candles = [];
        foreach (array_slice($series, 0, 100, true) as $date => $values) {
            if (! is_string($date) || ! is_array($values)) {
                continue;
            }

            $open = filter_var($values['1. open'] ?? null, FILTER_VALIDATE_FLOAT);
            $high = filter_var($values['2. high'] ?? null, FILTER_VALIDATE_FLOAT);
            $low = filter_var($values['3. low'] ?? null, FILTER_VALIDATE_FLOAT);
            $close = filter_var($values['4. close'] ?? null, FILTER_VALIDATE_FLOAT);
            $volume = filter_var($values['5. volume'] ?? null, FILTER_VALIDATE_INT);

            if (
                $open === false || $high === false || $low === false || $close === false
                || $open <= 0 || $high <= 0 || $low <= 0 || $close <= 0
                || $high < max($open, $close) || $low > min($open, $close) || $low > $high
                || ! $this->isValidDate($date)
            ) {
                continue;
            }

            $candles[] = [
                'time' => $date,
                'open' => (float) $open,
                'high' => (float) $high,
                'low' => (float) $low,
                'close' => (float) $close,
                'volume' => $volume === false ? null : (int) $volume,
            ];
        }

        if ($candles === []) {
            return $this->unavailable(
                'Penyedia tidak mengembalikan candle OHLC yang valid untuk simbol ini.',
                ['candles' => []],
            );
        }

        $candles = array_reverse($candles);
        $lastUpdated = $candles[array_key_last($candles)]['time'];
        $maxAgeDays = match ($timeframe) {
            'daily' => 4,
            'weekly' => 12,
            'monthly' => 40,
        };

        return [
            'status' => 'available',
            'freshness' => $this->freshness($lastUpdated, $maxAgeDays),
            'source' => self::SOURCE,
            'timeframe' => $timeframe,
            'last_updated' => $lastUpdated,
            'retrieved_at' => now(config('app.timezone'))->toIso8601String(),
            'candles' => $candles,
        ];
    }

    private function isValidDate(string $date): bool
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        [$year, $month, $day] = array_map('intval', explode('-', $date));

        return checkdate($month, $day, $year);
    }

    private function providerSymbol(string $symbol): string
    {
        $symbol = strtoupper($symbol);

        if ($symbol === 'BSI') {
            return 'BRIS.JK';
        }

        return str_contains($symbol, '.') ? $symbol : $symbol.'.JK';
    }

    private function freshness(mixed $date, int $maxAgeDays = 4): string
    {
        if (! is_string($date) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return 'unknown';
        }

        [$year, $month, $day] = array_map('intval', explode('-', $date));

        if (! checkdate($month, $day, $year)) {
            return 'unknown';
        }

        $timezone = config('app.timezone');
        $date = Carbon::createFromDate($year, $month, $day, $timezone)->startOfDay();
        $today = now($timezone)->startOfDay();

        if ($date->greaterThan($today)) {
            return 'unknown';
        }

        return $date->diffInDays($today) <= $maxAgeDays ? 'recent' : 'stale';
    }

    private function providerFailureMessage(mixed $payload, string $fallback): string
    {
        if (! is_array($payload)) {
            return $fallback;
        }

        $providerNotice = strtolower(implode(' ', array_filter([
            $payload['Note'] ?? null,
            $payload['Information'] ?? null,
        ], 'is_string')));

        if (str_contains($providerNotice, 'rate limit') || str_contains($providerNotice, 'requests per day')) {
            return 'Kuota harian Alpha Vantage tercapai. Coba lagi setelah kuota direset.';
        }

        if (isset($payload['Error Message'])) {
            return 'Alpha Vantage menolak simbol atau jenis data yang diminta; periksa dukungan simbol.';
        }

        return $fallback;
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
