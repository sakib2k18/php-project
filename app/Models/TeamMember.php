<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'position',
        'biography',
        'photo',
        'email',
        'facebook_url',
        'linkedin_url',
        'twitter_url',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? Storage::disk(config('site.uploads.disk'))->url($this->photo) : null;
    }

    public function getInitialsAttribute(): string
    {
        $parts = array_values(array_filter(preg_split('/\s+/', trim((string) $this->name)) ?: []));

        if ($parts === []) {
            return '?';
        }

        return mb_strtoupper(mb_substr($parts[0], 0, 1).(count($parts) > 1 ? mb_substr(end($parts), 0, 1) : ''));
    }
}
