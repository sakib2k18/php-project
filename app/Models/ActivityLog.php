<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'ip_address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAction(Builder $query, ?string $action): Builder
    {
        return blank($action) ? $query : $query->where('action', $action);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('description', 'like', "%{$term}%")
                ->orWhere('action', 'like', "%{$term}%");
        });
    }

    /** Tailwind-friendly accent used by the activity feed. */
    public function getToneAttribute(): string
    {
        return match (true) {
            str_contains($this->action, 'deleted'), str_contains($this->action, 'rejected') => 'rose',
            str_contains($this->action, 'approved'), str_contains($this->action, 'created') => 'brand',
            str_contains($this->action, 'updated') => 'accent',
            default => 'ink',
        };
    }
}
