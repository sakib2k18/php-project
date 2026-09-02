<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>Receipt {{ $donation->reference }} — {{ $site->name() }}</title>

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-ink-100 antialiased">

    {{-- Toolbar — hidden when printing --}}
    <div class="no-print sticky top-0 z-10 border-b border-ink-200 bg-white">
        <div class="mx-auto flex max-w-3xl flex-wrap items-center justify-between gap-3 px-5 py-3.5">
            <a href="{{ route('donations.show', $donation) }}" class="link-arrow">
                <x-ui.icon name="arrow-left" class="size-4" /> Back to donation
            </a>

            <div class="flex gap-2">
                <button type="button" data-copy="{{ $donation->reference }}" data-copy-message="Reference copied."
                        class="btn btn-outline btn-sm">
                    <x-ui.icon name="clipboard" class="size-4" /> Copy reference
                </button>
                <button type="button" data-print class="btn btn-primary btn-sm">
                    <x-ui.icon name="printer" class="size-4" /> Print / Save as PDF
                </button>
            </div>
        </div>
    </div>

    {{-- The sheet itself --}}
    <main class="mx-auto my-6 max-w-3xl px-4 sm:my-10">
        <article class="print-sheet overflow-hidden rounded-2xl bg-white shadow-[var(--shadow-lift)]">

            {{-- Header --}}
            <header class="border-b-2 border-brand-600 px-8 py-7">
                <div class="flex flex-wrap items-start justify-between gap-5">
                    <div class="flex items-center gap-3">
                        @if ($site->imageUrl('logo'))
                            <img src="{{ $site->imageUrl('logo') }}" alt="" class="size-14 rounded-xl object-cover">
                        @else
                            <span class="grid size-14 place-items-center rounded-xl bg-brand-600 text-white">
                                <x-ui.icon name="hand-heart" class="size-7" />
                            </span>
                        @endif

                        <div>
                            <h1 class="display text-2xl text-ink-900">{{ $site->name() }}</h1>
                            <p class="mt-0.5 text-[11px] font-bold uppercase tracking-[0.13em] text-brand-600">
                                Humanitarian Organization
                            </p>
                        </div>
                    </div>

                    <div class="text-right">
                        <p class="text-[11px] font-bold uppercase tracking-[0.13em] text-ink-400">Donation receipt</p>
                        <p class="mt-1 font-mono text-lg font-bold text-ink-900">{{ $donation->reference }}</p>
                        <p class="mt-1 text-xs text-ink-500">Issued {{ now()->format('j F Y') }}</p>
                    </div>
                </div>

                <p class="mt-5 text-xs leading-relaxed text-ink-500">
                    {{ $site->address() }}<br>
                    {{ $site->email() }} &nbsp;·&nbsp; {{ $site->phone() }}
                </p>
            </header>

            {{-- Confirmation strip --}}
            <div class="flex items-center gap-3 border-b border-ink-100 bg-brand-50 px-8 py-4">
                <x-ui.icon name="check-badge" class="size-6 shrink-0 text-brand-600" />
                <div>
                    <p class="text-sm font-bold text-brand-900">Verified donation</p>
                    <p class="text-xs text-brand-800">
                        Checked against our records{{ $donation->reviewed_at ? ' on '.$donation->reviewed_at->format('j F Y') : '' }}.
                    </p>
                </div>
            </div>

            {{-- Body --}}
            <div class="px-8 py-7">
                <div class="grid gap-8 sm:grid-cols-2">
                    <section>
                        <h2 class="text-[11px] font-bold uppercase tracking-[0.13em] text-ink-400">Received from</h2>
                        <p class="mt-2.5 text-base font-bold text-ink-900">{{ $donation->donor_name }}</p>
                        <p class="mt-1 text-sm text-ink-600">{{ $donation->donor_email }}</p>
                        @if ($donation->donor_phone)
                            <p class="text-sm text-ink-600">{{ $donation->donor_phone }}</p>
                        @endif
                        @if ($donation->is_anonymous)
                            <p class="mt-2 inline-flex rounded-md bg-ink-100 px-2 py-1 text-[11px] font-semibold text-ink-600">
                                Listed publicly as anonymous
                            </p>
                        @endif
                    </section>

                    <section class="sm:text-right">
                        <h2 class="text-[11px] font-bold uppercase tracking-[0.13em] text-ink-400">Amount received</h2>
                        <p class="display mt-2 text-4xl text-brand-700">{{ money($donation->amount) }}</p>
                        <p class="mt-1 text-sm text-ink-500">{{ $donation->method_label }}</p>
                    </section>
                </div>

                {{-- Detail table --}}
                <table class="mt-8 w-full border-collapse text-sm">
                    <caption class="sr-only">Donation details</caption>
                    <tbody>
                        @php
                            $rows = [
                                ['Donation ID', $donation->reference],
                                ['Campaign', $donation->campaign?->title ?? 'General fund'],
                                ['Donation method', $donation->method_label],
                                ['Transaction reference', $donation->transaction_reference ?: '—'],
                                ['Date of donation', $donation->donated_on->format('j F Y')],
                                ['Status', $donation->status_label],
                            ];
                        @endphp

                        @foreach ($rows as [$label, $value])
                            <tr class="border-b border-ink-100">
                                <th scope="row" class="w-[42%] py-3 pr-4 text-left align-top font-medium text-ink-500">{{ $label }}</th>
                                <td class="py-3 text-right align-top font-semibold text-ink-900">{{ $value }}</td>
                            </tr>
                        @endforeach

                        <tr class="border-b-2 border-ink-900">
                            <th scope="row" class="py-4 pr-4 text-left align-top text-base font-bold text-ink-900">Total</th>
                            <td class="py-4 text-right align-top text-xl font-bold text-brand-700">{{ money($donation->amount) }}</td>
                        </tr>
                    </tbody>
                </table>

                @if ($donation->message)
                    <div class="mt-6 rounded-xl bg-ink-50 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.13em] text-ink-400">Donor message</p>
                        <p class="mt-1.5 text-sm italic leading-relaxed text-ink-700">&ldquo;{{ $donation->message }}&rdquo;</p>
                    </div>
                @endif

                {{-- Thank you --}}
                <div class="mt-8 rounded-xl border border-brand-100 bg-brand-50 p-5 text-center">
                    <p class="display text-lg text-brand-900">Thank you for standing with us.</p>
                    <p class="mx-auto mt-2 max-w-md text-xs leading-relaxed text-brand-800">
                        Your contribution goes directly to the work described in
                        {{ $donation->campaign ? '“'.$donation->campaign->title.'”' : 'our general fund' }}.
                        Expenditure for every campaign is published in our field reports.
                    </p>
                </div>
            </div>

            {{-- Footer --}}
            <footer class="border-t border-ink-100 bg-ink-50 px-8 py-5">
                <div class="flex flex-wrap items-end justify-between gap-5">
                    <p class="max-w-md text-[11px] leading-relaxed text-ink-500">
                        This receipt is issued by {{ $site->name() }} as confirmation of a verified donation record.
                        It is generated automatically and is valid without a signature.
                    </p>

                    <div class="text-right">
                        <div class="h-10 w-40 border-b border-ink-300"></div>
                        <p class="mt-1.5 text-[11px] font-semibold text-ink-500">Authorised signature</p>
                    </div>
                </div>

                <p class="mt-4 border-t border-ink-200 pt-3 text-center text-[10px] text-ink-400">
                    {{ $site->name() }} · {{ $site->address() }} · Receipt {{ $donation->reference }}
                </p>
            </footer>
        </article>

        <p class="no-print mt-4 text-center text-xs text-ink-500">
            Use your browser&rsquo;s print dialog and choose &ldquo;Save as PDF&rdquo; to keep a copy.
        </p>
    </main>

</body>
</html>
