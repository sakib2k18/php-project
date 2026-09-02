<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Open-Meteo integration (free, keyless).
 *
 * Responses are cached so a page view never triggers an outbound request more
 * often than WEATHER_CACHE_MINUTES, and every failure degrades to null so the
 * page keeps rendering with a friendly "temporarily unavailable" panel.
 */
class WeatherService
{
    public function __construct(
        protected SiteSettings $settings,
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function current(?float $latitude = null, ?float $longitude = null): ?array
    {
        if (! config('apis.weather.enabled')) {
            return null;
        }

        $latitude ??= $this->settings->latitude();
        $longitude ??= $this->settings->longitude();

        $key = sprintf('weather:%s:%s', round($latitude, 3), round($longitude, 3));

        return Cache::remember(
            $key,
            now()->addMinutes((int) config('apis.weather.cache_minutes', 30)),
            fn () => $this->fetch($latitude, $longitude)
        );
    }

    /** Drop the cached payload so the next request re-fetches. */
    public function forget(?float $latitude = null, ?float $longitude = null): void
    {
        $latitude ??= $this->settings->latitude();
        $longitude ??= $this->settings->longitude();

        Cache::forget(sprintf('weather:%s:%s', round($latitude, 3), round($longitude, 3)));
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function fetch(float $latitude, float $longitude): ?array
    {
        try {
            $response = Http::timeout((int) config('apis.weather.timeout', 6))
                ->retry((int) config('apis.weather.retries', 1), 250, throw: false)
                ->acceptJson()
                ->get(config('apis.weather.url'), [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,weather_code,wind_speed_10m',
                    'daily' => 'weather_code,temperature_2m_max,temperature_2m_min',
                    'timezone' => config('apis.weather.timezone', 'auto'),
                    'forecast_days' => 4,
                ]);

            if ($response->failed()) {
                Log::warning('Open-Meteo request failed', ['status' => $response->status()]);

                return null;
            }

            return $this->transform($response->json());
        } catch (Throwable $e) {
            // Never surface a cURL/transport error to a visitor.
            Log::warning('Open-Meteo request threw an exception', ['message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Reshape the provider payload into the small, stable structure the Blade
     * views consume. Only what the UI needs leaves the server.
     *
     * @param  array<string, mixed>|null  $payload
     * @return array<string, mixed>|null
     */
    protected function transform(?array $payload): ?array
    {
        $current = $payload['current'] ?? null;

        if (! is_array($current) || ! isset($current['temperature_2m'])) {
            return null;
        }

        $daily = [];
        $dailyPayload = $payload['daily'] ?? [];

        foreach ($dailyPayload['time'] ?? [] as $index => $date) {
            if ($index === 0) {
                continue; // today is already covered by the "current" block
            }

            $daily[] = [
                'date' => $date,
                'label' => date('D', strtotime($date)),
                'code' => (int) ($dailyPayload['weather_code'][$index] ?? 0),
                'condition' => $this->describe((int) ($dailyPayload['weather_code'][$index] ?? 0)),
                'icon' => $this->icon((int) ($dailyPayload['weather_code'][$index] ?? 0)),
                'max' => round((float) ($dailyPayload['temperature_2m_max'][$index] ?? 0)),
                'min' => round((float) ($dailyPayload['temperature_2m_min'][$index] ?? 0)),
            ];
        }

        $code = (int) ($current['weather_code'] ?? 0);

        return [
            'temperature' => round((float) $current['temperature_2m']),
            'feels_like' => round((float) ($current['apparent_temperature'] ?? $current['temperature_2m'])),
            'humidity' => (int) ($current['relative_humidity_2m'] ?? 0),
            'wind_speed' => round((float) ($current['wind_speed_10m'] ?? 0), 1),
            'precipitation' => (float) ($current['precipitation'] ?? 0),
            'code' => $code,
            'condition' => $this->describe($code),
            'icon' => $this->icon($code),
            'daily' => array_slice($daily, 0, 3),
            'units' => [
                'temperature' => '°C',
                'wind' => 'km/h',
            ],
            'attribution' => config('apis.weather.attribution'),
            'fetched_at' => now()->toIso8601String(),
        ];
    }

    /** WMO weather interpretation codes → human wording. */
    protected function describe(int $code): string
    {
        return match (true) {
            $code === 0 => 'Clear sky',
            in_array($code, [1, 2], true) => 'Partly cloudy',
            $code === 3 => 'Overcast',
            in_array($code, [45, 48], true) => 'Foggy',
            in_array($code, [51, 53, 55, 56, 57], true) => 'Drizzle',
            in_array($code, [61, 63, 65, 66, 67], true) => 'Rain',
            in_array($code, [71, 73, 75, 77], true) => 'Snow',
            in_array($code, [80, 81, 82], true) => 'Rain showers',
            in_array($code, [85, 86], true) => 'Snow showers',
            in_array($code, [95, 96, 99], true) => 'Thunderstorm',
            default => 'Unsettled',
        };
    }

    /** Icon key consumed by the <x-ui.weather-icon> component. */
    protected function icon(int $code): string
    {
        return match (true) {
            $code === 0 => 'sun',
            in_array($code, [1, 2], true) => 'cloud-sun',
            $code === 3 => 'cloud',
            in_array($code, [45, 48], true) => 'fog',
            in_array($code, [51, 53, 55, 56, 57, 61, 63, 65, 66, 67, 80, 81, 82], true) => 'rain',
            in_array($code, [71, 73, 75, 77, 85, 86], true) => 'snow',
            in_array($code, [95, 96, 99], true) => 'storm',
            default => 'cloud',
        };
    }
}
