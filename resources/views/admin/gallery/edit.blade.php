@extends('layouts.admin')

@section('title', 'Edit gallery image')
@section('heading', 'Edit gallery image')
@section('subheading', $item->title)

@section('content')

    <x-admin.page-header :title="$item->title" :description="'Added '.$item->created_at->format('j F Y')">
        <a href="{{ route('admin.gallery.index') }}" class="btn btn-outline">
            <x-ui.icon name="arrow-left" class="size-4" /> Back to gallery
        </a>
    </x-admin.page-header>

    <x-form.errors class="mb-6" />

    <form method="POST" action="{{ route('admin.gallery.update', $item) }}" enctype="multipart/form-data" data-validate>
        @csrf
        @method('PUT')

        @include('admin.gallery._form', ['item' => $item, 'categories' => $categories])

        <div class="mt-6 flex flex-wrap gap-3 border-t border-ink-100 pt-6">
            <button type="submit" class="btn btn-primary btn-lg" data-loading-label="Saving…">
                <x-ui.icon name="check" class="size-5" /> Save changes
            </button>
            <a href="{{ route('admin.gallery.index') }}" class="btn btn-outline btn-lg">Cancel</a>
        </div>
    </form>

    <div class="panel mt-6 border-rose-200">
        <div class="panel-head !border-rose-100">
            <h3 class="panel-title text-rose-700">Delete this image</h3>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-4 p-6">
            <p class="max-w-xl text-sm leading-relaxed text-ink-600">
                Deleting removes both the database record and the uploaded file from storage. This cannot be undone.
            </p>

            <form method="POST" action="{{ route('admin.gallery.destroy', $item) }}"
                  data-confirm="“{{ $item->title }}” and its uploaded file will be permanently deleted."
                  data-confirm-title="Delete this image?" data-confirm-action="Delete permanently">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <x-ui.icon name="trash" class="size-4" /> Delete image
                </button>
            </form>
        </div>
    </div>

@endsection
