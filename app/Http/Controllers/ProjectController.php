<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', Rule::in(array_keys(config('site.campaign_categories')))],
            'status' => ['nullable', Rule::in(array_keys(config('site.project_statuses')))],
        ]);

        $projects = Project::query()
            ->published()
            ->search($filters['q'] ?? null)
            ->category($filters['category'] ?? null)
            ->status($filters['status'] ?? null)
            ->orderByDesc('featured')
            ->orderByDesc('start_date')
            ->paginate(config('site.pagination.public'))
            ->withQueryString();

        return view('projects.index', [
            'projects' => $projects,
            'filters' => $filters,
            'categories' => config('site.campaign_categories'),
            'statuses' => config('site.project_statuses'),
            'counts' => [
                'ongoing' => Project::query()->published()->where('status', Project::STATUS_ONGOING)->count(),
                'completed' => Project::query()->published()->where('status', Project::STATUS_COMPLETED)->count(),
                'beneficiaries' => (int) Project::query()->published()->sum('beneficiaries_count'),
            ],
        ]);
    }

    public function show(Project $project, Request $request): View
    {
        abort_if(! $project->is_published && ! $request->user()?->isAdmin(), 404);

        $related = Project::query()
            ->published()
            ->where('id', '!=', $project->id)
            ->where('category', $project->category)
            ->latest('start_date')
            ->limit(3)
            ->get();

        return view('projects.show', [
            'project' => $project,
            'related' => $related,
        ]);
    }
}
