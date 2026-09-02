<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'title',
        'content',
        'priority',
        'status',
        'link_url',
        'link_label',
        'published_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /** Live announcements: published, already released and not yet expired. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->where(function (Builder $q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
    }

    public function scopePriority(Builder $query, ?string $priority): Builder
    {
        return blank($priority) ? $query : $query->where('priority', $priority);
    }

    public function getPriorityLabelAttribute(): string
    {
        return config("site.announcement_priorities.{$this->priority}", ucfirst((string) $this->priority));
    }

    public function getIsUrgentAttribute(): bool
    {
        return in_array($this->priority, ['high', 'urgent'], true);
    }
}
