@extends('layouts.admin')

@section('title', 'Campaign overview')
@section('heading', $campaign->title)
@section('subheading', 'Campaign overview and field updates')

@section('content')

    <x-admin.page-header :title="$campaign->title" :description="$campaign->short_description">
        <a href="{{ route('campaigns.show', $campaign) }}" target="_blank" rel="noopener" class="btn btn-outline">
            <x-ui.icon name="eye" class="size-4" /> View public page
        </a>
        <a href="{{ route('admin.campaigns.edit', $campaign) }}" class="btn btn-primary">
            <x-ui.icon name="pencil" class="size-4" /> Edit
        </a>
    </x-admin.page-header>

    {{-- Stats --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Verified raised" icon="banknotes" tone="success" :value="money($totals['approved'])" />
        <x-ui.stat-card label="Pending" icon="clock" tone="warning" :value="money($totals['pending'])" />
        <x-ui.stat-card label="Target" icon="chart" tone="brand" :value="money($campaign->target_amount)" />
        <x-ui.stat-card label="Distinct donors" icon="users" tone="info" :value="number_format($totals['donors'])" />
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_20rem]">
        <div class="space-y-6">

            {{-- Progress --}}
            <div class="panel p-6">
                <h3 class="panel-title mb-4">Funding progress</h3>
                <x-ui.progress
                    :raised="$campaign->raised_amount"
                    :target="$campaign->target_amount"
                    :tone="$campaign->is_emergency ? 'accent' : 'brand'"
                />

                @if ($campaign->raw_progress_percent > 100)
                    <p class="mt-3 rounded-lg bg-emerald-50 p-3 text-xs text-emerald-800">
                        This campaign is over-funded at {{ rtrim(rtrim(number_format($campaign->raw_progress_percent, 1), '0'), '.') }}%
                        of its target. The progress bar is capped at 100%.
                    </p>
                @endif
            </div>

            {{-- Recent donations --}}
            <div class="panel">
                <div class="panel-head">
                    <h3 class="panel-title">Recent donations</h3>
                    <a href="{{ route('admin.donations.index', ['campaign_id' => $campaign->id]) }}" class="link-arrow !text-xs">
                        All for this campaign <x-ui.icon name="arrow-right" class="size-3.5" />
                    </a>
                </div>

                @if ($donations->isNotEmpty())
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Reference</th>
                                    <th scope="col">Donor</th>
                                    <th scope="col" class="text-right">Amount</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Status</th>
                                    <th scope="col"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($donations as $donation)
                                    <tr>
                                        <td class="whitespace-nowrap font-mono text-xs font-semibold text-ink-900">{{ $donation->reference }}</td>
                                        <td class="max-w-[12rem]">
                                            <p class="line-clamp-1 font-medium text-ink-800">{{ $donation->donor_name }}</p>
                                            @if ($donation->is_anonymous)
                                                <p class="text-[11px] text-ink-400">shown as anonymous</p>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap text-right font-bold text-ink-900">{{ money($donation->amount) }}</td>
                                        <td class="whitespace-nowrap text-ink-500">{{ $donation->donated_on->format('j M Y') }}</td>
                                        <td><x-ui.status-badge :status="$donation->status" :label="$donation->status_label" /></td>
                                        <td class="text-right">
                                            <a href="{{ route('admin.donations.show', $donation) }}" class="btn btn-ghost btn-sm">Review</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="p-6 text-center text-sm text-ink-500">No donations recorded for this campaign yet.</p>
                @endif
            </div>

            {{-- Post an update --}}
            <div class="panel">
                <div class="panel-head">
                    <h3 class="panel-title">Post a field update</h3>
                </div>

                <form method="POST" action="{{ route('admin.campaigns.updates.store', $campaign) }}"
                      enctype="multipart/form-data" data-validate class="space-y-5 p-6">
                    @csrf

                    <div class="grid gap-5 sm:grid-cols-[2fr_1fr]">
                        <x-form.field
                            name="title" label="Update title" :required="true"
                            placeholder="e.g. First 300 family kits delivered in Koyra"
                            rules="required|min:5|max:180"
                        />

                        <x-form.field
                            name="published_on" label="Date" type="date" :required="true"
                            :value="now()->toDateString()" :max="now()->toDateString()"
                            rules="required|notfuture"
                        />
                    </div>

                    <x-form.textarea
                        name="body" label="What happened?" :required="true"
                        placeholder="What was delivered, to whom, and what comes next."
                        rules="required|min:20" :rows="4"
                    />

                    <x-form.image name="image" label="Photo (optional)" />

                    <div class="border-t border-ink-100 pt-5">
                        <button type="submit" class="btn btn-primary" data-loading-label="Publishing…">
                            <x-ui.icon name="megaphone" class="size-4" /> Publish update
                        </button>
                    </div>
                </form>
            </div>

            {{-- Existing updates --}}
            @if ($campaign->updates->isNotEmpty())
                <div class="panel">
                    <div class="panel-head">
                        <h3 class="panel-title">Published updates</h3>
                        <x-ui.badge tone="neutral">{{ $campaign->updates->count() }}</x-ui.badge>
                    </div>

                    <ul class="divide-y divide-ink-100">
                        @foreach ($campaign->updates as $update)
                            <li class="flex items-start gap-4 p-4">
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold uppercase tracking-wider text-brand-700">
                                        {{ $update->published_on->format('j F Y') }}
                                    </p>
                                    <h4 class="mt-1 text-sm font-bold text-ink-900">{{ $update->title }}</h4>
                                    <p class="mt-1 text-sm leading-relaxed text-ink-600">{{ $update->body }}</p>
                                    <p class="mt-1.5 text-[11px] text-ink-400">Posted by {{ $update->author?->name ?? 'Administrator' }}</p>
                                </div>

                                <form method="POST" action="{{ route('admin.campaigns.updates.destroy', [$campaign, $update]) }}"
                                      data-confirm="Delete this update? It will disappear from the public campaign page."
                                      data-confirm-title="Delete update" data-confirm-action="Delete">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="grid size-8 shrink-0 place-items-center rounded-lg text-ink-400 transition hover:bg-rose-50 hover:text-rose-600" aria-label="Delete update">
                                        <x-ui.icon name="trash" class="size-4" />
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <aside class="space-y-6">
            <div class="panel p-5">
                <h3 class="panel-title mb-4">Details</h3>

                <dl class="space-y-3.5 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-ink-500">Status</dt>
                        <dd><x-ui.status-badge :status="$campaign->status" :label="$campaign->status_label" /></dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-t border-ink-100 pt-3.5">
                        <dt class="text-ink-500">Category</dt>
                        <dd class="font-semibold text-ink-900">{{ $campaign->category_label }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-t border-ink-100 pt-3.5">
                        <dt class="text-ink-500">Featured</dt>
                        <dd class="font-semibold text-ink-900">{{ $campaign->featured ? 'Yes' : 'No' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-t border-ink-100 pt-3.5">
                        <dt class="text-ink-500">Emergency</dt>
                        <dd class="font-semibold text-ink-900">{{ $campaign->is_emergency ? 'Yes' : 'No' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-t border-ink-100 pt-3.5">
                        <dt class="text-ink-500">Page views</dt>
                        <dd class="font-semibold tabular-nums text-ink-900">{{ number_format($campaign->views) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-t border-ink-100 pt-3.5">
                        <dt class="text-ink-500">Created by</dt>
                        <dd class="truncate font-semibold text-ink-900">{{ $campaign->creator?->name ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            @if ($campaign->has_coordinates)
                <div class="panel overflow-hidden">
                    <div class="panel-head">
                        <h3 class="panel-title">Location</h3>
                    </div>
                    <div class="p-3">
                        <x-ui.map
                            :latitude="$campaign->latitude"
                            :longitude="$campaign->longitude"
                            :title="$campaign->title"
                            :zoom="10"
                            height="h-56"
                        />
                    </div>
                </div>
            @endif
        </aside>
    </div>

@endsection
