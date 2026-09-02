{{--
    Shared campaign field set for create and edit.

    `raised_amount` is deliberately absent: it is not fillable on the model and
    is only ever changed by DonationService when a donation is approved.
--}}

<div class="grid gap-6 xl:grid-cols-[1fr_20rem]">

    {{-- Main fields --}}
    <div class="space-y-6">
        <div class="panel p-6">
            <h3 class="panel-title mb-5">Campaign details</h3>

            <div class="space-y-5">
                <x-form.field
                    name="title" label="Campaign title" :required="true"
                    :value="$campaign->title"
                    placeholder="e.g. Cyclone Response Fund — Coastal Khulna"
                    rules="required|min:5|max:180"
                />

                <x-form.field
                    name="slug" label="URL slug"
                    :value="$campaign->slug"
                    placeholder="Leave empty to generate from the title"
                    help="Only letters, numbers and hyphens. Changing this breaks existing links."
                />

                <x-form.textarea
                    name="short_description" label="Short description" :required="true"
                    :value="$campaign->short_description"
                    placeholder="One or two sentences shown on the campaign card and in search results."
                    rules="required|min:20|max:500" :rows="3" :maxlength="500"
                />

                <x-form.textarea
                    name="description" label="Full description" :required="true"
                    :value="$campaign->description"
                    placeholder="The full appeal. Basic HTML such as <p> and <strong> is supported."
                    rules="required|min:50" :rows="12"
                    help="Explain what the money buys, who it reaches and how it is delivered."
                />
            </div>
        </div>

        <div class="panel p-6">
            <h3 class="panel-title mb-5">Target &amp; dates</h3>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.field
                    name="target_amount" label="Target amount" type="number" :required="true"
                    :value="$campaign->target_amount"
                    :prefix="config('site.currency.symbol')"
                    min="100" step="1"
                    rules="required|numeric|minval:100"
                />

                <x-form.field
                    name="beneficiaries_count" label="People this will reach" type="number"
                    :value="$campaign->beneficiaries_count ?? 0"
                    min="0" step="1"
                    help="Used in the impact statistics on the homepage."
                />
            </div>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <x-form.field
                    name="start_date" label="Start date" type="date" :required="true"
                    :value="$campaign->start_date?->toDateString()"
                    rules="required"
                />

                <x-form.field
                    name="end_date" label="End date" type="date"
                    :value="$campaign->end_date?->toDateString()"
                    help="Leave empty for an open-ended appeal."
                />
            </div>
        </div>

        <div class="panel p-6">
            <h3 class="panel-title mb-5">Location</h3>

            <x-form.field
                name="location" label="Location description"
                :value="$campaign->location" icon="map-pin"
                placeholder="e.g. Koyra &amp; Dacope, Khulna"
                rules="max:180"
            />

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <x-form.field
                    name="latitude" label="Latitude" type="number"
                    :value="$campaign->latitude"
                    step="0.0000001" min="-90" max="90"
                    placeholder="22.3390000"
                />

                <x-form.field
                    name="longitude" label="Longitude" type="number"
                    :value="$campaign->longitude"
                    step="0.0000001" min="-180" max="180"
                    placeholder="89.2910000"
                />
            </div>

            <p class="help mt-3">
                Coordinates are stored so the map can be drawn without calling a geocoding service on every page
                view — which is what the OpenStreetMap usage policy asks for. Provide both or neither.
            </p>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        <div class="panel p-6">
            <h3 class="panel-title mb-5">Publishing</h3>

            <div class="space-y-5">
                <x-form.select
                    name="status" label="Status" :required="true"
                    :value="$campaign->status"
                    :options="$statuses"
                    rules="required"
                    help="Only Active and Completed campaigns are visible publicly."
                />

                <x-form.select
                    name="category" label="Category" :required="true"
                    :value="$campaign->category"
                    :options="$categories" placeholder="Choose a category…"
                    rules="required"
                />

                <div class="space-y-3 border-t border-ink-100 pt-5">
                    <x-form.checkbox
                        name="featured" label="Feature on the homepage"
                        :checked="(bool) $campaign->featured"
                        help="Featured campaigns appear in the highlighted grid."
                    />

                    <x-form.checkbox
                        name="is_emergency" label="Mark as an emergency appeal"
                        :checked="(bool) $campaign->is_emergency"
                        help="Shows the site-wide alert ribbon and the hero spotlight."
                    />
                </div>
            </div>
        </div>

        <div class="panel p-6">
            <h3 class="panel-title mb-5">Cover image</h3>

            <x-form.image
                name="cover_image" label="Upload a cover image"
                :current="$campaign->image_url"
            />

            <p class="help mt-3">
                Optional. Without one, the site renders a designed placeholder derived from the campaign title,
                so the card layout still looks intentional.
            </p>
        </div>

        @if ($campaign->exists)
            <div class="panel p-6">
                <h3 class="panel-title mb-4">Funding</h3>

                <x-ui.progress :raised="$campaign->raised_amount" :target="$campaign->target_amount" />

                <p class="help mt-4">
                    The raised amount is maintained automatically when donations are approved. It cannot be edited
                    by hand — use
                    <a href="{{ route('admin.donations.index', ['campaign_id' => $campaign->id]) }}" class="font-semibold text-brand-700 underline">the donation list</a>
                    to review individual records.
                </p>
            </div>
        @endif
    </div>
</div>
