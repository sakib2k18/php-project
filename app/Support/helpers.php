<?php

use App\Services\SiteSettings;

if (! function_exists('settings')) {
    /**
     * Resolve the organisation settings service (or a single setting).
     */
    function settings(?string $key = null, mixed $default = null): mixed
    {
        /** @var SiteSettings $service */
        $service = app(SiteSettings::class);

        return $key === null ? $service : $service->get($key, $default);
    }
}

if (! function_exists('money')) {
    /**
     * Format an amount with the configured currency symbol, e.g. "Tk 1,250".
     */
    function money(float|int|string|null $amount, bool $withSymbol = true): string
    {
        $decimals = (int) config('site.currency.decimals', 0);
        $formatted = number_format((float) $amount, $decimals);

        return $withSymbol
            ? config('site.currency.symbol', 'Tk').' '.$formatted
            : $formatted;
    }
}

if (! function_exists('compact_number')) {
    /**
     * Short form for headline statistics: 1.2K, 3.4M …
     */
    function compact_number(float|int|null $value): string
    {
        $value = (float) $value;

        return match (true) {
            abs($value) >= 1_000_000_000 => rtrim(rtrim(number_format($value / 1_000_000_000, 1), '0'), '.').'B',
            abs($value) >= 1_000_000 => rtrim(rtrim(number_format($value / 1_000_000, 1), '0'), '.').'M',
            abs($value) >= 1_000 => rtrim(rtrim(number_format($value / 1_000, 1), '0'), '.').'K',
            default => number_format($value),
        };
    }
}

if (! function_exists('active_class')) {
    /**
     * Return $class when the current route matches any of the given patterns.
     */
    function active_class(array|string $patterns, string $class = 'is-active', string $inactive = ''): string
    {
        return request()->routeIs(...(array) $patterns) ? $class : $inactive;
    }
}
