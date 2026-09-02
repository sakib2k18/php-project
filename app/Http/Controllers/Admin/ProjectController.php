<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProjectRequest;
use App\Http\Requests\Admin\UpdateProjectRequest;
use App\Models\Project;
use App\Services\ActivityLogger;
use App\Services\ImageUploadService;
use App\Services\StatisticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(
        protected ImageUploadService $images,
        protected ActivityLogger $activity,
        protected StatisticsService $statistics,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Project::class);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(array_keys(config('site.project_statuses')))],
            'category' => ['nullable', Rule::in(array_keys(config('site.campaign_categories')))],
            'trashed' => ['nullable', 'boolean'],
        ]);

        $projects = Project::query()
            ->when($request->boolean('trashed'), fn ($q) => $q->onlyTrashed())
            ->search($filters['q'] ?? null)
            ->status($filters['status'] ?? null)
            ->category($filters['category'] ?? null)
            ->latest('start_date')
            ->paginate(config('site.pagination.admin'))
            ->withQueryString();

        return view('admin.projects.index', [
            'projects' => $projects,
            'filters' => $filters,
            'statuses' => config('site.project_statuses'),
            'categories' => config('site.campaign_categories'),
            'trashedCount' => Project::onlyTrashed()->count(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Project::class);

        return view('admin.projects.create', [
            'project' => new Project([
                'status' => Project::STATUS_ONGOING,
                'is_published' => true,
                'start_date' => now()->toDateString(),
            ]),
            'categories' => config('site.campaign_categories'),
            'statuses' => config('site.project_statuses'),
        ]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = new Project($request->safe()->except('image'));
        $project->image = $this->images->store($request->file('image'), 'projects');
        $project->save();

        $this->activity->created($project, "project \"{$project->title}\"");
        $this->statistics->flushPublicCache();

        return redirect()->route('admin.projects.index')->with('success', 'Project created successfully.');
    }

    public function edit(Project $project): View
    {
        $this->authorize('update', $project);

        return view('admin.projects.edit', [
            'project' => $project,
            'categories' => config('site.campaign_categories'),
            'statuses' => config('site.project_statuses'),
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $project->fill($request->safe()->except('image'));

        if ($request->hasFile('image')) {
            $project->image = $this->images->store($request->file('image'), 'projects', $project->image);
        }

        $project->save();

        $this->activity->updated($project, "project \"{$project->title}\"");
        $this->statistics->flushPublicCache();

        return redirect()->route('admin.projects.edit', $project)->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $title = $project->title;
        $project->delete();

        $this->activity->log('project.deleted', "Deleted project \"{$title}\"");
        $this->statistics->flushPublicCache();

        return redirect()->route('admin.projects.index')->with('success', "Project \"{$title}\" moved to the archive.");
    }

    public function restore(int $id): RedirectResponse
    {
        $project = Project::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $project);

        $project->restore();
        $this->activity->log('project.restored', "Restored project \"{$project->title}\"", $project);
        $this->statistics->flushPublicCache();

        return redirect()->route('admin.projects.index')->with('success', 'Project restored.');
    }

    public function togglePublished(Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $project->forceFill(['is_published' => ! $project->is_published])->save();
        $this->activity->updated($project, ($project->is_published ? 'published' : 'unpublished')." project \"{$project->title}\"");

        return back()->with('success', $project->is_published ? 'Project published.' : 'Project unpublished.');
    }
}
