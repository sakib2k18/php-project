<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Project;
use App\Models\TeamMember;
use App\Services\StatisticsService;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(protected StatisticsService $statistics) {}

    public function about(): View
    {
        return view('pages.about', [
            'impact' => $this->statistics->publicImpact(),
            'team' => TeamMember::query()->active()->ordered()->limit(4)->get(),
            'milestones' => $this->milestones(),
        ]);
    }

    public function team(): View
    {
        return view('pages.team', [
            'members' => TeamMember::query()->active()->ordered()->get(),
        ]);
    }

    public function getInvolved(): View
    {
        return view('pages.get-involved', [
            'impact' => $this->statistics->publicImpact(),
            'urgentCampaigns' => Campaign::query()
                ->active()
                ->orderByDesc('is_emergency')
                ->orderByDesc('featured')
                ->limit(3)
                ->get(),
        ]);
    }

    /**
     * Timeline entries derived from real project data, so the "our journey"
     * section grows with the database instead of being a static list.
     *
     * @return Collection<int, object>
     */
    protected function milestones()
    {
        return Project::query()
            ->published()
            ->orderBy('start_date')
            ->get()
            ->groupBy(fn (Project $project) => $project->start_date->format('Y'))
            ->map(fn ($group, $year) => (object) [
                'year' => $year,
                'count' => $group->count(),
                'beneficiaries' => $group->sum('beneficiaries_count'),
                'highlight' => $group->sortByDesc('beneficiaries_count')->first(),
            ])
            ->values();
    }
}
