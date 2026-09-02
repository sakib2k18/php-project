@extends('layouts.admin')

@section('title', 'Projects')
@section('heading', 'Projects')
@section('subheading', 'Completed and ongoing humanitarian work')

@section('content')

    <x-admin.page-header
        title="All projects"
        description="Projects are what campaign money became. Unpublished projects are hidden from the public site."
        :count="$projects->total()"
    >
        @if ($trashedCount && ! request()->boolean('trashed'))
            <a href="{{ route('admin.projects.index', ['trashed' => 1]) }}" class="btn btn-outline">
                <x-ui.icon name="trash" class="size-4" /> Archive ({{ $trashedCount }})
            </a>
        @elseif (request()->boolean('trashed'))
            <a href="{{ route('admin.projects.index') }}" class="btn btn-outline">
                <x-ui.icon name="arrow-left" class="size-4" /> Back to list
            </a>
        @endif

        <a href="{{ route('admin.projects.create') }}" class="btn btn-primary">
            <x-ui.icon name="plus" class="size-4" /> New project
        </a>
    </x-admin.page-header>

    <form method="GET" action="{{ route('admin.projects.index') }}" data-filter-form class="panel mb-6 p-4">
        @if (request()->boolean('trashed'))
            <input type="hidden" name="trashed" value="1">
        @endif

        <div class="grid gap-3 lg:grid-cols-[2fr_1fr_1fr_auto]">
            <div class="relative">
                <label for="q" class="sr-only">Search projects</label>
                <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-ink-400">
                    <x-ui.icon name="search" class="size-4.5" />
                </span>
                <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                       placeholder="Search projects…" class="field pl-10" data-filter-search>
            </div>

            <div>
                <label for="status" class="sr-only">Status</label>
                <select id="status" name="status" class="field">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="category" class="sr-only">Category</label>
                <select id="category" name="category" class="field">
                    <option value="">All categories</option>
                    @foreach ($categories as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['category'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1 lg:flex-none">Filter</button>
                @if (array_filter(Arr::except($filters, 'trashed')))
                    <button type="button" data-filter-reset="{{ route('admin.projects.index') }}" class="btn btn-ghost" aria-label="Clear filters">
                        <x-ui.icon name="x-mark" class="size-4" />
                    </button>
                @endif
            </div>
        </div>
    </form>

    <div class="panel overflow-hidden">
        @if ($projects->isNotEmpty())
            <div class="table-wrap hidden lg:block">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Project</th>
                            <th scope="col">Category</th>
                            <th scope="col">Status</th>
                            <th scope="col">Visibility</th>
                            <th scope="col" class="text-right">Reached</th>
                            <th scope="col">Dates</th>
                            <th scope="col"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projects as $project)
                            <tr>
                                <td class="max-w-[20rem]">
                                    <div class="flex items-center gap-3">
                                        <x-ui.media :src="$project->image_url" :alt="$project->title" :seed="$project->slug"
                                                    icon="briefcase" ratio="aspect-square" rounded="rounded-lg" class="w-10 shrink-0" />
                                        <div class="min-w-0">
                                            <p class="line-clamp-1 font-semibold text-ink-900">{{ $project->title }}</p>
                                            <p class="truncate text-[11px] text-ink-400">{{ $project->location }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap text-ink-600">{{ $project->category_label }}</td>
                                <td><x-ui.status-badge :status="$project->status" :label="$project->status_label" /></td>
                                <td>
                                    @if ($project->is_published)
                                        <x-ui.badge tone="success" icon="eye">Published</x-ui.badge>
                                    @else
                                        <x-ui.badge tone="neutral">Hidden</x-ui.badge>
                                    @endif
                                </td>
                                <td class="text-right tabular-nums text-ink-600">{{ number_format($project->beneficiaries_count) }}</td>
                                <td class="whitespace-nowrap text-xs text-ink-500">
                                    {{ $project->start_date->format('j M y') }}
                                    @if ($project->end_date)<br>→ {{ $project->end_date->format('j M y') }}@endif
                                </td>
                                <td>
                                    @if ($project->trashed())
                                        <form method="POST" action="{{ route('admin.projects.restore', $project->id) }}" class="flex justify-end">
                                            @csrf
                                            <button type="submit" class="btn btn-outline btn-sm">
                                                <x-ui.icon name="refresh" class="size-3.5" /> Restore
                                            </button>
                                        </form>
                                    @else
                                        <x-admin.row-actions
                                            :view="route('projects.show', $project)"
                                            :edit="route('admin.projects.edit', $project)"
                                            :delete="route('admin.projects.destroy', $project)"
                                            delete-title="Archive this project?"
                                            delete-label="Archive"
                                            :delete-confirm="'“'.$project->title.'” will be hidden from the public site. It can be restored later.'"
                                        >
                                            <form method="POST" action="{{ route('admin.projects.publish', $project) }}">
                                                @csrf
                                                <button type="submit"
                                                        class="grid size-8 place-items-center rounded-lg transition {{ $project->is_published ? 'text-brand-600 hover:bg-brand-50' : 'text-ink-400 hover:bg-ink-100' }}"
                                                        aria-label="{{ $project->is_published ? 'Unpublish' : 'Publish' }}">
                                                    <x-ui.icon name="globe" class="size-4" />
                                                </button>
                                            </form>
                                        </x-admin.row-actions>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <ul class="divide-y divide-ink-100 lg:hidden">
                @foreach ($projects as $project)
                    <li class="p-4">
                        <div class="flex items-start gap-3">
                            <x-ui.media :src="$project->image_url" :alt="$project->title" :seed="$project->slug"
                                        icon="briefcase" ratio="aspect-square" rounded="rounded-lg" class="w-12 shrink-0" />
                            <div class="min-w-0 flex-1">
                                <p class="line-clamp-2 text-sm font-bold text-ink-900">{{ $project->title }}</p>
                                <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                    <x-ui.status-badge :status="$project->status" :label="$project->status_label" />
                                    <span class="text-[11px] text-ink-500">{{ $project->category_label }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 flex gap-2">
                            @if ($project->trashed())
                                <form method="POST" action="{{ route('admin.projects.restore', $project->id) }}" class="flex-1">
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-sm btn-block">Restore</button>
                                </form>
                            @else
                                <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-primary btn-sm flex-1">Edit</a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="px-4 pb-4">{{ $projects->links() }}</div>
        @else
            <div class="p-6">
                <x-ui.empty-state icon="briefcase" title="No projects yet"
                                  description="Record the work you have delivered so supporters can see where donations went.">
                    <a href="{{ route('admin.projects.create') }}" class="btn btn-primary btn-sm">
                        <x-ui.icon name="plus" class="size-4" /> New project
                    </a>
                </x-ui.empty-state>
            </div>
        @endif
    </div>

@endsection
