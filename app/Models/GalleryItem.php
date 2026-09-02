<?php

namespace App\Models;

use App\Models\Concerns\HasCoverImage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GalleryItem extends Model
{
    use HasCoverImage, HasFactory;

    protected $fillable = [
        'title',
        'caption',
        'category',
        'image',
        'taken_on',
        'sort_order',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'taken_on' => 'date',
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        return blank($category) ? $query : $query->where('category', $category);
    }

    public function getCategoryLabelAttribute(): string
    {
        return config("site.gallery_categories.{$this->category}", 'Gallery');
    }
}
