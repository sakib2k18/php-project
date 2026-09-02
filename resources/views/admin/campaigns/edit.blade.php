@extends('layouts.admin')

@section('title', 'Edit campaign')
@section('heading', 'Edit campaign')
@section('subheading', $campaign->title)

@section('content')

    <x-admin.page-header :title="$campaign->title" :description="'Created '.$campaign->created_at->format('j F Y').' · last updated '.$campaign->updated_at->diffForHumans()">
        <a href="{{ route('campaigns.show', $campaign) }}" target="_blank" rel="noopener" class="btn btn-outline">
            <x-ui.icon name="eye" class="size-4" /> Preview
        </a>
        <a href="{{ route('admin.campaigns.show', $campaign) }}" class="btn btn-outline">
            <x-ui.icon name="chart" class="size-4" /> Overview
        </a>
        <a href="{{ route('admin.campaigns.index') }}" class="btn btn-outline">
            <x-ui.icon name="arrow-left" class="size-4" /> All campaigns
        </a>
    </x-admin.page-header>

    <x-form.errors class="mb-6" />

    <form method="POST" action="{{ route('admin.campaigns.update', $campaign) }}" enctype="multipart/form-data" data-validate>
        @csrf
        @method('PUT')

        @include('admin.campaigns._form', ['campaign' => $campaign, 'categories' => $categories, 'statuses' => $statuses])

        <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-ink-100 pt-6">
            <button type="submit" class="btn btn-primary btn-lg" data-loading-label="Saving…">
                <x-ui.icon name="check" class="size-5" /> Save changes
            </button>
            <a href="{{ route('admin.campaigns.index') }}" class="btn btn-outline btn-lg">Cancel</a>
        </div>
    </form>

    {{-- Delete, kept outside the edit form --}}
    <div class="panel mt-6 border-rose-200">
        <div class="panel-head !border-rose-100">
            <h3 class="panel-title text-rose-700">Archive this campaign</h3>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-4 p-6">
            <p class="max-w-xl text-sm leading-relaxed text-ink-600">
                Archiving hides the campaign from the public site. It is a soft delete: the donation history stays
                intact and the campaign can be restored from the archive at any time.
            </p>

            <form method="POST" action="{{ route('admin.campaigns.destroy', $campaign) }}"
                  data-confirm="“{{ $campaign->title }}” will be hidden from the public site. Donation records are kept and it can be restored later."
                  data-confirm-title="Archive this campaign?" data-confirm-action="Archive">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <x-ui.icon name="trash" class="size-4" /> Archive campaign
                </button>
            </form>
        </div>
    </div>

@endsection
