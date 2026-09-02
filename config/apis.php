<?php

/*
|--------------------------------------------------------------------------
| External API configuration
|--------------------------------------------------------------------------
|
| All integrations used by KUET TRY are keyless, free and open. Providers are
| configurable so they can be swapped later without touching application code.
|
*/

return [

    'weather' => [
        'enabled' => (bool) env('WEATHER_ENABLED', true),
        'provider' => 'open-meteo',
        'url' => env('WEATHER_API_URL', 'https://api.open-meteo.com/v1/forecast'),
        'timezone' => env('WEATHER_TIMEZONE', 'Asia/Dhaka'),
        'cache_minutes' => (int) env('WEATHER_CACHE_MINUTES', 30),
        'timeout' => (int) env('WEATHER_TIMEOUT', 6),
        'retries' => 1,
        'attribution' => 'Weather data by Open-Meteo.com',
    ],

    'map' => [
        'enabled' => (bool) env('MAP_ENABLED', true),
        'provider' => env('MAP_PROVIDER', 'openstreetmap'),
        'tile_url' => env('MAP_TILE_URL', 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'),
        'attribution' => env('MAP_ATTRIBUTION', '&copy; OpenStreetMap contributors'),
        'default_zoom' => (int) env('MAP_DEFAULT_ZOOM', 14),
        'max_zoom' => (int) env('MAP_MAX_ZOOM', 19),
    ],

    /*
     * Nominatim geocoding is OFF by default: coordinates are stored in the
     * database instead, which respects the Nominatim usage policy (no bulk
     * lookups, no autocomplete, no geocoding on every page request).
     */
    'geocoder' => [
        'enabled' => (bool) env('GEOCODER_ENABLED', false),
        'url' => env('GEOCODER_URL', 'https://nominatim.openstreetmap.org/search'),
        'user_agent' => env('GEOCODER_USER_AGENT', 'KUET-TRY-University-Project/1.0'),
        'cache_days' => (int) env('GEOCODER_CACHE_DAYS', 30),
        'timeout' => 8,
        // Nominatim asks for a maximum of one request per second.
        'min_interval_seconds' => 1,
    ],
];
