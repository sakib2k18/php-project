@php
    $socials = $site->socialLinks();
    $year = now()->year;
@endphp

<footer class="mt-auto bg-ink-950 text-ink-300">
    {{-- Call to action band --}}
    <div class="border-b border-white/10 bg-gradient-to-br from-brand-800 to-brand-950">
        <div class="shell py-12 sm:py-14">
            <div class="grid items-center gap-8 lg:grid-cols-[1fr_auto]">
                <div class="max-w-2xl">
                    <p class="eyebrow !text-accent-300">
                        <span class="inline-block h-px w-6 bg-accent-400"></span> Every contribution counts
                    </p>
                    <h2 class="display mt-3 text-3xl text-white sm:text-4xl">
                        Your support can change someone&rsquo;s tomorrow.
                    </h2>
                    <p class="mt-3 text-[15px] leading-relaxed text-white/70">
                        Whether you give once, give monthly, or give your time — it reaches a real family,
                        and we publish exactly where it went.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row lg:flex-col xl:flex-row">
                    <a href="{{ route('donations.create') }}" class="btn btn-accent btn-lg">
                        <x-ui.icon name="heart" class="size-5" /> Donate
                    </a>
                    <a href="{{ route('volunteer.index') }}" class="btn btn-on-dark btn-lg">
                        <x-ui.icon name="users" class="size-5" /> Become a volunteer
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main footer --}}
    <div class="shell py-12">
        <div class="grid gap-10 lg:grid-cols-[1.4fr_1fr_1fr_1.2fr]">
            <div>
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    @if ($site->imageUrl('logo'))
                        <img src="{{ $site->imageUrl('logo') }}" alt="" class="size-10 rounded-xl object-cover">
                    @else
                        <span class="grid size-10 place-items-center rounded-xl bg-brand-600 text-white">
                            <x-ui.icon name="hand-heart" class="size-5.5" />
                        </span>
                    @endif
                    <span class="display text-lg text-white">{{ $site->name() }}</span>
                </a>

                <p class="mt-4 max-w-sm text-sm leading-relaxed text-ink-400">
                    {{ Str::limit(strip_tags($site->get('about', '')), 190) }}
                </p>

                @if ($socials)
                    <div class="mt-5 flex items-center gap-2">
                        @foreach ($socials as $network => $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                               class="grid size-9 place-items-center rounded-lg bg-white/5 text-ink-300 transition hover:bg-brand-600 hover:text-white"
                               aria-label="{{ ucfirst($network) }}">
                                <x-ui.icon :name="$network" class="size-4" />
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <h3 class="text-[11px] font-bold uppercase tracking-[0.13em] text-white">Explore</h3>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('about') }}" class="transition hover:text-brand-300">About us</a></li>
                    <li><a href="{{ route('campaigns.index') }}" class="transition hover:text-brand-300">Campaigns</a></li>
                    <li><a href="{{ route('projects.index') }}" class="transition hover:text-brand-300">Projects</a></li>
                    <li><a href="{{ route('events.index') }}" class="transition hover:text-brand-300">Events</a></li>
                    <li><a href="{{ route('team') }}" class="transition hover:text-brand-300">Our team</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-[11px] font-bold uppercase tracking-[0.13em] text-white">Get involved</h3>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('donations.create') }}" class="transition hover:text-brand-300">Make a donation</a></li>
                    <li><a href="{{ route('volunteer.index') }}" class="transition hover:text-brand-300">Volunteer with us</a></li>
                    <li><a href="{{ route('stories.index') }}" class="transition hover:text-brand-300">Impact stories</a></li>
                    <li><a href="{{ route('news.index') }}" class="transition hover:text-brand-300">News &amp; reports</a></li>
                    <li><a href="{{ route('gallery.index') }}" class="transition hover:text-brand-300">Gallery</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-[11px] font-bold uppercase tracking-[0.13em] text-white">Reach us</h3>
                <ul class="mt-4 space-y-3 text-sm">
                    <li class="flex gap-2.5">
                        <x-ui.icon name="map-pin" class="mt-0.5 size-4 shrink-0 text-brand-400" />
                        <span class="leading-relaxed">{{ $site->address() }}</span>
                    </li>
                    <li class="flex gap-2.5">
                        <x-ui.icon name="mail" class="mt-0.5 size-4 shrink-0 text-brand-400" />
                        <a href="mailto:{{ $site->email() }}" class="break-all transition hover:text-brand-300">{{ $site->email() }}</a>
                    </li>
                    <li class="flex gap-2.5">
                        <x-ui.icon name="phone" class="mt-0.5 size-4 shrink-0 text-brand-400" />
                        <a href="tel:{{ preg_replace('/\s+/', '', $site->phone()) }}" class="transition hover:text-brand-300">{{ $site->phone() }}</a>
                    </li>
                    @if ($site->get('office_hours'))
                        <li class="flex gap-2.5">
                            <x-ui.icon name="clock" class="mt-0.5 size-4 shrink-0 text-brand-400" />
                            <span>{{ $site->get('office_hours') }}</span>
                        </li>
                    @endif
                </ul>

                <a href="{{ route('contact.create') }}" class="btn btn-on-dark btn-sm mt-5">
                    <x-ui.icon name="mail" class="size-4" /> Send a message
                </a>
            </div>
        </div>
    </div>

    {{-- Legal strip --}}
    <div class="border-t border-white/10">
        <div class="shell flex flex-col items-center justify-between gap-3 py-5 text-xs text-ink-500 sm:flex-row">
            <p>&copy; {{ $year }} {{ $site->name() }}. Built as a university project with the Laravel framework.</p>
            <p class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1">
                <span class="inline-flex items-center gap-1.5" title="Khulna University of Engineering & Technology">
                    <img src="{{ asset('images/kuet-logo.jpg') }}" alt="" class="size-4 rounded-sm object-contain">
                    Based at KUET
                </span>
                <span>Weather by Open-Meteo</span>
                <span>Maps &copy; OpenStreetMap contributors</span>
            </p>
        </div>
    </div>
</footer>

{{-- Cookie notice --}}
<div data-cookie-notice hidden
     class="fixed inset-x-3 bottom-3 z-[58] mx-auto max-w-2xl rounded-2xl border border-ink-200 bg-white p-4 shadow-[var(--shadow-deep)] sm:inset-x-6 sm:bottom-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-600">
            <x-ui.icon name="shield-check" class="size-5" />
        </span>
        <p class="min-w-0 flex-1 text-xs leading-relaxed text-ink-600">
            We use only the cookies this site needs to work — your sign-in session, security tokens and a small
            &ldquo;recently viewed campaigns&rdquo; preference. No advertising or third-party tracking.
        </p>
        <button type="button" data-cookie-accept class="btn btn-primary btn-sm shrink-0">Got it</button>
    </div>
</div>
