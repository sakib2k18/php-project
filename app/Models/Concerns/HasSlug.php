<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Generates a unique, URL-safe slug from a source column and keeps the model
 * resolvable by that slug in route model binding.
 */
trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::saving(function ($model): void {
            $source = $model->slugSourceColumn();

            if (blank($model->slug) && filled($model->{$source})) {
                $model->slug = $model->generateUniqueSlug($model->{$source});
            }
        });
    }

    public function slugSourceColumn(): string
    {
        return 'title';
    }

    public function generateUniqueSlug(string $value): string
    {
        $base = Str::slug($value);

        if ($base === '') {
            $base = 'item';
        }

        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->when(method_exists(static::class, 'bootSoftDeletes'), fn ($q) => $q->withTrashed())
            ->where('slug', $slug)
            ->where($this->getKeyName(), '!=', $this->getKey())
            ->exists()
        ) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
