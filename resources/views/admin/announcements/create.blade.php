@extends('layouts.admin')

@section('title', 'New announcement')
@section('heading', 'New announcement')
@section('subheading', 'Publish a notice to the homepage and supporter dashboards')

@section('content')

    <x-admin.page-header title="Create an announcement">
        <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline">
            <x-ui.icon name="arrow-left" class="size-4" /> Back to announcements
        </a>
    </x-admin.page-header>

    <x-form.errors class="mb-6" />

    <form method="POST" action="{{ route('admin.announcements.store') }}" data-validate>
        @csrf

        @include('admin.announcements._form', ['announcement' => $announcement, 'priorities' => $priorities])

        <div class="mt-6 flex flex-wrap gap-3 border-t border-ink-100 pt-6">
            <button type="submit" class="btn btn-primary btn-lg" data-loading-label="Publishing…">
                <x-ui.icon name="megaphone" class="size-5" /> Publish announcement
            </button>
            <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline btn-lg">Cancel</a>
        </div>
    </form>

@endsection
