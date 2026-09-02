<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps a short "recently viewed campaigns" list in a queued cookie.
 *
 * Demonstrates Laravel's cookie handling with safe attributes: HttpOnly so it
 * is unreadable from JavaScript, SameSite=Lax, and Secure whenever the request
 * arrived over HTTPS.
 */
class RememberVisitedCampaign
{
    public const COOKIE = 'kt_recent_campaigns';

    public const MAX_ITEMS = 4;

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $campaign = $request->route('campaign');

        if ($campaign && $campaign->getKey()) {
            $slugs = static::read($request);

            // Most recent first, no duplicates, capped length.
            $slugs = array_values(array_unique(array_merge([$campaign->slug], $slugs)));
            $slugs = array_slice($slugs, 0, self::MAX_ITEMS);

            Cookie::queue(cookie(
                name: self::COOKIE,
                value: json_encode($slugs, JSON_THROW_ON_ERROR),
                minutes: 60 * 24 * 30,
                path: '/',
                domain: null,
                secure: $request->isSecure(),
                httpOnly: true,
                raw: false,
                sameSite: 'lax',
            ));
        }

        return $response;
    }

    /**
     * @return array<int, string>
     */
    public static function read(Request $request): array
    {
        $raw = $request->cookie(self::COOKIE);

        if (blank($raw)) {
            return [];
        }

        $decoded = json_decode((string) $raw, true);

        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_filter($decoded, 'is_string'));
    }
}
