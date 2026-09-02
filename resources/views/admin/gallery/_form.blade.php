<div class="grid gap-6 xl:grid-cols-[1fr_20rem]">

    <div class="panel p-6">
        <h3 class="panel-title mb-5">Image details</h3>

        <div class="space-y-5">
            <x-form.field
                name="title" label="Title" :required="true"
                :value="$item->title"
                placeholder="e.g. Family kits reaching Maheshwaripur"
                rules="required|min:3|max:150"
            />

            <x-form.textarea
                name="caption" label="Caption"
                :value="$item->caption"
                placeholder="One sentence of context — what is happening in this photograph."
                :rows="3" :maxlength="500"
            />

            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.select
                    name="category" label="Category" :required="true"
                    :value="$item->category" :options="$categories"
                    placeholder="Choose a category…" rules="required"
                />

                <x-form.field
                    name="taken_on" label="Date taken" type="date"
                    :value="$item->taken_on?->toDateString()"
                    :max="now()->toDateString()"
                    rules="notfuture"
                />
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="panel p-6">
            <h3 class="panel-title mb-5">Photograph</h3>
            <x-form.image name="image" label="Image file" :current="$item->image_url" aspect="aspect-[4/3]" />
        </div>

        <div class="panel p-6">
            <h3 class="panel-title mb-5">Display</h3>

            <div class="space-y-5">
                <x-form.field
                    name="sort_order" label="Sort order" type="number"
                    :value="$item->sort_order ?? 0" min="0" step="1"
                    help="Lower numbers appear first."
                />

                <x-form.checkbox
                    name="is_published" label="Visible in the public gallery"
                    :checked="(bool) ($item->is_published ?? true)"
                />
            </div>
        </div>
    </div>
</div>
