<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCampaignRequest;
use App\Http\Requests\Admin\UpdateCampaignRequest;
use App\Models\Campaign;
use App\Services\ActivityLogger;
use App\Services\ImageUploadService;
use App\Services\StatisticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function __construct(
        protected ImageUploadService $images,
        protected ActivityLogger $activity,
        protected StatisticsService $statistics,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Campaign::class);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(array_keys(config('site.campaign_statuses')))],
            'category' => ['nullable', Rule::in(array_keys(config('site.campaign_categories')))],
            'trashed' => ['nullable', 'boolean'],
        ]);

        $campaigns = Campaign::query()
            ->when($request->boolean('trashed'), fn ($q) => $q->onlyTrashed())
            ->search($filters['q'] ?? null)
            ->status($filters['status'] ?? null)
            ->category($filters['category'] ?? null)
            ->withCount(['donations as approved_donations_count' => fn ($q) => $q->approved()])
            ->latest()
            ->paginate(config('site.pagination.admin'))
            ->withQueryString();

        return view('admin.campaigns.index', [
            'campaigns' => $campaigns,
            'filters' => $filters,
            'statuses' => config('site.campaign_statuses'),
            'categories' => config('site.campaign_categories'),
            'trashedCount' => Campaign::onlyTrashed()->count(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Campaign::class);

        return view('admin.campaigns.create', [
            'campaign' => new Campaign([
                'status' => Campaign::STATUS_DRAFT,
                'start_date' => now()->toDateString(),
            ]),
            'categories' => config('site.campaign_categories'),
            'statuses' => config('site.campaign_statuses'),
        ]);
    }

    public function store(StoreCampaignRequest $request): RedirectResponse
    {
        $campaign = new Campaign($request->safe()->except('cover_image'));
        $campaign->created_by = $request->user()->id;
        $campaign->cover_image = $this->images->store($request->file('cover_image'), 'campaigns');
        $campaign->save();

        $this->activity->created($campaign, "campaign \"{$campaign->title}\"");
        $this->statistics->flushPublicCache();

        return redirect()
            ->route('admin.campaigns.edit', $campaign)
            ->with('success', 'Campaign created successfully.');
    }

    public function show(Campaign $campaign): View
    {
        $this->authorize('view', $campaign);

        $campaign->load(['updates.author:id,name', 'creator:id,name']);

        return view('admin.campaigns.show', [
            'campaign' => $campaign,
            'donations' => $campaign->donations()
                ->with('user:id,name')
                ->latest('donated_on')
                ->limit(10)
                ->get(),
            'totals' => [
                'approved' => (float) $campaign->donations()->approved()->sum('amount'),
                'pending' => (float) $campaign->donations()->pending()->sum('amount'),
                'donors' => $campaign->donations()->approved()->distinct('donor_email')->count('donor_email'),
            ],
        ]);
    }

    public function edit(Campaign $campaign): View
    {
        $this->authorize('update', $campaign);

        return view('admin.campaigns.edit', [
            'campaign' => $campaign,
            'categories' => config('site.campaign_categories'),
            'statuses' => config('site.campaign_statuses'),
        ]);
    }

    public function update(UpdateCampaignRequest $request, Campaign $campaign): RedirectResponse
    {
        $campaign->fill($request->safe()->except('cover_image'));

        if ($request->hasFile('cover_image')) {
            $campaign->cover_image = $this->images->store(
                $request->file('cover_image'),
                'campaigns',
                $campaign->cover_image
            );
        }

        $campaign->save();

        $this->activity->updated($campaign, "campaign \"{$campaign->title}\"");
        $this->statistics->flushPublicCache();

        return redirect()
            ->route('admin.campaigns.edit', $campaign)
            ->with('success', 'Campaign updated successfully.');
    }

    /** Soft delete — donation history stays intact and the record is restorable. */
    public function destroy(Campaign $campaign): RedirectResponse
    {
        $this->authorize('delete', $campaign);

        $title = $campaign->title;
        $campaign->delete();

        $this->activity->log('campaign.deleted', "Deleted campaign \"{$title}\"");
        $this->statistics->flushPublicCache();

        return redirect()
            ->route('admin.campaigns.index')
            ->with('success', "Campaign \"{$title}\" moved to the archive.");
    }

    public function restore(int $id): RedirectResponse
    {
        $campaign = Campaign::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $campaign);

        $campaign->restore();

        $this->activity->log('campaign.restored', "Restored campaign \"{$campaign->title}\"", $campaign);
        $this->statistics->flushPublicCache();

        return redirect()
            ->route('admin.campaigns.index')
            ->with('success', 'Campaign restored.');
    }

    /** Quick status toggle from the index table. */
    public function toggleFeatured(Campaign $campaign): RedirectResponse
    {
        $this->authorize('update', $campaign);

        $campaign->forceFill(['featured' => ! $campaign->featured])->save();

        $this->activity->updated($campaign, ($campaign->featured ? 'featured' : 'unfeatured')." campaign \"{$campaign->title}\"");

        return back()->with('success', $campaign->featured
            ? 'Campaign is now featured on the homepage.'
            : 'Campaign removed from the featured list.');
    }
}
