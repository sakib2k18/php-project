<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Project;
use App\Models\SuccessStory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Site-wide search across the content types visitors look for.
     * Every query uses Eloquent bindings — no string-concatenated SQL.
     */
    public function __invoke(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $term = $validated['q'] ?? null;

        $results = [
            'campaigns' => collect(),
            'projects' => collect(),
            'stories' => collect(),
        ];

        if (filled($term)) {
            $results['campaigns'] = Campaign::query()->published()->search($term)->limit(6)->get();
            $results['projects'] = Project::query()->published()->search($term)->limit(6)->get();
            $results['stories'] = SuccessStory::query()->published()->search($term)->limit(6)->get();
        }

        return view('pages.search', [
            'term' => $term,
            'results' => $results,
            'total' => collect($results)->sum(fn ($c) => $c->count()),
        ]);
    }
}
