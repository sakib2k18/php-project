<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Models\Post;
use App\Services\ActivityLogger;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PostController extends Controller
{
    public function __construct(
        protected ImageUploadService $images,
        protected ActivityLogger $activity,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Post::class);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(array_keys(config('site.post_statuses')))],
            'category' => ['nullable', Rule::in(array_keys(config('site.post_categories')))],
            'trashed' => ['nullable', 'boolean'],
        ]);

        $posts = Post::query()
            ->when($request->boolean('trashed'), fn ($q) => $q->onlyTrashed())
            ->search($filters['q'] ?? null)
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->category($filters['category'] ?? null)
            ->latest('created_at')
            ->paginate(config('site.pagination.admin'))
            ->withQueryString();

        return view('admin.posts.index', [
            'posts' => $posts,
            'filters' => $filters,
            'statuses' => config('site.post_statuses'),
            'categories' => config('site.post_categories'),
            'trashedCount' => Post::onlyTrashed()->count(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Post::class);

        return view('admin.posts.create', [
            'post' => new Post([
                'status' => Post::STATUS_DRAFT,
                'category' => 'news',
                'author' => $request->user()->name,
            ]),
            'statuses' => config('site.post_statuses'),
            'categories' => config('site.post_categories'),
        ]);
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $post = new Post($request->safe()->except('cover_image'));
        $post->user_id = $request->user()->id;
        $post->cover_image = $this->images->store($request->file('cover_image'), 'posts');
        $post->published_at = $this->resolvePublishedAt($post, $request->validated('published_at'));
        $post->save();

        $this->activity->created($post, "article \"{$post->title}\"");

        return redirect()->route('admin.posts.index')->with('success', 'Article created successfully.');
    }

    public function edit(Post $post): View
    {
        $this->authorize('update', $post);

        return view('admin.posts.edit', [
            'post' => $post,
            'statuses' => config('site.post_statuses'),
            'categories' => config('site.post_categories'),
        ]);
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $post->fill($request->safe()->except('cover_image'));

        if ($request->hasFile('cover_image')) {
            $post->cover_image = $this->images->store($request->file('cover_image'), 'posts', $post->cover_image);
        }

        $post->published_at = $this->resolvePublishedAt($post, $request->validated('published_at'));
        $post->save();

        $this->activity->updated($post, "article \"{$post->title}\"");

        return redirect()->route('admin.posts.edit', $post)->with('success', 'Article updated successfully.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $title = $post->title;
        $post->delete();

        $this->activity->log('post.deleted', "Deleted article \"{$title}\"");

        return redirect()->route('admin.posts.index')->with('success', "Article \"{$title}\" moved to the archive.");
    }

    public function restore(int $id): RedirectResponse
    {
        $post = Post::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $post);

        $post->restore();
        $this->activity->log('post.restored', "Restored article \"{$post->title}\"", $post);

        return redirect()->route('admin.posts.index')->with('success', 'Article restored.');
    }

    /**
     * A published article always has a publish date; an explicit one wins,
     * otherwise it is stamped the moment it goes live.
     */
    protected function resolvePublishedAt(Post $post, ?string $input): ?string
    {
        if (filled($input)) {
            return $input;
        }

        if ($post->status === Post::STATUS_PUBLISHED) {
            return $post->published_at?->toDateTimeString() ?? now()->toDateTimeString();
        }

        return $post->published_at?->toDateTimeString();
    }
}
