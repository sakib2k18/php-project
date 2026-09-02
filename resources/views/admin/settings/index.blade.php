@extends('layouts.admin')

@section('title', 'Organisation settings')
@section('heading', 'Organisation settings')
@section('subheading', 'The identity used across the whole public website')

@section('content')

    <x-admin.page-header
        title="Organisation settings"
        description="These values are read by every page through App\Services\SiteSettings, so changing the organisation name here changes it everywhere — nothing is hardcoded in the templates."
    >
        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="btn btn-outline">
            <x-ui.icon name="eye" class="size-4" /> View public site
        </a>
    </x-admin.page-header>

    <x-form.errors class="mb-6" />

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" data-validate>
        @csrf
        @method('PUT')

        <div class="grid gap-6 xl:grid-cols-[1fr_20rem]">

            <div class="space-y-6">
                {{-- Identity --}}
                <div class="panel p-6">
                    <h3 class="panel-title mb-5">Identity</h3>

                    <div class="space-y-5">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <x-form.field
                                name="org_name" label="Organisation name" :required="true"
                                :value="$values['org_name'] ?? $site->name()"
                                rules="required|min:2|max:120"
                            />

                            <x-form.field
                                name="tagline" label="Tagline" :required="true"
                                :value="$values['tagline'] ?? $site->tagline()"
                                rules="required|max:180"
                            />
                        </div>

                        <x-form.textarea
                            name="about" label="About the organisation" :required="true"
                            :value="$values['about'] ?? ''"
                            rules="required|min:50|max:4000" :rows="6" :maxlength="4000"
                            help="Shown on the homepage, the About page and in the footer."
                        />

                        <div class="grid gap-5 sm:grid-cols-2">
                            <x-form.textarea
                                name="mission" label="Mission" :required="true"
                                :value="$values['mission'] ?? ''"
                                rules="required|min:20|max:2000" :rows="4" :maxlength="2000"
                            />

                            <x-form.textarea
                                name="vision" label="Vision" :required="true"
                                :value="$values['vision'] ?? ''"
                                rules="required|min:20|max:2000" :rows="4" :maxlength="2000"
                            />
                        </div>
                    </div>
                </div>

                {{-- Contact --}}
                <div class="panel p-6">
                    <h3 class="panel-title mb-5">Contact details</h3>

                    <div class="space-y-5">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <x-form.field
                                name="email" label="Email address" type="email" :required="true"
                                :value="$values['email'] ?? $site->email()" icon="mail"
                                rules="required|email"
                            />

                            <x-form.field
                                name="phone" label="Phone number" :required="true"
                                :value="$values['phone'] ?? $site->phone()" icon="phone"
                                rules="required|phone"
                            />
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <x-form.field
                                name="emergency_contact" label="Emergency hotline"
                                :value="$values['emergency_contact'] ?? ''" icon="phone"
                                rules="phone"
                            />

                            <x-form.field
                                name="office_hours" label="Office hours"
                                :value="$values['office_hours'] ?? ''" icon="clock"
                                placeholder="e.g. Sunday – Thursday, 9:00 AM – 5:00 PM"
                                rules="max:150"
                            />
                        </div>

                        <x-form.field
                            name="address" label="Address" :required="true"
                            :value="$values['address'] ?? $site->address()" icon="map-pin"
                            rules="required|max:255"
                        />

                        <div class="grid gap-5 sm:grid-cols-2">
                            <x-form.field
                                name="latitude" label="Latitude" type="number" :required="true"
                                :value="$values['latitude'] ?? $site->latitude()"
                                step="0.0000001" min="-90" max="90"
                                rules="required|numeric"
                            />

                            <x-form.field
                                name="longitude" label="Longitude" type="number" :required="true"
                                :value="$values['longitude'] ?? $site->longitude()"
                                step="0.0000001" min="-180" max="180"
                                rules="required|numeric"
                            />
                        </div>

                        <p class="help">
                            The coordinates drive the map on the homepage, About page and Contact page, and are the
                            default location for the weather widget.
                        </p>
                    </div>
                </div>

                {{-- Social --}}
                <div class="panel p-6">
                    <h3 class="panel-title mb-5">Social links</h3>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-form.field name="facebook_url" label="Facebook URL"
                                      :value="$values['facebook_url'] ?? ''" placeholder="https://facebook.com/…" rules="url" />
                        <x-form.field name="instagram_url" label="Instagram URL"
                                      :value="$values['instagram_url'] ?? ''" placeholder="https://instagram.com/…" rules="url" />
                        <x-form.field name="youtube_url" label="YouTube URL"
                                      :value="$values['youtube_url'] ?? ''" placeholder="https://youtube.com/@…" rules="url" />
                        <x-form.field name="linkedin_url" label="LinkedIn URL"
                                      :value="$values['linkedin_url'] ?? ''" placeholder="https://linkedin.com/company/…" rules="url" />
                    </div>

                    <p class="help mt-3">Only the links you fill in are shown in the header and footer.</p>
                </div>

                {{-- Donation instructions --}}
                <div class="panel p-6">
                    <h3 class="panel-title mb-5">Donation instructions</h3>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-form.textarea
                            name="bank_details" label="Bank transfer details"
                            :value="$values['bank_details'] ?? ''"
                            :rows="6" :maxlength="1000"
                            help="Shown on the Get Involved page."
                        />

                        <x-form.textarea
                            name="mobile_banking_details" label="Mobile banking details"
                            :value="$values['mobile_banking_details'] ?? ''"
                            :rows="6" :maxlength="1000"
                            help="bKash, Nagad, Rocket and any instructions donors need."
                        />
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                <div class="panel p-6">
                    <h3 class="panel-title mb-5">Branding</h3>

                    <div class="space-y-6">
                        <x-form.image name="logo" label="Logo" :current="$site->imageUrl('logo')" aspect="aspect-square" />
                        <x-form.image name="favicon" label="Favicon" :current="$site->imageUrl('favicon')" aspect="aspect-square" />
                    </div>

                    <p class="help mt-4">
                        Without a logo the site renders the built-in mark. Without a favicon it falls back to
                        <code class="rounded bg-ink-100 px-1">public/favicon.svg</code>.
                    </p>
                </div>

                {{-- API status --}}
                <div class="panel p-6">
                    <h3 class="panel-title mb-4">External APIs</h3>

                    <dl class="space-y-4 text-sm">
                        <div>
                            <dt class="flex items-center justify-between gap-3">
                                <span class="font-semibold text-ink-800">Weather</span>
                                <x-ui.badge :tone="$apis['weather']['enabled'] ? 'success' : 'neutral'">
                                    {{ $apis['weather']['enabled'] ? 'Enabled' : 'Disabled' }}
                                </x-ui.badge>
                            </dt>
                            <dd class="mt-1.5 text-xs leading-relaxed text-ink-500">
                                {{ $apis['weather']['provider'] }} · cached for {{ $apis['weather']['cache_minutes'] }} minutes ·
                                {{ $apis['weather']['timeout'] }}s timeout. No API key required.
                            </dd>
                        </div>

                        <div class="border-t border-ink-100 pt-4">
                            <dt class="flex items-center justify-between gap-3">
                                <span class="font-semibold text-ink-800">Maps</span>
                                <x-ui.badge :tone="$apis['map']['enabled'] ? 'success' : 'neutral'">
                                    {{ $apis['map']['enabled'] ? 'Enabled' : 'Disabled' }}
                                </x-ui.badge>
                            </dt>
                            <dd class="mt-1.5 text-xs leading-relaxed text-ink-500">
                                {{ $apis['map']['provider'] }} tiles rendered with Leaflet. Coordinates are stored in
                                the database, so no geocoding request is made on page load.
                            </dd>
                        </div>
                    </dl>

                    <p class="help mt-4">
                        Both providers are configured in <code class="rounded bg-ink-100 px-1">config/apis.php</code>
                        and can be swapped from <code class="rounded bg-ink-100 px-1">.env</code>.
                    </p>
                </div>

                <div class="panel p-6">
                    <h3 class="panel-title mb-4">Stored settings</h3>
                    <p class="text-xs leading-relaxed text-ink-500">
                        {{ $groups->flatten()->count() }} settings across {{ $groups->count() }} groups, cached until
                        you save. Values fall back to <code class="rounded bg-ink-100 px-1">config/site.php</code> if a
                        row is ever missing.
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap gap-3 border-t border-ink-100 pt-6">
            <button type="submit" class="btn btn-primary btn-lg" data-loading-label="Saving…">
                <x-ui.icon name="check" class="size-5" /> Save settings
            </button>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline btn-lg">Cancel</a>
        </div>
    </form>

@endsection
