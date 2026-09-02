<div class="grid gap-6 xl:grid-cols-[1fr_20rem]">

    <div class="space-y-6">
        <div class="panel p-6">
            <h3 class="panel-title mb-5">The story</h3>

            <div class="space-y-5">
                <x-form.field
                    name="title" label="Title" :required="true"
                    :value="$story->title"
                    placeholder="e.g. Rehana Rebuilt Her Home — and Her Tailoring Business"
                    rules="required|min:5|max:180"
                />

                <x-form.field
                    name="slug" label="URL slug"
                    :value="$story->slug"
                    placeholder="Leave empty to generate from the title"
                />

                <x-form.textarea
                    name="story" label="Full story" :required="true"
                    :value="$story->story"
                    placeholder="Tell it in their words where you can. Wrap paragraphs in <p> tags."
                    rules="required|min:80" :rows="16"
                />
            </div>
        </div>

        <div class="panel p-6">
            <h3 class="panel-title mb-5">Beneficiary</h3>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.field
                    name="beneficiary_name" label="Name"
                    :value="$story->beneficiary_name" icon="user"
                    placeholder="e.g. Rehana Begum" rules="max:120"
                    help="Only with their permission."
                />

                <x-form.field
                    name="location" label="Location"
                    :value="$story->location" icon="map-pin"
                    placeholder="e.g. Koyra, Khulna" rules="max:180"
                />
            </div>

            <div class="mt-5">
                <x-form.textarea
                    name="beneficiary_description" label="Who they are"
                    :value="$story->beneficiary_description"
                    placeholder="e.g. Widow and mother of three from Koyra, Khulna."
                    :rows="2" :maxlength="500"
                />
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="panel p-6">
            <h3 class="panel-title mb-5">Publishing</h3>

            <div class="space-y-5">
                <x-form.field
                    name="story_date" label="Story date" type="date" :required="true"
                    :value="$story->story_date?->toDateString()"
                    :max="now()->toDateString()"
                    rules="required|notfuture"
                />

                <x-form.select
                    name="campaign_id" label="Related campaign"
                    :value="$story->campaign_id"
                    :options="$campaigns->all()"
                    placeholder="Not linked to a campaign"
                    help="Links the story to the appeal that funded it."
                />

                <div class="space-y-3 border-t border-ink-100 pt-5">
                    <x-form.checkbox
                        name="is_published" label="Visible on the public site"
                        :checked="(bool) ($story->is_published ?? true)"
                    />

                    <x-form.checkbox
                        name="featured" label="Feature this story"
                        :checked="(bool) $story->featured"
                        help="Featured stories appear at the top of the stories page and on the homepage."
                    />
                </div>
            </div>
        </div>

        <div class="panel p-6">
            <h3 class="panel-title mb-5">Photograph</h3>
            <x-form.image name="image" label="Upload a photograph" :current="$story->image_url" />
            <p class="help mt-3">Never publish a face without explicit consent from the person in the photograph.</p>
        </div>
    </div>
</div>
