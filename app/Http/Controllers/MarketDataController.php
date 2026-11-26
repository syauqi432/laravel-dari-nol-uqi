<?php

namespace App\Http\Controllers;

use App\Services\AlphaVantageMarketData;
use App\Services\GoogleNewsMarketData;
use App\Services\YahooFinanceMarketData;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketDataController extends Controller
{
    public function index(YahooFinanceMarketData $marketData): JsonResponse
    {
        try {
            $data = $marketData->index();
        } catch (ConnectionException $exception) {
            report($exception);

            return response()->json([
                'status' => 'unavailable',
                'source' => 'Yahoo Finance',
                'message' => 'Penyedia indeks pasar tidak dapat dihubungi.',
            ], 503);
        }

        return response()->json($data, $data['status'] === 'available' ? 200 : 503);
    }

    public function news(GoogleNewsMarketData $marketNews): JsonResponse
    {
        try {
            $data = $marketNews->headlines();
        } catch (ConnectionException $exception) {
            report($exception);

            return response()->json([
                'status' => 'unavailable',
                'source' => 'Google News RSS',
                'message' => 'Penyedia berita pasar tidak dapat dihubungi.',
                'headlines' => [],
            ], 503);
        }

        return response()->json($data, $data['status'] === 'available' ? 200 : 503);
    }

    public function quote(string $emiten, AlphaVantageMarketData $marketData): JsonResponse
    {
        try {
            $data = $marketData->quote($emiten);
        } catch (ConnectionException $exception) {
            report($exception);

            return response()->json([
                'status' => 'unavailable',
                'freshness' => 'unknown',
                'source' => 'Alpha Vantage / Yahoo Finance',
                'message' => 'Penyedia quote tidak dapat dihubungi.',
            ], 503);
        }

        return response()->json($data, $data['status'] === 'available' ? 200 : 503);
    }

    public function history(Request $request, string $emiten, AlphaVantageMarketData $marketData): JsonResponse
    {
        $validated = $request->validate([
            'timeframe' => ['nullable', 'in:daily,weekly,monthly'],
        ]);

        try {
            $data = $marketData->historical($emiten, $validated['timeframe'] ?? 'daily');
        } catch (ConnectionException $exception) {
            report($exception);

            return response()->json([
                'status' => 'unavailable',
                'freshness' => 'unknown',
                'source' => 'Alpha Vantage / Yahoo Finance',
                'message' => 'Penyedia data historis tidak dapat dihubungi.',
                'candles' => [],
            ], 503);
        }

        return response()->json($data, $data['status'] === 'available' ? 200 : 503);
    }
}
