@extends('layouts.admin')

@section('title', 'New success story')
@section('heading', 'New success story')
@section('subheading', 'Tell the story of a family you supported')

@section('content')

    <x-admin.page-header title="Create a success story">
        <a href="{{ route('admin.stories.index') }}" class="btn btn-outline">
            <x-ui.icon name="arrow-left" class="size-4" /> Back to stories
        </a>
    </x-admin.page-header>

    <x-form.errors class="mb-6" />

    <form method="POST" action="{{ route('admin.stories.store') }}" enctype="multipart/form-data" data-validate>
        @csrf

        @include('admin.stories._form', ['story' => $story, 'campaigns' => $campaigns])

        <div class="mt-6 flex flex-wrap gap-3 border-t border-ink-100 pt-6">
            <button type="submit" class="btn btn-primary btn-lg" data-loading-label="Creating…">
                <x-ui.icon name="check" class="size-5" /> Create story
            </button>
            <a href="{{ route('admin.stories.index') }}" class="btn btn-outline btn-lg">Cancel</a>
        </div>
    </form>

@endsection
