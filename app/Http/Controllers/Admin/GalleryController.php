<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GalleryItemRequest;
use App\Models\GalleryItem;
use App\Services\ActivityLogger;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function __construct(
        protected ImageUploadService $images,
        protected ActivityLogger $activity,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', GalleryItem::class);

        $filters = $request->validate([
            'category' => ['nullable', Rule::in(array_keys(config('site.gallery_categories')))],
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        return view('admin.gallery.index', [
            'items' => GalleryItem::query()
                ->category($filters['category'] ?? null)
                ->when($filters['q'] ?? null, fn ($q, $t) => $q->where('title', 'like', "%{$t}%"))
                ->orderBy('sort_order')
                ->latest('taken_on')
                ->paginate(12)
                ->withQueryString(),
            'filters' => $filters,
            'categories' => config('site.gallery_categories'),
            'total' => GalleryItem::query()->count(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', GalleryItem::class);

        return view('admin.gallery.create', [
            'item' => new GalleryItem(['is_published' => true, 'taken_on' => now()->toDateString()]),
            'categories' => config('site.gallery_categories'),
        ]);
    }

    public function store(GalleryItemRequest $request): RedirectResponse
    {
        $item = new GalleryItem($request->safe()->except('image'));
        $item->image = $this->images->store($request->file('image'), 'gallery');
        $item->save();

        $this->activity->created($item, "gallery image \"{$item->title}\"");

        return redirect()->route('admin.gallery.index')->with('success', 'Image added to the gallery.');
    }

    public function edit(GalleryItem $gallery): View
    {
        $this->authorize('update', $gallery);

        return view('admin.gallery.edit', [
            'item' => $gallery,
            'categories' => config('site.gallery_categories'),
        ]);
    }

    public function update(GalleryItemRequest $request, GalleryItem $gallery): RedirectResponse
    {
        $gallery->fill($request->safe()->except('image'));

        if ($request->hasFile('image')) {
            $gallery->image = $this->images->store($request->file('image'), 'gallery', $gallery->image);
        }

        $gallery->save();

        $this->activity->updated($gallery, "gallery image \"{$gallery->title}\"");

        return redirect()->route('admin.gallery.index')->with('success', 'Gallery image updated.');
    }

    public function destroy(GalleryItem $gallery): RedirectResponse
    {
        $this->authorize('delete', $gallery);

        $title = $gallery->title;
        $this->images->delete($gallery->image);
        $gallery->delete();

        $this->activity->log('gallery_item.deleted', "Deleted gallery image \"{$title}\"");

        return redirect()->route('admin.gallery.index')->with('success', 'Gallery image deleted.');
    }
}
