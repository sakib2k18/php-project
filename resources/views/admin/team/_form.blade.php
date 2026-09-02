<div class="grid gap-6 xl:grid-cols-[1fr_20rem]">

    <div class="panel p-6">
        <h3 class="panel-title mb-5">Member details</h3>

        <div class="space-y-5">
            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.field
                    name="name" label="Full name" :required="true"
                    :value="$member->name" icon="user"
                    rules="required|min:3|max:120"
                />

                <x-form.field
                    name="position" label="Position" :required="true"
                    :value="$member->position"
                    placeholder="e.g. Volunteer Coordinator"
                    rules="required|min:2|max:120"
                />
            </div>

            <x-form.textarea
                name="biography" label="Biography"
                :value="$member->biography"
                placeholder="A short paragraph — what they do for the organisation."
                :rows="4" :maxlength="1500"
            />

            <div class="grid gap-5 border-t border-ink-100 pt-5 sm:grid-cols-2">
                <x-form.field
                    name="email" label="Email address" type="email"
                    :value="$member->email" icon="mail"
                    rules="email"
                />

                <x-form.field
                    name="facebook_url" label="Facebook URL"
                    :value="$member->facebook_url"
                    placeholder="https://facebook.com/…" rules="url"
                />

                <x-form.field
                    name="linkedin_url" label="LinkedIn URL"
                    :value="$member->linkedin_url"
                    placeholder="https://linkedin.com/in/…" rules="url"
                />

                <x-form.field
                    name="twitter_url" label="X / Twitter URL"
                    :value="$member->twitter_url"
                    placeholder="https://x.com/…" rules="url"
                />
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="panel p-6">
            <h3 class="panel-title mb-5">Photograph</h3>
            <x-form.image name="photo" label="Upload a photo" :current="$member->photo_url" aspect="aspect-square" />
            <p class="help mt-3">Without a photo the site shows the member&rsquo;s initials in a brand-coloured circle.</p>
        </div>

        <div class="panel p-6">
            <h3 class="panel-title mb-5">Display</h3>

            <div class="space-y-5">
                <x-form.field
                    name="sort_order" label="Sort order" type="number"
                    :value="$member->sort_order ?? 0" min="0" step="1"
                    help="Lower numbers appear first."
                />

                <x-form.checkbox
                    name="is_active" label="Show on the public team page"
                    :checked="(bool) ($member->is_active ?? true)"
                />
            </div>
        </div>
    </div>
</div>
