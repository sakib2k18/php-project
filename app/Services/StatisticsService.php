<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\ContactMessage;
use App\Models\Donation;
use App\Models\Event;
use App\Models\GalleryItem;
use App\Models\Project;
use App\Models\SuccessStory;
use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Every number rendered on the public homepage and the admin dashboard is
 * calculated here from MySQL — nothing is hardcoded.
 */
class StatisticsService
{
    /**
     * Headline impact numbers for the public homepage (cached briefly, since
     * they are shown on the most visited page).
     *
     * @return array<string, int|float>
     */
    public function publicImpact(): array
    {
        return Cache::remember('stats.public_impact', now()->addMinutes(10), function (): array {
            $beneficiaries = (int) Campaign::query()->published()->sum('beneficiaries_count')
                + (int) Project::query()->published()->sum('beneficiaries_count');

            return [
                'people_helped' => $beneficiaries,
                'campaigns_completed' => Campaign::query()->completed()->count(),
                'funds_raised' => (float) Donation::query()->approved()->sum('amount'),
                'volunteers' => Volunteer::query()->approved()->count(),
                'projects_delivered' => Project::query()->published()->where('status', Project::STATUS_COMPLETED)->count(),
                'active_campaigns' => Campaign::query()->active()->count(),
            ];
        });
    }

    /**
     * Full admin dashboard payload.
     *
     * @return array<string, mixed>
     */
    public function adminOverview(): array
    {
        $donationTotals = Donation::query()
            ->selectRaw('status, COUNT(*) as c, COALESCE(SUM(amount), 0) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $totalFor = fn (string $status) => (float) ($donationTotals[$status]->total ?? 0);
        $countFor = fn (string $status) => (int) ($donationTotals[$status]->c ?? 0);

        return [
            'users_total' => User::query()->count(),
            'users_members' => User::query()->members()->count(),
            'users_new_this_month' => User::query()->where('created_at', '>=', now()->startOfMonth())->count(),

            'campaigns_total' => Campaign::query()->count(),
            'campaigns_active' => Campaign::query()->active()->count(),
            'campaigns_completed' => Campaign::query()->completed()->count(),
            'campaigns_draft' => Campaign::query()->where('status', Campaign::STATUS_DRAFT)->count(),

            'donations_total' => array_sum([
                $countFor(Donation::STATUS_PENDING),
                $countFor(Donation::STATUS_APPROVED),
                $countFor(Donation::STATUS_REJECTED),
            ]),
            'donations_pending' => $countFor(Donation::STATUS_PENDING),
            'donations_approved' => $countFor(Donation::STATUS_APPROVED),
            'donations_rejected' => $countFor(Donation::STATUS_REJECTED),
            'amount_approved' => $totalFor(Donation::STATUS_APPROVED),
            'amount_pending' => $totalFor(Donation::STATUS_PENDING),
            'amount_rejected' => $totalFor(Donation::STATUS_REJECTED),
            'amount_this_month' => (float) Donation::query()->approved()
                ->where('donated_on', '>=', now()->startOfMonth()->toDateString())
                ->sum('amount'),

            'volunteers_total' => Volunteer::query()->count(),
            'volunteers_pending' => Volunteer::query()->pending()->count(),
            'volunteers_approved' => Volunteer::query()->approved()->count(),

            'events_upcoming' => Event::query()->published()->upcoming()->count(),
            'events_total' => Event::query()->count(),

            'projects_total' => Project::query()->count(),
            'stories_total' => SuccessStory::query()->count(),
            'gallery_total' => GalleryItem::query()->count(),

            'messages_unread' => ContactMessage::query()->unread()->count(),
            'messages_total' => ContactMessage::query()->count(),
        ];
    }

    /**
     * Approved donation amount per month for the last N months.
     *
     * @return array{labels: array<int, string>, amounts: array<int, float>, counts: array<int, int>}
     */
    public function donationsByMonth(int $months = 6): array
    {
        $start = now()->startOfMonth()->subMonths($months - 1);

        $rows = Donation::query()
            ->approved()
            ->where('donated_on', '>=', $start->toDateString())
            ->selectRaw("DATE_FORMAT(donated_on, '%Y-%m') as period, COALESCE(SUM(amount),0) as total, COUNT(*) as c")
            ->groupBy('period')
            ->pluck('total', 'period');

        $counts = Donation::query()
            ->approved()
            ->where('donated_on', '>=', $start->toDateString())
            ->selectRaw("DATE_FORMAT(donated_on, '%Y-%m') as period, COUNT(*) as c")
            ->groupBy('period')
            ->pluck('c', 'period');

        $labels = [];
        $amounts = [];
        $countSeries = [];

        for ($i = 0; $i < $months; $i++) {
            $month = (clone $start)->addMonths($i);
            $key = $month->format('Y-m');

            $labels[] = $month->format('M Y');
            $amounts[] = (float) ($rows[$key] ?? 0);
            $countSeries[] = (int) ($counts[$key] ?? 0);
        }

        return ['labels' => $labels, 'amounts' => $amounts, 'counts' => $countSeries];
    }

    /**
     * New user sign-ups per month for the last N months.
     *
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    public function userGrowth(int $months = 6): array
    {
        $start = now()->startOfMonth()->subMonths($months - 1);

        $rows = User::query()
            ->where('created_at', '>=', $start)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period, COUNT(*) as c")
            ->groupBy('period')
            ->pluck('c', 'period');

        $labels = [];
        $values = [];

        for ($i = 0; $i < $months; $i++) {
            $month = (clone $start)->addMonths($i);
            $labels[] = $month->format('M');
            $values[] = (int) ($rows[$month->format('Y-m')] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Approved donation total grouped by campaign category.
     *
     * @return array{labels: array<int, string>, values: array<int, float>}
     */
    public function donationsByCategory(): array
    {
        $rows = Donation::query()
            ->approved()
            ->join('campaigns', 'campaigns.id', '=', 'donations.campaign_id')
            ->selectRaw('campaigns.category as category, COALESCE(SUM(donations.amount),0) as total')
            ->groupBy('campaigns.category')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $rows->map(fn ($r) => config("site.campaign_categories.{$r->category}", 'Other'))->all(),
            'values' => $rows->map(fn ($r) => (float) $r->total)->all(),
        ];
    }

    /**
     * Donation count by status — feeds the doughnut chart.
     *
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    public function donationStatusBreakdown(): array
    {
        $rows = Donation::query()
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $labels = [];
        $values = [];

        foreach (config('site.donation_statuses') as $key => $label) {
            $labels[] = $label;
            $values[] = (int) ($rows[$key] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Campaigns ranked by approved donation total.
     *
     * @return Collection<int, object>
     */
    public function topCampaigns(int $limit = 5)
    {
        return Campaign::query()
            ->select('campaigns.*')
            ->selectSub(
                Donation::query()
                    ->approved()
                    ->whereColumn('donations.campaign_id', 'campaigns.id')
                    ->selectRaw('COALESCE(SUM(amount),0)'),
                'approved_total'
            )
            ->selectSub(
                Donation::query()
                    ->approved()
                    ->whereColumn('donations.campaign_id', 'campaigns.id')
                    ->selectRaw('COUNT(*)'),
                'approved_count'
            )
            ->orderByDesc('approved_total')
            ->limit($limit)
            ->get();
    }

    /**
     * Donation totals grouped by campaign, for the reports table.
     */
    public function donationsPerCampaign()
    {
        return DB::table('campaigns')
            ->leftJoin('donations', function ($join) {
                $join->on('donations.campaign_id', '=', 'campaigns.id')
                    ->where('donations.status', '=', Donation::STATUS_APPROVED);
            })
            ->whereNull('campaigns.deleted_at')
            ->groupBy('campaigns.id', 'campaigns.title', 'campaigns.target_amount', 'campaigns.status')
            ->select(
                'campaigns.id',
                'campaigns.title',
                'campaigns.target_amount',
                'campaigns.status',
                DB::raw('COUNT(donations.id) as donations_count'),
                DB::raw('COALESCE(SUM(donations.amount), 0) as approved_total')
            )
            ->orderByDesc('approved_total')
            ->get();
    }

    /** Donation totals for an explicit date range, used by the report filters. */
    public function rangeSummary(?Carbon $from, ?Carbon $to): array
    {
        $query = Donation::query()
            ->when($from, fn ($q) => $q->where('donated_on', '>=', $from->toDateString()))
            ->when($to, fn ($q) => $q->where('donated_on', '<=', $to->toDateString()));

        $rows = (clone $query)
            ->selectRaw('status, COUNT(*) as c, COALESCE(SUM(amount),0) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return [
            'count' => (int) (clone $query)->count(),
            'approved_amount' => (float) ($rows[Donation::STATUS_APPROVED]->total ?? 0),
            'pending_amount' => (float) ($rows[Donation::STATUS_PENDING]->total ?? 0),
            'rejected_amount' => (float) ($rows[Donation::STATUS_REJECTED]->total ?? 0),
            'approved_count' => (int) ($rows[Donation::STATUS_APPROVED]->c ?? 0),
            'pending_count' => (int) ($rows[Donation::STATUS_PENDING]->c ?? 0),
            'rejected_count' => (int) ($rows[Donation::STATUS_REJECTED]->c ?? 0),
        ];
    }

    public function flushPublicCache(): void
    {
        Cache::forget('stats.public_impact');
    }
}
