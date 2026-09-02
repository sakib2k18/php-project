<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    /**
     * `status`, `reviewed_by`, `reviewed_at` and `counted_in_campaign` are NOT
     * mass assignable: only App\Services\DonationService may change them, and
     * only for an administrator.
     */
    protected $fillable = [
        'reference',
        'user_id',
        'campaign_id',
        'donor_name',
        'donor_email',
        'donor_phone',
        'amount',
        'method',
        'transaction_reference',
        'is_anonymous',
        'message',
        'donated_on',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'donated_on' => 'date',
            'reviewed_at' => 'datetime',
            'is_anonymous' => 'boolean',
            'counted_in_campaign' => 'boolean',
        ];
    }

    // -----------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // -----------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------

    /*
     * The status scopes qualify the column name (`donations.status`) so they
     * stay unambiguous when the query joins another table that also has a
     * `status` column — campaigns, for instance, in the reporting aggregates.
     */

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where($query->qualifyColumn('status'), self::STATUS_APPROVED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where($query->qualifyColumn('status'), self::STATUS_PENDING);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where($query->qualifyColumn('status'), self::STATUS_REJECTED);
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return blank($status) ? $query : $query->where($query->qualifyColumn('status'), $status);
    }

    public function scopeMethod(Builder $query, ?string $method): Builder
    {
        return blank($method) ? $query : $query->where($query->qualifyColumn('method'), $method);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('reference', 'like', "%{$term}%")
                ->orWhere('donor_name', 'like', "%{$term}%")
                ->orWhere('donor_email', 'like', "%{$term}%")
                ->orWhere('transaction_reference', 'like', "%{$term}%");
        });
    }

    // -----------------------------------------------------------------
    // Derived attributes
    // -----------------------------------------------------------------

    public function getStatusLabelAttribute(): string
    {
        return config("site.donation_statuses.{$this->status}", ucfirst((string) $this->status));
    }

    public function getMethodLabelAttribute(): string
    {
        return config("site.donation_methods.{$this->method}", ucfirst((string) $this->method));
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /** Name shown publicly — respects the donor's anonymity choice. */
    public function getPublicDonorNameAttribute(): string
    {
        return $this->is_anonymous ? 'Anonymous Donor' : $this->donor_name;
    }

    /**
     * Build the next human-readable reference, e.g. KT-2026-000042.
     */
    public static function nextReference(): string
    {
        $year = now()->format('Y');
        $prefix = 'KT-'.$year.'-';

        $last = static::query()
            ->where('reference', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('reference');

        $sequence = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }
}
