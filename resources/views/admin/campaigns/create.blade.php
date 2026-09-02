@extends('layouts.admin')

@section('title', 'New campaign')
@section('heading', 'New campaign')
@section('subheading', 'Create a fundraising appeal')

@section('content')

    <x-admin.page-header title="Create a campaign" description="Save it as a draft first if you are not ready to publish.">
        <a href="{{ route('admin.campaigns.index') }}" class="btn btn-outline">
            <x-ui.icon name="arrow-left" class="size-4" /> Back to campaigns
        </a>
    </x-admin.page-header>

    <x-form.errors class="mb-6" />

    <form method="POST" action="{{ route('admin.campaigns.store') }}" enctype="multipart/form-data" data-validate>
        @csrf

        @include('admin.campaigns._form', ['campaign' => $campaign, 'categories' => $categories, 'statuses' => $statuses])

        <div class="mt-6 flex flex-wrap gap-3 border-t border-ink-100 pt-6">
            <button type="submit" class="btn btn-primary btn-lg" data-loading-label="Creating…">
                <x-ui.icon name="check" class="size-5" /> Create campaign
            </button>
            <a href="{{ route('admin.campaigns.index') }}" class="btn btn-outline btn-lg">Cancel</a>
        </div>
    </form>

@endsection
