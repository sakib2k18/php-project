<?php

namespace App\Models;

use App\Models\Concerns\HasCoverImage;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasCoverImage, HasFactory, HasSlug, SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'event_date',
        'start_time',
        'end_time',
        'location',
        'latitude',
        'longitude',
        'organizer',
        'image',
        'status',
        'capacity',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('event_date', '>=', now()->toDateString());
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->where('event_date', '<', now()->toDateString());
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('location', 'like', "%{$term}%")
                ->orWhere('organizer', 'like', "%{$term}%");
        });
    }

    public function getStatusLabelAttribute(): string
    {
        return config("site.event_statuses.{$this->status}", ucfirst((string) $this->status));
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->event_date && $this->event_date->gte(now()->startOfDay());
    }

    public function getTimeRangeAttribute(): ?string
    {
        if (blank($this->start_time)) {
            return null;
        }

        $format = fn (string $t): string => date('g:i A', strtotime($t));

        return blank($this->end_time)
            ? $format($this->start_time)
            : $format($this->start_time).' – '.$format($this->end_time);
    }

    public function getHasCoordinatesAttribute(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }
}
