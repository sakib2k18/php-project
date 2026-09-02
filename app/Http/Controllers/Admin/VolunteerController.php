<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Volunteer;
use App\Notifications\VolunteerReviewed;
use App\Services\ActivityLogger;
use App\Services\StatisticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VolunteerController extends Controller
{
    public function __construct(
        protected ActivityLogger $activity,
        protected StatisticsService $statistics,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Volunteer::class);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(array_keys(config('site.volunteer_statuses')))],
            'availability' => ['nullable', Rule::in(array_keys(config('site.volunteer_availability')))],
        ]);

        $counts = Volunteer::query()
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        return view('admin.volunteers.index', [
            'volunteers' => Volunteer::query()
                ->with('user:id,name,email')
                ->search($filters['q'] ?? null)
                ->status($filters['status'] ?? null)
                ->when($filters['availability'] ?? null, fn ($q, $a) => $q->where('availability', $a))
                ->latest()
                ->paginate(config('site.pagination.admin'))
                ->withQueryString(),
            'filters' => $filters,
            'statuses' => config('site.volunteer_statuses'),
            'availability' => config('site.volunteer_availability'),
            'counts' => [
                'pending' => (int) ($counts[Volunteer::STATUS_PENDING] ?? 0),
                'approved' => (int) ($counts[Volunteer::STATUS_APPROVED] ?? 0),
                'rejected' => (int) ($counts[Volunteer::STATUS_REJECTED] ?? 0),
            ],
        ]);
    }

    public function show(Volunteer $volunteer): View
    {
        $this->authorize('view', $volunteer);

        $volunteer->load(['user:id,name,email', 'reviewer:id,name']);

        return view('admin.volunteers.show', compact('volunteer'));
    }

    public function review(Request $request, Volunteer $volunteer): RedirectResponse
    {
        $this->authorize('review', $volunteer);

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'reject', 'pending'])],
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $status = match ($validated['decision']) {
            'approve' => Volunteer::STATUS_APPROVED,
            'reject' => Volunteer::STATUS_REJECTED,
            default => Volunteer::STATUS_PENDING,
        };

        $volunteer->forceFill([
            'status' => $status,
            'admin_note' => $validated['admin_note'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ])->save();

        $this->activity->log(
            "volunteer.{$status}",
            "Marked volunteer application from {$volunteer->name} as {$status}",
            $volunteer
        );

        if ($status !== Volunteer::STATUS_PENDING) {
            $volunteer->user?->notify(new VolunteerReviewed($volunteer));
        }

        $this->statistics->flushPublicCache();

        return back()->with('success', "Volunteer application marked as {$status}.");
    }

    public function destroy(Volunteer $volunteer): RedirectResponse
    {
        $this->authorize('delete', $volunteer);

        $name = $volunteer->name;
        $volunteer->delete();

        $this->activity->log('volunteer.deleted', "Deleted volunteer application from {$name}");
        $this->statistics->flushPublicCache();

        return redirect()->route('admin.volunteers.index')->with('success', 'Volunteer application deleted.');
    }
}
