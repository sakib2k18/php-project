@extends('layouts.admin')

@section('title', 'Add gallery image')
@section('heading', 'Add gallery image')
@section('subheading', 'JPG, PNG or WEBP')

@section('content')

    <x-admin.page-header title="Add an image">
        <a href="{{ route('admin.gallery.index') }}" class="btn btn-outline">
            <x-ui.icon name="arrow-left" class="size-4" /> Back to gallery
        </a>
    </x-admin.page-header>

    <x-form.errors class="mb-6" />

    <form method="POST" action="{{ route('admin.gallery.store') }}" enctype="multipart/form-data" data-validate>
        @csrf

        @include('admin.gallery._form', ['item' => $item, 'categories' => $categories])

        <div class="mt-6 flex flex-wrap gap-3 border-t border-ink-100 pt-6">
            <button type="submit" class="btn btn-primary btn-lg" data-loading-label="Uploading…">
                <x-ui.icon name="photo" class="size-5" /> Add to gallery
            </button>
            <a href="{{ route('admin.gallery.index') }}" class="btn btn-outline btn-lg">Cancel</a>
        </div>
    </form>

@endsection
