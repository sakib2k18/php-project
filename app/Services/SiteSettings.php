<?php

namespace App\Services;

use App\Models\OrganizationSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Single source of truth for the organisation identity.
 *
 * Values come from the `organization_settings` table and fall back to
 * config/site.php, so the site keeps rendering even before the seeder has run
 * (or if the database is momentarily unavailable during a first install).
 */
class SiteSettings
{
    public const CACHE_KEY = 'organization.settings';

    /** @var array<string, string|null>|null */
    protected ?array $values = null;

    /**
     * @return array<string, string|null>
     */
    public function all(): array
    {
        if ($this->values !== null) {
            return $this->values;
        }

        $this->values = Cache::rememberForever(self::CACHE_KEY, function (): array {
            try {
                return OrganizationSetting::query()->pluck('value', 'key')->all();
            } catch (Throwable) {
                // Database not migrated yet — fall back to config defaults.
                return [];
            }
        });

        return $this->values;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->all()[$key] ?? null;

        return filled($value) ? $value : ($default ?? $this->configDefault($key));
    }

    public function bool(string $key, bool $default = false): bool
    {
        $value = $this->get($key);

        return $value === null ? $default : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /** Public URL for an uploaded setting image (logo / favicon), or null. */
    public function imageUrl(string $key): ?string
    {
        $path = $this->get($key);

        if (blank($path)) {
            return null;
        }

        return Storage::disk(config('site.uploads.disk'))->url($path);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function put(array $values): void
    {
        foreach ($values as $key => $value) {
            OrganizationSetting::query()->where('key', $key)->update(['value' => $value]);
        }

        $this->flush();
    }

    public function flush(): void
    {
        $this->values = null;
        Cache::forget(self::CACHE_KEY);
    }

    // -----------------------------------------------------------------
    // Convenience accessors used all over the Blade layer
    // -----------------------------------------------------------------

    public function name(): string
    {
        return (string) $this->get('org_name', config('site.organization.name'));
    }

    public function tagline(): string
    {
        return (string) $this->get('tagline', config('site.organization.tagline'));
    }

    public function email(): string
    {
        return (string) $this->get('email', config('site.contact.email'));
    }

    public function phone(): string
    {
        return (string) $this->get('phone', config('site.contact.phone'));
    }

    public function address(): string
    {
        return (string) $this->get('address', config('site.contact.address'));
    }

    public function latitude(): float
    {
        return (float) $this->get('latitude', config('site.location.latitude'));
    }

    public function longitude(): float
    {
        return (float) $this->get('longitude', config('site.location.longitude'));
    }

    public function currencySymbol(): string
    {
        return (string) config('site.currency.symbol');
    }

    /**
     * Social links that are actually filled in.
     *
     * @return array<string, string>
     */
    public function socialLinks(): array
    {
        $links = [
            'facebook' => $this->get('facebook_url', config('site.social.facebook')),
            'instagram' => $this->get('instagram_url', config('site.social.instagram')),
            'youtube' => $this->get('youtube_url', config('site.social.youtube')),
            'linkedin' => $this->get('linkedin_url', config('site.social.linkedin')),
        ];

        return array_filter($links, fn ($url) => filled($url));
    }

    /**
     * Map a settings key onto its config/site.php fallback.
     */
    protected function configDefault(string $key): mixed
    {
        return match ($key) {
            'org_name' => config('site.organization.name'),
            'tagline' => config('site.organization.tagline'),
            'email' => config('site.contact.email'),
            'phone' => config('site.contact.phone'),
            'emergency_contact' => config('site.contact.emergency_contact'),
            'address' => config('site.contact.address'),
            'office_hours' => config('site.contact.office_hours'),
            'facebook_url' => config('site.social.facebook'),
            'instagram_url' => config('site.social.instagram'),
            'youtube_url' => config('site.social.youtube'),
            'linkedin_url' => config('site.social.linkedin'),
            'latitude' => config('site.location.latitude'),
            'longitude' => config('site.location.longitude'),
            default => null,
        };
    }
}
