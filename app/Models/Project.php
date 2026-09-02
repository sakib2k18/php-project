<?php

namespace App\Models;

use App\Models\Concerns\HasCoverImage;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasCoverImage, HasFactory, HasSlug, SoftDeletes;

    public const STATUS_PLANNED = 'planned';

    public const STATUS_ONGOING = 'ongoing';

    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'description',
        'category',
        'location',
        'latitude',
        'longitude',
        'start_date',
        'end_date',
        'status',
        'image',
        'featured',
        'is_published',
        'beneficiaries_count',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'featured' => 'boolean',
            'is_published' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return blank($status) ? $query : $query->where('status', $status);
    }

    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        return blank($category) ? $query : $query->where('category', $category);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('summary', 'like', "%{$term}%")
                ->orWhere('location', 'like', "%{$term}%");
        });
    }

    public function getCategoryLabelAttribute(): string
    {
        return config("site.campaign_categories.{$this->category}", 'Other');
    }

    public function getStatusLabelAttribute(): string
    {
        return config("site.project_statuses.{$this->status}", ucfirst((string) $this->status));
    }

    public function getHasCoordinatesAttribute(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }
}
