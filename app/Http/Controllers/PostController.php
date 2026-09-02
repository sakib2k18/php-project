<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', Rule::in(array_keys(config('site.post_categories')))],
        ]);

        $query = Post::query()
            ->published()
            ->search($filters['q'] ?? null)
            ->category($filters['category'] ?? null);

        // The lead article is only pulled out on the unfiltered first page.
        $isPristine = blank($filters['q'] ?? null) && blank($filters['category'] ?? null) && $request->integer('page', 1) === 1;

        $featured = $isPristine
            ? Post::query()->published()->orderByDesc('featured')->orderByDesc('published_at')->first()
            : null;

        $posts = $query
            ->when($featured, fn ($q) => $q->where('id', '!=', $featured->id))
            ->orderByDesc('published_at')
            ->paginate(config('site.pagination.news'))
            ->withQueryString();

        return view('news.index', [
            'posts' => $posts,
            'featured' => $featured,
            'filters' => $filters,
            'categories' => config('site.post_categories'),
            'recent' => Post::query()->published()->latest('published_at')->limit(5)->get(['id', 'title', 'slug', 'published_at']),
        ]);
    }

    public function show(Post $post, Request $request): View
    {
        $isVisible = $post->status === Post::STATUS_PUBLISHED
            && $post->published_at !== null
            && $post->published_at->lte(now());

        abort_if(! $isVisible && ! $request->user()?->isAdmin(), 404);

        $post->incrementQuietly('views');

        return view('news.show', [
            'post' => $post,
            'related' => Post::query()
                ->published()
                ->where('id', '!=', $post->id)
                ->where('category', $post->category)
                ->latest('published_at')
                ->limit(3)
                ->get(),
        ]);
    }
}
