@extends('layouts.admin')

@section('title', 'New project')
@section('heading', 'New project')
@section('subheading', 'Record humanitarian work delivered or in progress')

@section('content')

    <x-admin.page-header title="Create a project" description="Projects show supporters what campaign money became.">
        <a href="{{ route('admin.projects.index') }}" class="btn btn-outline">
            <x-ui.icon name="arrow-left" class="size-4" /> Back to projects
        </a>
    </x-admin.page-header>

    <x-form.errors class="mb-6" />

    <form method="POST" action="{{ route('admin.projects.store') }}" enctype="multipart/form-data" data-validate>
        @csrf

        @include('admin.projects._form', ['project' => $project, 'categories' => $categories, 'statuses' => $statuses])

        <div class="mt-6 flex flex-wrap gap-3 border-t border-ink-100 pt-6">
            <button type="submit" class="btn btn-primary btn-lg" data-loading-label="Creating…">
                <x-ui.icon name="check" class="size-5" /> Create project
            </button>
            <a href="{{ route('admin.projects.index') }}" class="btn btn-outline btn-lg">Cancel</a>
        </div>
    </form>

@endsection
