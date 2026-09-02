<?php

namespace App\Models;

use App\Models\Concerns\HasCoverImage;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use HasCoverImage, HasFactory, HasSlug, SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'description',
        'category',
        'target_amount',
        'start_date',
        'end_date',
        'location',
        'latitude',
        'longitude',
        'status',
        'featured',
        'is_emergency',
        'cover_image',
        'beneficiaries_count',
        'created_by',
    ];

    /**
     * `raised_amount` is intentionally NOT fillable — it is only ever changed by
     * App\Services\DonationService inside a database transaction.
     */
    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'raised_amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'featured' => 'boolean',
            'is_emergency' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function imageColumn(): string
    {
        return 'cover_image';
    }

    // -----------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    public function approvedDonations(): HasMany
    {
        return $this->hasMany(Donation::class)->where('status', Donation::STATUS_APPROVED);
    }

    public function updates(): HasMany
    {
        return $this->hasMany(CampaignUpdate::class)->orderByDesc('published_on');
    }

    public function successStories(): HasMany
    {
        return $this->hasMany(SuccessStory::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // -----------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------

    /** Campaigns that may be shown on the public website. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_COMPLETED]);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function scopeEmergency(Builder $query): Builder
    {
        return $query->where('is_emergency', true)->where('status', self::STATUS_ACTIVE);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('short_description', 'like', "%{$term}%")
                ->orWhere('location', 'like', "%{$term}%");
        });
    }

    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        return blank($category) ? $query : $query->where('category', $category);
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return blank($status) ? $query : $query->where('status', $status);
    }

    // -----------------------------------------------------------------
    // Derived attributes
    // -----------------------------------------------------------------

    /** Progress is capped at 100% so an over-funded campaign never breaks the bar. */
    public function getProgressPercentAttribute(): float
    {
        $target = (float) $this->target_amount;

        if ($target <= 0) {
            return 0.0;
        }

        return round(min(100, ((float) $this->raised_amount / $target) * 100), 1);
    }

    public function getRawProgressPercentAttribute(): float
    {
        $target = (float) $this->target_amount;

        return $target <= 0 ? 0.0 : round(((float) $this->raised_amount / $target) * 100, 1);
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->target_amount - (float) $this->raised_amount);
    }

    public function getCategoryLabelAttribute(): string
    {
        return config("site.campaign_categories.{$this->category}", 'Other');
    }

    public function getStatusLabelAttribute(): string
    {
        return config("site.campaign_statuses.{$this->status}", ucfirst((string) $this->status));
    }

    public function getDaysLeftAttribute(): ?int
    {
        if (! $this->end_date) {
            return null;
        }

        return max(0, (int) now()->startOfDay()->diffInDays($this->end_date->startOfDay(), false));
    }

    public function getIsOpenAttribute(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && (! $this->end_date || $this->end_date->endOfDay()->isFuture());
    }

    public function getHasCoordinatesAttribute(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }
}
