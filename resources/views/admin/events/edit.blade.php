@extends('layouts.admin')

@section('title', 'Edit event')
@section('heading', 'Edit event')
@section('subheading', $event->title)

@section('content')

    <x-admin.page-header :title="$event->title" :description="$event->event_date->format('l, j F Y')">
        <a href="{{ route('events.show', $event) }}" target="_blank" rel="noopener" class="btn btn-outline">
            <x-ui.icon name="eye" class="size-4" /> Preview
        </a>
        <a href="{{ route('admin.events.index') }}" class="btn btn-outline">
            <x-ui.icon name="arrow-left" class="size-4" /> All events
        </a>
    </x-admin.page-header>

    <x-form.errors class="mb-6" />

    <form method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data" data-validate>
        @csrf
        @method('PUT')

        @include('admin.events._form', ['event' => $event, 'statuses' => $statuses])

        <div class="mt-6 flex flex-wrap gap-3 border-t border-ink-100 pt-6">
            <button type="submit" class="btn btn-primary btn-lg" data-loading-label="Saving…">
                <x-ui.icon name="check" class="size-5" /> Save changes
            </button>
            <a href="{{ route('admin.events.index') }}" class="btn btn-outline btn-lg">Cancel</a>
        </div>
    </form>

    <div class="panel mt-6 border-rose-200">
        <div class="panel-head !border-rose-100">
            <h3 class="panel-title text-rose-700">Archive this event</h3>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-4 p-6">
            <p class="max-w-xl text-sm leading-relaxed text-ink-600">
                Archiving hides the event from the public site. It is a soft delete and can be undone from the archive.
            </p>

            <form method="POST" action="{{ route('admin.events.destroy', $event) }}"
                  data-confirm="“{{ $event->title }}” will be hidden from the public site. It can be restored later."
                  data-confirm-title="Archive this event?" data-confirm-action="Archive">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <x-ui.icon name="trash" class="size-4" /> Archive event
                </button>
            </form>
        </div>
    </div>

@endsection
