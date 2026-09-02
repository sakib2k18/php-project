@extends('layouts.admin')

@section('title', 'Edit announcement')
@section('heading', 'Edit announcement')
@section('subheading', $announcement->title)

@section('content')

    <x-admin.page-header :title="$announcement->title" :description="'Last updated '.$announcement->updated_at->diffForHumans()">
        <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline">
            <x-ui.icon name="arrow-left" class="size-4" /> All announcements
        </a>
    </x-admin.page-header>

    <x-form.errors class="mb-6" />

    <form method="POST" action="{{ route('admin.announcements.update', $announcement) }}" data-validate>
        @csrf
        @method('PUT')

        @include('admin.announcements._form', ['announcement' => $announcement, 'priorities' => $priorities])

        <div class="mt-6 flex flex-wrap gap-3 border-t border-ink-100 pt-6">
            <button type="submit" class="btn btn-primary btn-lg" data-loading-label="Saving…">
                <x-ui.icon name="check" class="size-5" /> Save changes
            </button>
            <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline btn-lg">Cancel</a>
        </div>
    </form>

    <div class="panel mt-6 border-rose-200">
        <div class="panel-head !border-rose-100">
            <h3 class="panel-title text-rose-700">Delete this announcement</h3>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-4 p-6">
            <p class="max-w-xl text-sm leading-relaxed text-ink-600">
                Announcements are deleted permanently. If you only want to hide it, set an expiry date or switch the
                status to Draft instead.
            </p>

            <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}"
                  data-confirm="“{{ $announcement->title }}” will be permanently deleted."
                  data-confirm-title="Delete this announcement?" data-confirm-action="Delete permanently">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <x-ui.icon name="trash" class="size-4" /> Delete announcement
                </button>
            </form>
        </div>
    </div>

@endsection
