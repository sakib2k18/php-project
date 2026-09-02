@extends('layouts.admin')

@section('title', 'Add team member')
@section('heading', 'Add team member')

@section('content')

    <x-admin.page-header title="Add a team member">
        <a href="{{ route('admin.team.index') }}" class="btn btn-outline">
            <x-ui.icon name="arrow-left" class="size-4" /> Back to team
        </a>
    </x-admin.page-header>

    <x-form.errors class="mb-6" />

    <form method="POST" action="{{ route('admin.team.store') }}" enctype="multipart/form-data" data-validate>
        @csrf

        @include('admin.team._form', ['member' => $member])

        <div class="mt-6 flex flex-wrap gap-3 border-t border-ink-100 pt-6">
            <button type="submit" class="btn btn-primary btn-lg" data-loading-label="Adding…">
                <x-ui.icon name="check" class="size-5" /> Add member
            </button>
            <a href="{{ route('admin.team.index') }}" class="btn btn-outline btn-lg">Cancel</a>
        </div>
    </form>

@endsection
