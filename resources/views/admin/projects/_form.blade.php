<div class="grid gap-6 xl:grid-cols-[1fr_20rem]">

    <div class="space-y-6">
        <div class="panel p-6">
            <h3 class="panel-title mb-5">Project details</h3>

            <div class="space-y-5">
                <x-form.field
                    name="title" label="Project title" :required="true"
                    :value="$project->title"
                    placeholder="e.g. Winter Clothes Distribution 2025"
                    rules="required|min:5|max:180"
                />

                <x-form.field
                    name="slug" label="URL slug"
                    :value="$project->slug"
                    placeholder="Leave empty to generate from the title"
                    help="Only letters, numbers and hyphens."
                />

                <x-form.textarea
                    name="summary" label="Summary" :required="true"
                    :value="$project->summary"
                    placeholder="One or two sentences for the project card."
                    rules="required|min:20|max:500" :rows="3" :maxlength="500"
                />

                <x-form.textarea
                    name="description" label="Full description" :required="true"
                    :value="$project->description"
                    placeholder="What was delivered, to whom, at what cost. Basic HTML is supported."
                    rules="required|min:50" :rows="12"
                />
            </div>
        </div>

        <div class="panel p-6">
            <h3 class="panel-title mb-5">Dates, reach &amp; location</h3>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.field
                    name="start_date" label="Start date" type="date" :required="true"
                    :value="$project->start_date?->toDateString()" rules="required"
                />

                <x-form.field
                    name="end_date" label="End date" type="date"
                    :value="$project->end_date?->toDateString()"
                    help="Leave empty while the project is ongoing."
                />
            </div>

            <div class="mt-5">
                <x-form.field
                    name="beneficiaries_count" label="People reached" type="number"
                    :value="$project->beneficiaries_count ?? 0" min="0" step="1"
                    help="Feeds the impact statistics on the homepage and About page."
                />
            </div>

            <div class="mt-5">
                <x-form.field
                    name="location" label="Location description"
                    :value="$project->location" icon="map-pin"
                    placeholder="e.g. Kurigram &amp; Khulna" rules="max:180"
                />
            </div>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <x-form.field
                    name="latitude" label="Latitude" type="number"
                    :value="$project->latitude" step="0.0000001" min="-90" max="90"
                    placeholder="22.8156000"
                />

                <x-form.field
                    name="longitude" label="Longitude" type="number"
                    :value="$project->longitude" step="0.0000001" min="-180" max="180"
                    placeholder="89.5687000"
                />
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="panel p-6">
            <h3 class="panel-title mb-5">Publishing</h3>

            <div class="space-y-5">
                <x-form.select
                    name="status" label="Status" :required="true"
                    :value="$project->status" :options="$statuses" rules="required"
                />

                <x-form.select
                    name="category" label="Category" :required="true"
                    :value="$project->category" :options="$categories"
                    placeholder="Choose a category…" rules="required"
                />

                <div class="space-y-3 border-t border-ink-100 pt-5">
                    <x-form.checkbox
                        name="is_published" label="Visible on the public site"
                        :checked="(bool) ($project->is_published ?? true)"
                    />

                    <x-form.checkbox
                        name="featured" label="Feature this project"
                        :checked="(bool) $project->featured"
                        help="Featured projects are shown first in the listing."
                    />
                </div>
            </div>
        </div>

        <div class="panel p-6">
            <h3 class="panel-title mb-5">Project image</h3>
            <x-form.image name="image" label="Upload an image" :current="$project->image_url" />
        </div>
    </div>
</div>
