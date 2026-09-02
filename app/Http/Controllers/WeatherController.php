<?php

namespace App\Http\Controllers;

use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Thin server-side proxy for the weather widget's "refresh" button.
 *
 * The browser never talks to Open-Meteo directly: the request is made by
 * Laravel's HTTP client, cached, and only the small transformed payload is
 * returned. That keeps the provider swappable and the request volume bounded.
 */
class WeatherController extends Controller
{
    public function __construct(protected WeatherService $weather) {}

    public function __invoke(Request $request): JsonResponse
    {
        if ($request->boolean('refresh')) {
            $this->weather->forget();
        }

        $data = $this->weather->current();

        if ($data === null) {
            return response()->json([
                'ok' => false,
                'message' => 'Weather data is temporarily unavailable.',
            ], 200);
        }

        return response()->json([
            'ok' => true,
            'data' => $data,
        ]);
    }
}
