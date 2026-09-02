@extends('layouts.admin')

@section('title', 'Edit story')
@section('heading', 'Edit success story')
@section('subheading', $story->title)

@section('content')

    <x-admin.page-header :title="$story->title" :description="'Last updated '.$story->updated_at->diffForHumans()">
        <a href="{{ route('stories.show', $story) }}" target="_blank" rel="noopener" class="btn btn-outline">
            <x-ui.icon name="eye" class="size-4" /> Preview
        </a>
        <a href="{{ route('admin.stories.index') }}" class="btn btn-outline">
            <x-ui.icon name="arrow-left" class="size-4" /> All stories
        </a>
    </x-admin.page-header>

    <x-form.errors class="mb-6" />

    <form method="POST" action="{{ route('admin.stories.update', $story) }}" enctype="multipart/form-data" data-validate>
        @csrf
        @method('PUT')

        @include('admin.stories._form', ['story' => $story, 'campaigns' => $campaigns])

        <div class="mt-6 flex flex-wrap gap-3 border-t border-ink-100 pt-6">
            <button type="submit" class="btn btn-primary btn-lg" data-loading-label="Saving…">
                <x-ui.icon name="check" class="size-5" /> Save changes
            </button>
            <a href="{{ route('admin.stories.index') }}" class="btn btn-outline btn-lg">Cancel</a>
        </div>
    </form>

    <div class="panel mt-6 border-rose-200">
        <div class="panel-head !border-rose-100">
            <h3 class="panel-title text-rose-700">Archive this story</h3>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-4 p-6">
            <p class="max-w-xl text-sm leading-relaxed text-ink-600">
                Archiving hides the story from the public site. It is a soft delete and can be undone.
            </p>

            <form method="POST" action="{{ route('admin.stories.destroy', $story) }}"
                  data-confirm="“{{ $story->title }}” will be hidden from the public site. It can be restored later."
                  data-confirm-title="Archive this story?" data-confirm-action="Archive">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <x-ui.icon name="trash" class="size-4" /> Archive story
                </button>
            </form>
        </div>
    </div>

@endsection
