@extends('layouts.admin')

@section('title', 'Edit team member')
@section('heading', 'Edit team member')
@section('subheading', $member->name)

@section('content')

    <x-admin.page-header :title="$member->name" :description="$member->position">
        <a href="{{ route('admin.team.index') }}" class="btn btn-outline">
            <x-ui.icon name="arrow-left" class="size-4" /> Back to team
        </a>
    </x-admin.page-header>

    <x-form.errors class="mb-6" />

    <form method="POST" action="{{ route('admin.team.update', $member) }}" enctype="multipart/form-data" data-validate>
        @csrf
        @method('PUT')

        @include('admin.team._form', ['member' => $member])

        <div class="mt-6 flex flex-wrap gap-3 border-t border-ink-100 pt-6">
            <button type="submit" class="btn btn-primary btn-lg" data-loading-label="Saving…">
                <x-ui.icon name="check" class="size-5" /> Save changes
            </button>
            <a href="{{ route('admin.team.index') }}" class="btn btn-outline btn-lg">Cancel</a>
        </div>
    </form>

    <div class="panel mt-6 border-rose-200">
        <div class="panel-head !border-rose-100">
            <h3 class="panel-title text-rose-700">Remove this member</h3>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-4 p-6">
            <p class="max-w-xl text-sm leading-relaxed text-ink-600">
                To hide someone temporarily, untick &ldquo;Show on the public team page&rdquo; instead — removing deletes
                the record and their uploaded photo permanently.
            </p>

            <form method="POST" action="{{ route('admin.team.destroy', $member) }}"
                  data-confirm="{{ $member->name }} will be removed from the team page and their photo deleted."
                  data-confirm-title="Remove this team member?" data-confirm-action="Remove">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <x-ui.icon name="trash" class="size-4" /> Remove member
                </button>
            </form>
        </div>
    </div>

@endsection
