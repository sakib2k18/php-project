<?php

namespace App\Http\Controllers;

use App\Http\Middleware\RememberVisitedCampaign;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CampaignController extends Controller
{
    /**
     * Public campaign index with search, category/status filters and sorting.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', Rule::in(array_keys(config('site.campaign_categories')))],
            'status' => ['nullable', Rule::in([Campaign::STATUS_ACTIVE, Campaign::STATUS_COMPLETED])],
            'sort' => ['nullable', Rule::in(['recent', 'ending', 'progress', 'target'])],
        ]);

        $sort = $filters['sort'] ?? 'recent';

        $campaigns = Campaign::query()
            ->published()
            ->search($filters['q'] ?? null)
            ->category($filters['category'] ?? null)
            ->status($filters['status'] ?? null)
            ->when($sort === 'recent', fn ($q) => $q->orderByDesc('featured')->orderByDesc('created_at'))
            ->when($sort === 'ending', fn ($q) => $q->whereNotNull('end_date')->orderBy('end_date'))
            ->when($sort === 'progress', fn ($q) => $q->orderByRaw('(raised_amount / NULLIF(target_amount,0)) DESC'))
            ->when($sort === 'target', fn ($q) => $q->orderByDesc('target_amount'))
            ->paginate(config('site.pagination.public'))
            ->withQueryString();

        return view('campaigns.index', [
            'campaigns' => $campaigns,
            'filters' => $filters + ['sort' => $sort],
            'categories' => config('site.campaign_categories'),
            'totalActive' => Campaign::query()->active()->count(),
        ]);
    }

    /**
     * Campaign detail page. Route model binding resolves the slug and the
     * `remember.campaign` middleware records the visit in a cookie.
     */
    public function show(Campaign $campaign, Request $request): View
    {
        // Drafts and cancelled campaigns stay invisible to the public; the
        // administrator can still preview them.
        abort_if(
            ! in_array($campaign->status, [Campaign::STATUS_ACTIVE, Campaign::STATUS_COMPLETED], true)
                && ! $request->user()?->isAdmin(),
            404
        );

        $campaign->loadMissing('updates');
        $campaign->incrementQuietly('views');

        $related = Campaign::query()
            ->published()
            ->where('id', '!=', $campaign->id)
            ->where(function ($q) use ($campaign) {
                $q->where('category', $campaign->category)
                    ->orWhere('location', $campaign->location);
            })
            ->orderByDesc('featured')
            ->limit(3)
            ->get();

        if ($related->count() < 3) {
            $related = $related->concat(
                Campaign::query()
                    ->published()
                    ->whereNotIn('id', $related->pluck('id')->push($campaign->id))
                    ->latest()
                    ->limit(3 - $related->count())
                    ->get()
            );
        }

        // Recent supporters, respecting the anonymity flag.
        $recentDonors = $campaign->approvedDonations()
            ->latest('donated_on')
            ->limit(5)
            ->get(['id', 'donor_name', 'amount', 'donated_on', 'is_anonymous', 'message']);

        return view('campaigns.show', [
            'campaign' => $campaign,
            'related' => $related,
            'recentDonors' => $recentDonors,
            'recentlyViewed' => $this->recentlyViewed($request, $campaign),
        ]);
    }

    /**
     * Campaigns the visitor looked at before, read from the cookie.
     */
    protected function recentlyViewed(Request $request, Campaign $current)
    {
        $slugs = collect(RememberVisitedCampaign::read($request))
            ->reject(fn ($slug) => $slug === $current->slug)
            ->take(3);

        if ($slugs->isEmpty()) {
            return collect();
        }

        return Campaign::query()
            ->published()
            ->whereIn('slug', $slugs)
            ->get()
            ->sortBy(fn ($c) => $slugs->search($c->slug))
            ->values();
    }
}
