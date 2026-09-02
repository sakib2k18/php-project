<?php

namespace App\Http\Controllers;

use App\Models\GalleryItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'category' => ['nullable', Rule::in(array_keys(config('site.gallery_categories')))],
        ]);

        $items = GalleryItem::query()
            ->published()
            ->category($filters['category'] ?? null)
            ->orderBy('sort_order')
            ->orderByDesc('taken_on')
            ->paginate(12)
            ->withQueryString();

        // Only categories that actually contain images are offered as filters.
        $counts = GalleryItem::query()
            ->published()
            ->selectRaw('category, COUNT(*) as c')
            ->groupBy('category')
            ->pluck('c', 'category');

        return view('gallery.index', [
            'items' => $items,
            'filters' => $filters,
            'categories' => collect(config('site.gallery_categories'))
                ->filter(fn ($label, $key) => ($counts[$key] ?? 0) > 0)
                ->all(),
            'counts' => $counts,
            'total' => GalleryItem::query()->published()->count(),
        ]);
    }
}
