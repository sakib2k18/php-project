<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDonationRequest;
use App\Models\Campaign;
use App\Models\Donation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DonationController extends Controller
{
    /**
     * A supporter's own donation history. The policy allows listing, and the
     * query is *always* constrained to the signed-in user — another person's
     * records are never reachable from here.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Donation::class);

        $filters = $request->validate([
            'status' => ['nullable', Rule::in(array_keys(config('site.donation_statuses')))],
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $donations = Donation::query()
            ->where('user_id', $request->user()->id)
            ->status($filters['status'] ?? null)
            ->search($filters['q'] ?? null)
            ->with('campaign:id,title,slug')
            ->latest('donated_on')
            ->latest('id')
            ->paginate(config('site.pagination.admin'))
            ->withQueryString();

        $summary = Donation::query()
            ->where('user_id', $request->user()->id)
            ->selectRaw('status, COUNT(*) as c, COALESCE(SUM(amount),0) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return view('dashboard.donations.index', [
            'donations' => $donations,
            'filters' => $filters,
            'statuses' => config('site.donation_statuses'),
            'summary' => [
                'approved_amount' => (float) ($summary[Donation::STATUS_APPROVED]->total ?? 0),
                'pending_amount' => (float) ($summary[Donation::STATUS_PENDING]->total ?? 0),
                'count' => (int) $summary->sum('c'),
            ],
        ]);
    }

    /** Donation form, optionally pre-selected for a campaign. */
    public function create(Request $request): View
    {
        $this->authorize('create', Donation::class);

        $campaigns = Campaign::query()
            ->active()
            ->orderByDesc('is_emergency')
            ->orderByDesc('featured')
            ->orderBy('title')
            ->get(['id', 'title', 'category', 'target_amount', 'raised_amount']);

        $selected = null;

        if ($request->filled('campaign')) {
            $selected = Campaign::query()
                ->active()
                ->where('slug', $request->string('campaign'))
                ->first();
        }

        return view('dashboard.donations.create', [
            'campaigns' => $campaigns,
            'selected' => $selected,
            'methods' => config('site.donation_methods'),
        ]);
    }

    /**
     * Record a donation. The record is always created as PENDING and owned by
     * the signed-in user; status and campaign totals are the administrator's
     * responsibility alone.
     */
    public function store(StoreDonationRequest $request): RedirectResponse
    {
        $donation = new Donation($request->safe()->only([
            'campaign_id', 'donor_name', 'donor_email', 'donor_phone',
            'amount', 'method', 'transaction_reference', 'is_anonymous',
            'message', 'donated_on',
        ]));

        $donation->user_id = $request->user()->id;   // ownership is server-side
        $donation->reference = Donation::nextReference();
        $donation->status = Donation::STATUS_PENDING; // never taken from input
        $donation->save();

        return redirect()
            ->route('donations.show', $donation)
            ->with('success', "Thank you! Donation {$donation->reference} has been recorded and is awaiting verification.");
    }

    public function show(Donation $donation): View
    {
        $this->authorize('view', $donation);

        $donation->loadMissing('campaign', 'reviewer');

        return view('dashboard.donations.show', compact('donation'));
    }

    /** Printable receipt — only available once the donation is approved. */
    public function receipt(Donation $donation): View
    {
        $this->authorize('downloadReceipt', $donation);

        $donation->loadMissing('campaign');

        return view('dashboard.donations.receipt', compact('donation'));
    }
}
