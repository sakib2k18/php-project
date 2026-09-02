<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Campaign;
use App\Models\Event;
use App\Models\GalleryItem;
use App\Models\Post;
use App\Models\Project;
use App\Models\SuccessStory;
use App\Services\StatisticsService;
use App\Services\WeatherService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected StatisticsService $statistics,
        protected WeatherService $weather,
    ) {}

    public function index(): View
    {
        return view('pages.home', [
            'impact' => $this->statistics->publicImpact(),

            'emergency' => Campaign::query()
                ->emergency()
                ->orderByDesc('created_at')
                ->first(),

            'featuredCampaigns' => Campaign::query()
                ->published()
                ->featured()
                ->orderByRaw("FIELD(status, 'active', 'completed')")
                ->orderByDesc('created_at')
                ->limit(3)
                ->get(),

            'activeCampaigns' => Campaign::query()
                ->active()
                ->where('featured', false)
                ->orderByDesc('created_at')
                ->limit(3)
                ->get(),

            'announcements' => Announcement::query()
                ->active()
                ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
                ->orderByDesc('published_at')
                ->limit(3)
                ->get(),

            'stories' => SuccessStory::query()
                ->published()
                ->orderByDesc('featured')
                ->orderByDesc('story_date')
                ->limit(3)
                ->get(),

            'events' => Event::query()
                ->published()
                ->upcoming()
                ->orderBy('event_date')
                ->limit(3)
                ->get(),

            'posts' => Post::query()
                ->published()
                ->orderByDesc('published_at')
                ->limit(3)
                ->get(),

            'gallery' => GalleryItem::query()
                ->published()
                ->orderBy('sort_order')
                ->orderByDesc('taken_on')
                ->limit(6)
                ->get(),

            // Returns null on any API problem; the view renders a fallback panel.
            'weather' => $this->weather->current(),
        ]);
    }

    /** Simple sitemap so search engines can discover the public pages. */
    public function sitemap()
    {
        $content = view('pages.sitemap', [
            'campaigns' => Campaign::query()->published()->latest('updated_at')->get(['slug', 'updated_at']),
            'projects' => Project::query()->published()->latest('updated_at')->get(['slug', 'updated_at']),
            'events' => Event::query()->published()->latest('updated_at')->get(['slug', 'updated_at']),
            'stories' => SuccessStory::query()->published()->latest('updated_at')->get(['slug', 'updated_at']),
            'posts' => Post::query()->published()->latest('updated_at')->get(['slug', 'updated_at']),
        ])->render();

        return response($content, 200)->header('Content-Type', 'application/xml');
    }
}
