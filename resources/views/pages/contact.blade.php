@extends('layouts.app')

@section('title', 'Contact us — '.$site->name())
@section('description', 'Get in touch with KUET TRY — office address, phone, email, office hours and a contact form.')

@section('content')

    <x-layout.page-hero
        eyebrow="We read every message"
        title="Contact us"
        description="Questions about a donation, a case to refer, an offer of help, or a request for our expenditure report — all welcome."
        :breadcrumbs="['Contact' => null]"
    />

    <section class="section">
        <div class="shell">
            <div class="grid gap-10 lg:grid-cols-[1fr_22rem] lg:gap-12">

                {{-- Form --}}
                <div class="min-w-0">
                    @if (session('success'))
                        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5" role="status">
                            <div class="flex gap-3">
                                <x-ui.icon name="check-circle" class="size-5 shrink-0 text-emerald-600" />
                                <div>
                                    <h2 class="text-sm font-bold text-emerald-900">Message received</h2>
                                    <p class="mt-1 text-sm leading-relaxed text-emerald-800">{{ session('success') }}</p>
                                    @if (session('contact_reference'))
                                        <p class="mt-2 text-xs text-emerald-700">
                                            Your reference: <span class="font-mono font-bold">{{ session('contact_reference') }}</span>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="panel p-6 sm:p-8">
                        <h2 class="display text-2xl text-ink-900">Send us a message</h2>
                        <p class="mt-2 text-sm text-ink-600">
                            We usually reply within two working days. For anything urgent, please call the hotline instead.
                        </p>

                        <x-form.errors class="mt-6" />

                        <form method="POST" action="{{ route('contact.store') }}" data-validate class="mt-6 space-y-5">
                            @csrf

                            {{-- Honeypot: hidden from people, tempting to bots. --}}
                            <div class="absolute -left-[9999px]" aria-hidden="true">
                                <label for="website">Leave this field empty</label>
                                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <x-form.field
                                    name="name" label="Your name" :required="true"
                                    :value="auth()->user()?->name"
                                    placeholder="Full name" icon="user"
                                    rules="required|min:3|max:120" autocomplete="name"
                                />

                                <x-form.field
                                    name="email" label="Email address" type="email" :required="true"
                                    :value="auth()->user()?->email"
                                    placeholder="you@example.com" icon="mail"
                                    rules="required|email|max:150" autocomplete="email"
                                />
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <x-form.field
                                    name="phone" label="Phone number" type="tel"
                                    :value="auth()->user()?->phone"
                                    placeholder="+880 17XX-XXXXXX" icon="phone"
                                    rules="phone" autocomplete="tel"
                                    help="Optional — helpful if you would rather we call."
                                />

                                <x-form.field
                                    name="subject" label="Subject" :required="true"
                                    placeholder="What is this about?"
                                    rules="required|min:3|max:180"
                                />
                            </div>

                            <x-form.textarea
                                name="message" label="Your message" :required="true"
                                placeholder="Tell us what you need. If this is about a donation, please include the reference number."
                                rules="required|min:20|max:2000" :rows="6" :maxlength="2000"
                            />

                            <div class="flex flex-wrap items-center gap-3 border-t border-ink-100 pt-5">
                                <button type="submit" class="btn btn-primary" data-loading-label="Sending…">
                                    <x-ui.icon name="mail" class="size-4" /> Send message
                                </button>
                                <p class="text-xs text-ink-500">
                                    Your details are stored only so we can reply. They are never shared or published.
                                </p>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Details --}}
                <aside class="space-y-6">
                    <div class="panel p-6">
                        <h2 class="panel-title mb-5">Reach us directly</h2>

                        <ul class="space-y-5 text-sm">
                            <li class="flex gap-3">
                                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-600">
                                    <x-ui.icon name="map-pin" class="size-4.5" />
                                </span>
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Office</p>
                                    <p class="mt-1 leading-relaxed text-ink-700">{{ $site->address() }}</p>
                                </div>
                            </li>

                            <li class="flex gap-3 border-t border-ink-100 pt-5">
                                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-600">
                                    <x-ui.icon name="mail" class="size-4.5" />
                                </span>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Email</p>
                                    <a href="mailto:{{ $site->email() }}" class="mt-1 block break-all font-semibold text-ink-800 transition hover:text-brand-700">{{ $site->email() }}</a>
                                </div>
                            </li>

                            <li class="flex gap-3 border-t border-ink-100 pt-5">
                                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-600">
                                    <x-ui.icon name="phone" class="size-4.5" />
                                </span>
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Phone</p>
                                    <a href="tel:{{ preg_replace('/\s+/', '', $site->phone()) }}" class="mt-1 block font-semibold text-ink-800 transition hover:text-brand-700">{{ $site->phone() }}</a>
                                    @if ($site->get('emergency_contact'))
                                        <p class="mt-1.5 text-xs text-ink-500">
                                            Emergency hotline:
                                            <a href="tel:{{ preg_replace('/\s+/', '', $site->get('emergency_contact')) }}" class="font-semibold text-accent-600">{{ $site->get('emergency_contact') }}</a>
                                        </p>
                                    @endif
                                </div>
                            </li>

                            @if ($site->get('office_hours'))
                                <li class="flex gap-3 border-t border-ink-100 pt-5">
                                    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-600">
                                        <x-ui.icon name="clock" class="size-4.5" />
                                    </span>
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Office hours</p>
                                        <p class="mt-1 text-ink-700">{{ $site->get('office_hours') }}</p>
                                    </div>
                                </li>
                            @endif
                        </ul>

                        @if ($site->socialLinks())
                            <div class="mt-6 border-t border-ink-100 pt-5">
                                <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Follow us</p>
                                <div class="mt-3 flex items-center gap-2">
                                    @foreach ($site->socialLinks() as $network => $url)
                                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                                           class="grid size-9 place-items-center rounded-lg bg-ink-50 text-ink-500 transition hover:bg-brand-600 hover:text-white"
                                           aria-label="{{ ucfirst($network) }}">
                                            <x-ui.icon :name="$network" class="size-4" />
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <x-ui.map
                        :latitude="$site->latitude()"
                        :longitude="$site->longitude()"
                        :title="$site->name()"
                        height="h-72"
                    />
                </aside>
            </div>
        </div>
    </section>

@endsection
