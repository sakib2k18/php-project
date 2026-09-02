<?php

namespace App\Models;

use App\Models\Concerns\HasCoverImage;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuccessStory extends Model
{
    use HasCoverImage, HasFactory, HasSlug, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'beneficiary_name',
        'beneficiary_description',
        'story',
        'location',
        'story_date',
        'image',
        'is_published',
        'featured',
        'campaign_id',
    ];

    protected function casts(): array
    {
        return [
            'story_date' => 'date',
            'is_published' => 'boolean',
            'featured' => 'boolean',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('beneficiary_name', 'like', "%{$term}%")
                ->orWhere('location', 'like', "%{$term}%");
        });
    }
}
