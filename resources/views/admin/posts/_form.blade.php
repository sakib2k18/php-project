<div class="grid gap-6 xl:grid-cols-[1fr_20rem]">

    <div class="space-y-6">
        <div class="panel p-6">
            <h3 class="panel-title mb-5">Article</h3>

            <div class="space-y-5">
                <x-form.field
                    name="title" label="Title" :required="true"
                    :value="$post->title"
                    placeholder="e.g. Cyclone Response: What Your Donations Delivered"
                    rules="required|min:5|max:180"
                />

                <x-form.field
                    name="slug" label="URL slug"
                    :value="$post->slug"
                    placeholder="Leave empty to generate from the title"
                />

                <x-form.textarea
                    name="excerpt" label="Excerpt" :required="true"
                    :value="$post->excerpt"
                    placeholder="A short summary shown on the article card and in search results."
                    rules="required|min:20|max:500" :rows="3" :maxlength="500"
                />

                <x-form.textarea
                    name="content" label="Content" :required="true"
                    :value="$post->content"
                    placeholder="The full article. Wrap paragraphs in <p> tags; <strong>, <em>, <ul> and <blockquote> are supported."
                    rules="required|min:80" :rows="18"
                    help="Reports read better with concrete figures: what was bought, at what price, for how many households."
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
                    :value="$post->status" :options="$statuses" rules="required"
                />

                <x-form.select
                    name="category" label="Category" :required="true"
                    :value="$post->category" :options="$categories" rules="required"
                />

                <x-form.field
                    name="author" label="Author" :required="true"
                    :value="$post->author"
                    placeholder="e.g. Field Operations Team"
                    rules="required|max:120"
                />

                <x-form.field
                    name="published_at" label="Publish date &amp; time" type="datetime-local"
                    :value="$post->published_at?->format('Y-m-d\TH:i')"
                    help="Leave empty and it will be stamped when you publish."
                />

                <div class="border-t border-ink-100 pt-5">
                    <x-form.checkbox
                        name="featured" label="Feature as the lead article"
                        :checked="(bool) $post->featured"
                        help="The lead article gets the large card at the top of the news page."
                    />
                </div>
            </div>
        </div>

        <div class="panel p-6">
            <h3 class="panel-title mb-5">Cover image</h3>
            <x-form.image name="cover_image" label="Upload a cover image" :current="$post->image_url" />
        </div>
    </div>
</div>
