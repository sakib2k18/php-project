<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSuccessStoryRequest;
use App\Http\Requests\Admin\UpdateSuccessStoryRequest;
use App\Models\Campaign;
use App\Models\SuccessStory;
use App\Services\ActivityLogger;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuccessStoryController extends Controller
{
    public function __construct(
        protected ImageUploadService $images,
        protected ActivityLogger $activity,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SuccessStory::class);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'trashed' => ['nullable', 'boolean'],
        ]);

        $stories = SuccessStory::query()
            ->when($request->boolean('trashed'), fn ($q) => $q->onlyTrashed())
            ->search($filters['q'] ?? null)
            ->with('campaign:id,title')
            ->latest('story_date')
            ->paginate(config('site.pagination.admin'))
            ->withQueryString();

        return view('admin.stories.index', [
            'stories' => $stories,
            'filters' => $filters,
            'trashedCount' => SuccessStory::onlyTrashed()->count(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', SuccessStory::class);

        return view('admin.stories.create', [
            'story' => new SuccessStory([
                'is_published' => true,
                'story_date' => now()->toDateString(),
            ]),
            'campaigns' => Campaign::query()->orderBy('title')->pluck('title', 'id'),
        ]);
    }

    public function store(StoreSuccessStoryRequest $request): RedirectResponse
    {
        $story = new SuccessStory($request->safe()->except('image'));
        $story->image = $this->images->store($request->file('image'), 'stories');
        $story->save();

        $this->activity->created($story, "success story \"{$story->title}\"");

        return redirect()->route('admin.stories.index')->with('success', 'Success story created.');
    }

    public function edit(SuccessStory $story): View
    {
        $this->authorize('update', $story);

        return view('admin.stories.edit', [
            'story' => $story,
            'campaigns' => Campaign::query()->orderBy('title')->pluck('title', 'id'),
        ]);
    }

    public function update(UpdateSuccessStoryRequest $request, SuccessStory $story): RedirectResponse
    {
        $story->fill($request->safe()->except('image'));

        if ($request->hasFile('image')) {
            $story->image = $this->images->store($request->file('image'), 'stories', $story->image);
        }

        $story->save();

        $this->activity->updated($story, "success story \"{$story->title}\"");

        return redirect()->route('admin.stories.edit', $story)->with('success', 'Success story updated.');
    }

    public function destroy(SuccessStory $story): RedirectResponse
    {
        $this->authorize('delete', $story);

        $title = $story->title;
        $story->delete();

        $this->activity->log('success_story.deleted', "Deleted success story \"{$title}\"");

        return redirect()->route('admin.stories.index')->with('success', 'Success story archived.');
    }

    public function restore(int $id): RedirectResponse
    {
        $story = SuccessStory::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $story);

        $story->restore();
        $this->activity->log('success_story.restored', "Restored success story \"{$story->title}\"", $story);

        return redirect()->route('admin.stories.index')->with('success', 'Success story restored.');
    }
}
