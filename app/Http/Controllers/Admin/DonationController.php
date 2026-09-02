<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewDonationRequest;
use App\Models\Campaign;
use App\Models\Donation;
use App\Services\DonationService;
use App\Services\StatisticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DonationController extends Controller
{
    public function __construct(
        protected DonationService $donations,
        protected StatisticsService $statistics,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Donation::class);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(array_keys(config('site.donation_statuses')))],
            'method' => ['nullable', Rule::in(array_keys(config('site.donation_methods')))],
            'campaign_id' => ['nullable', 'integer', 'exists:campaigns,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $donations = Donation::query()
            ->with(['campaign:id,title,slug', 'user:id,name,email'])
            ->search($filters['q'] ?? null)
            ->status($filters['status'] ?? null)
            ->method($filters['method'] ?? null)
            ->when($filters['campaign_id'] ?? null, fn ($q, $id) => $q->where('campaign_id', $id))
            ->when($filters['from'] ?? null, fn ($q, $d) => $q->where('donated_on', '>=', $d))
            ->when($filters['to'] ?? null, fn ($q, $d) => $q->where('donated_on', '<=', $d))
            ->latest('donated_on')
            ->latest('id')
            ->paginate(config('site.pagination.admin'))
            ->withQueryString();

        return view('admin.donations.index', [
            'donations' => $donations,
            'filters' => $filters,
            'statuses' => config('site.donation_statuses'),
            'methods' => config('site.donation_methods'),
            'campaigns' => Campaign::query()->orderBy('title')->pluck('title', 'id'),
            'summary' => $this->statistics->adminOverview(),
        ]);
    }

    public function show(Donation $donation): View
    {
        $this->authorize('view', $donation);

        $donation->load(['campaign', 'user', 'reviewer:id,name']);

        return view('admin.donations.show', compact('donation'));
    }

    /**
     * Approve / reject / reopen. All the money handling lives in
     * DonationService inside a database transaction.
     */
    public function review(ReviewDonationRequest $request, Donation $donation): RedirectResponse
    {
        $admin = $request->user();
        $note = $request->validated('admin_note');
        $reference = $donation->reference;

        if ($request->validated('decision') === 'approve') {
            $this->donations->approve($donation, $admin, $note);
            $message = "Donation {$reference} approved. The campaign total has been updated.";
        } elseif ($request->validated('decision') === 'reject') {
            $this->donations->reject($donation, $admin, $note);
            $message = "Donation {$reference} rejected.";
        } else {
            $this->donations->markPending($donation, $admin);
            $message = "Donation {$reference} moved back to pending.";
        }

        $this->statistics->flushPublicCache();

        return back()->with('success', $message);
    }

    public function destroy(Donation $donation): RedirectResponse
    {
        // Deleting is admin-only; the policy's before() hook grants it.
        $this->authorize('review', $donation);

        $reference = $donation->reference;
        $this->donations->delete($donation);
        $this->statistics->flushPublicCache();

        return redirect()
            ->route('admin.donations.index')
            ->with('success', "Donation {$reference} deleted and campaign totals adjusted.");
    }

    /** Rebuild every campaign total from the approved donations on record. */
    public function recalculate(): RedirectResponse
    {
        $count = $this->donations->recalculateCampaignTotals();
        $this->statistics->flushPublicCache();

        return back()->with('success', "Recalculated raised amounts for {$count} campaigns.");
    }
}
