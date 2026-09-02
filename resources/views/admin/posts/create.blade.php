@extends('layouts.admin')

@section('title', 'New article')
@section('heading', 'New article')
@section('subheading', 'Write a field report, announcement or news item')

@section('content')

    <x-admin.page-header title="Create an article">
        <a href="{{ route('admin.posts.index') }}" class="btn btn-outline">
            <x-ui.icon name="arrow-left" class="size-4" /> Back to articles
        </a>
    </x-admin.page-header>

    <x-form.errors class="mb-6" />

    <form method="POST" action="{{ route('admin.posts.store') }}" enctype="multipart/form-data" data-validate>
        @csrf

        @include('admin.posts._form', ['post' => $post, 'statuses' => $statuses, 'categories' => $categories])

        <div class="mt-6 flex flex-wrap gap-3 border-t border-ink-100 pt-6">
            <button type="submit" class="btn btn-primary btn-lg" data-loading-label="Creating…">
                <x-ui.icon name="check" class="size-5" /> Create article
            </button>
            <a href="{{ route('admin.posts.index') }}" class="btn btn-outline btn-lg">Cancel</a>
        </div>
    </form>

@endsection
