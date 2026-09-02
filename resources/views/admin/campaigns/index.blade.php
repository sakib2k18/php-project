@extends('layouts.admin')

@section('title', 'Campaigns')
@section('heading', 'Campaigns')
@section('subheading', 'Create, edit and archive fundraising appeals')

@section('content')

    <x-admin.page-header
        title="All campaigns"
        description="Only Active and Completed campaigns appear on the public website. Drafts stay private until you publish them."
        :count="$campaigns->total()"
    >
        @if ($trashedCount && ! request()->boolean('trashed'))
            <a href="{{ route('admin.campaigns.index', ['trashed' => 1]) }}" class="btn btn-outline">
                <x-ui.icon name="trash" class="size-4" /> Archive ({{ $trashedCount }})
            </a>
        @elseif (request()->boolean('trashed'))
            <a href="{{ route('admin.campaigns.index') }}" class="btn btn-outline">
                <x-ui.icon name="arrow-left" class="size-4" /> Back to active list
            </a>
        @endif

        <a href="{{ route('admin.campaigns.create') }}" class="btn btn-primary">
            <x-ui.icon name="plus" class="size-4" /> New campaign
        </a>
    </x-admin.page-header>

    @if (request()->boolean('trashed'))
        <div class="mb-4 flex gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4">
            <x-ui.icon name="info" class="size-5 shrink-0 text-amber-600" />
            <p class="text-sm leading-relaxed text-amber-800">
                These campaigns are soft-deleted. Their donation history is intact and they can be restored at any time.
            </p>
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.campaigns.index') }}" data-filter-form class="panel mb-6 p-4">
        @if (request()->boolean('trashed'))
            <input type="hidden" name="trashed" value="1">
        @endif

        <div class="grid gap-3 lg:grid-cols-[2fr_1fr_1fr_auto]">
            <div class="relative">
                <label for="q" class="sr-only">Search campaigns</label>
                <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-ink-400">
                    <x-ui.icon name="search" class="size-4.5" />
                </span>
                <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                       placeholder="Search title, description or location…" class="field pl-10" data-filter-search>
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
                    <button type="button" data-filter-reset="{{ route('admin.campaigns.index') }}" class="btn btn-ghost" aria-label="Clear filters">
                        <x-ui.icon name="x-mark" class="size-4" />
                    </button>
                @endif
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="panel overflow-hidden">
        @if ($campaigns->isNotEmpty())
            <div class="table-wrap hidden lg:block">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Campaign</th>
                            <th scope="col">Category</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="w-44">Raised / target</th>
                            <th scope="col" class="text-right">Donations</th>
                            <th scope="col">Dates</th>
                            <th scope="col"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($campaigns as $campaign)
                            <tr>
                                <td class="max-w-[18rem]">
                                    <div class="flex items-center gap-3">
                                        <x-ui.media :src="$campaign->image_url" :alt="$campaign->title" :seed="$campaign->slug"
                                                    icon="heart" ratio="aspect-square" rounded="rounded-lg" class="w-10 shrink-0" />
                                        <div class="min-w-0">
                                            <p class="line-clamp-1 font-semibold text-ink-900">{{ $campaign->title }}</p>
                                            <div class="mt-0.5 flex flex-wrap items-center gap-1.5">
                                                @if ($campaign->featured)<x-ui.badge tone="accent">Featured</x-ui.badge>@endif
                                                @if ($campaign->is_emergency)<x-ui.badge tone="danger">Emergency</x-ui.badge>@endif
                                                <span class="truncate text-[11px] text-ink-400">{{ $campaign->location }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap text-ink-600">{{ $campaign->category_label }}</td>
                                <td><x-ui.status-badge :status="$campaign->status" :label="$campaign->status_label" /></td>
                                <td>
                                    <p class="text-xs font-bold text-ink-900">{{ money($campaign->raised_amount) }}</p>
                                    <p class="text-[11px] text-ink-500">of {{ money($campaign->target_amount) }}</p>
                                    <div class="mt-1.5">
                                        <x-ui.progress :raised="$campaign->raised_amount" :target="$campaign->target_amount" :show-labels="false" size="sm" />
                                    </div>
                                </td>
                                <td class="text-right tabular-nums text-ink-600">{{ number_format($campaign->approved_donations_count) }}</td>
                                <td class="whitespace-nowrap text-xs text-ink-500">
                                    {{ $campaign->start_date->format('j M y') }}
                                    @if ($campaign->end_date)<br>→ {{ $campaign->end_date->format('j M y') }}@endif
                                </td>
                                <td>
                                    @if ($campaign->trashed())
                                        <form method="POST" action="{{ route('admin.campaigns.restore', $campaign->id) }}" class="flex justify-end">
                                            @csrf
                                            <button type="submit" class="btn btn-outline btn-sm">
                                                <x-ui.icon name="refresh" class="size-3.5" /> Restore
                                            </button>
                                        </form>
                                    @else
                                        <x-admin.row-actions
                                            :view="route('admin.campaigns.show', $campaign)"
                                            :edit="route('admin.campaigns.edit', $campaign)"
                                            :delete="route('admin.campaigns.destroy', $campaign)"
                                            delete-title="Archive this campaign?"
                                            delete-label="Archive"
                                            :delete-confirm="'“'.$campaign->title.'” will be hidden from the public site. Its donation history is kept and it can be restored later.'"
                                        >
                                            <form method="POST" action="{{ route('admin.campaigns.featured', $campaign) }}">
                                                @csrf
                                                <button type="submit"
                                                        class="grid size-8 place-items-center rounded-lg transition {{ $campaign->featured ? 'text-accent-600 hover:bg-accent-50' : 'text-ink-400 hover:bg-ink-100' }}"
                                                        aria-label="{{ $campaign->featured ? 'Remove from featured' : 'Mark as featured' }}">
                                                    <x-ui.icon name="sparkles" class="size-4" />
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

            {{-- Mobile cards --}}
            <ul class="divide-y divide-ink-100 lg:hidden">
                @foreach ($campaigns as $campaign)
                    <li class="p-4">
                        <div class="flex items-start gap-3">
                            <x-ui.media :src="$campaign->image_url" :alt="$campaign->title" :seed="$campaign->slug"
                                        icon="heart" ratio="aspect-square" rounded="rounded-lg" class="w-12 shrink-0" />
                            <div class="min-w-0 flex-1">
                                <p class="line-clamp-2 text-sm font-bold text-ink-900">{{ $campaign->title }}</p>
                                <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                    <x-ui.status-badge :status="$campaign->status" :label="$campaign->status_label" />
                                    <span class="text-[11px] text-ink-500">{{ $campaign->category_label }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <x-ui.progress :raised="$campaign->raised_amount" :target="$campaign->target_amount" size="sm" />
                        </div>

                        <div class="mt-3 flex gap-2">
                            @if ($campaign->trashed())
                                <form method="POST" action="{{ route('admin.campaigns.restore', $campaign->id) }}" class="flex-1">
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-sm btn-block">Restore</button>
                                </form>
                            @else
                                <a href="{{ route('admin.campaigns.show', $campaign) }}" class="btn btn-outline btn-sm flex-1">View</a>
                                <a href="{{ route('admin.campaigns.edit', $campaign) }}" class="btn btn-primary btn-sm flex-1">Edit</a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="px-4 pb-4">{{ $campaigns->links() }}</div>
        @else
            <div class="p-6">
                <x-ui.empty-state
                    icon="heart"
                    :title="array_filter($filters) ? 'No campaigns match your filters' : 'No campaigns yet'"
                    :description="array_filter($filters) ? 'Try a different status or category.' : 'Create your first appeal to start raising funds.'"
                >
                    @if (array_filter($filters))
                        <a href="{{ route('admin.campaigns.index') }}" class="btn btn-outline btn-sm">Clear filters</a>
                    @endif
                    <a href="{{ route('admin.campaigns.create') }}" class="btn btn-primary btn-sm">
                        <x-ui.icon name="plus" class="size-4" /> New campaign
                    </a>
                </x-ui.empty-state>
            </div>
        @endif
    </div>

@endsection
