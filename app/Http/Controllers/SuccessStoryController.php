<?php

namespace App\Http\Controllers;

use App\Models\SuccessStory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuccessStoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $stories = SuccessStory::query()
            ->published()
            ->search($filters['q'] ?? null)
            ->orderByDesc('featured')
            ->orderByDesc('story_date')
            ->paginate(config('site.pagination.public'))
            ->withQueryString();

        return view('stories.index', [
            'stories' => $stories,
            'filters' => $filters,
            'featured' => SuccessStory::query()->published()->where('featured', true)->latest('story_date')->first(),
        ]);
    }

    public function show(SuccessStory $story, Request $request): View
    {
        abort_if(! $story->is_published && ! $request->user()?->isAdmin(), 404);

        $story->loadMissing('campaign');

        return view('stories.show', [
            'story' => $story,
            'related' => SuccessStory::query()
                ->published()
                ->where('id', '!=', $story->id)
                ->latest('story_date')
                ->limit(3)
                ->get(),
        ]);
    }
}
