@extends('layouts.admin')

@section('title', 'Edit article')
@section('heading', 'Edit article')
@section('subheading', $post->title)

@section('content')

    <x-admin.page-header :title="$post->title" :description="'Last updated '.$post->updated_at->diffForHumans().' · '.number_format($post->views).' views'">
        <a href="{{ route('news.show', $post) }}" target="_blank" rel="noopener" class="btn btn-outline">
            <x-ui.icon name="eye" class="size-4" /> Preview
        </a>
        <a href="{{ route('admin.posts.index') }}" class="btn btn-outline">
            <x-ui.icon name="arrow-left" class="size-4" /> All articles
        </a>
    </x-admin.page-header>

    <x-form.errors class="mb-6" />

    <form method="POST" action="{{ route('admin.posts.update', $post) }}" enctype="multipart/form-data" data-validate>
        @csrf
        @method('PUT')

        @include('admin.posts._form', ['post' => $post, 'statuses' => $statuses, 'categories' => $categories])

        <div class="mt-6 flex flex-wrap gap-3 border-t border-ink-100 pt-6">
            <button type="submit" class="btn btn-primary btn-lg" data-loading-label="Saving…">
                <x-ui.icon name="check" class="size-5" /> Save changes
            </button>
            <a href="{{ route('admin.posts.index') }}" class="btn btn-outline btn-lg">Cancel</a>
        </div>
    </form>

    <div class="panel mt-6 border-rose-200">
        <div class="panel-head !border-rose-100">
            <h3 class="panel-title text-rose-700">Archive this article</h3>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-4 p-6">
            <p class="max-w-xl text-sm leading-relaxed text-ink-600">
                Archiving is a soft delete: the article disappears from the public site but can be restored.
            </p>

            <form method="POST" action="{{ route('admin.posts.destroy', $post) }}"
                  data-confirm="“{{ $post->title }}” will be hidden from the public site. It can be restored later."
                  data-confirm-title="Archive this article?" data-confirm-action="Archive">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <x-ui.icon name="trash" class="size-4" /> Archive article
                </button>
            </form>
        </div>
    </div>

@endsection
