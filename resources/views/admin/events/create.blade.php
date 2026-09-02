@extends('layouts.admin')

@section('title', 'New event')
@section('heading', 'New event')
@section('subheading', 'Publish an orientation, camp, packing day or fundraiser')

@section('content')

    <x-admin.page-header title="Create an event">
        <a href="{{ route('admin.events.index') }}" class="btn btn-outline">
            <x-ui.icon name="arrow-left" class="size-4" /> Back to events
        </a>
    </x-admin.page-header>

    <x-form.errors class="mb-6" />

    <form method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data" data-validate>
        @csrf

        @include('admin.events._form', ['event' => $event, 'statuses' => $statuses])

        <div class="mt-6 flex flex-wrap gap-3 border-t border-ink-100 pt-6">
            <button type="submit" class="btn btn-primary btn-lg" data-loading-label="Creating…">
                <x-ui.icon name="check" class="size-5" /> Create event
            </button>
            <a href="{{ route('admin.events.index') }}" class="btn btn-outline btn-lg">Cancel</a>
        </div>
    </form>

@endsection
