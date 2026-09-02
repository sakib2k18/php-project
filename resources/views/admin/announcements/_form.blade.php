<div class="grid gap-6 xl:grid-cols-[1fr_20rem]">

    <div class="panel p-6">
        <h3 class="panel-title mb-5">Announcement</h3>

        <div class="space-y-5">
            <x-form.field
                name="title" label="Title" :required="true"
                :value="$announcement->title"
                placeholder="e.g. Emergency appeal: coastal cyclone response is 40% funded"
                rules="required|min:5|max:180"
            />

            <x-form.textarea
                name="content" label="Message" :required="true"
                :value="$announcement->content"
                placeholder="Keep it short — this appears in a compact card on the homepage."
                rules="required|min:10|max:2000" :rows="5" :maxlength="2000"
            />

            <div class="grid gap-5 border-t border-ink-100 pt-5 sm:grid-cols-2">
                <x-form.field
                    name="link_url" label="Link URL"
                    :value="$announcement->link_url"
                    placeholder="https://…" rules="url"
                    help="Optional call to action."
                />

                <x-form.field
                    name="link_label" label="Link button label"
                    :value="$announcement->link_label"
                    placeholder="e.g. Support the appeal" rules="max:60"
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
                    :value="$announcement->status"
                    :options="['draft' => 'Draft', 'published' => 'Published']"
                    rules="required"
                />

                <x-form.select
                    name="priority" label="Priority" :required="true"
                    :value="$announcement->priority" :options="$priorities" rules="required"
                    help="High and Urgent are highlighted in amber and appear first."
                />

                <x-form.field
                    name="published_at" label="Publish from" type="datetime-local"
                    :value="$announcement->published_at?->format('Y-m-d\TH:i')"
                    help="Leave empty to publish immediately."
                />

                <x-form.field
                    name="expires_at" label="Expires on" type="datetime-local"
                    :value="$announcement->expires_at?->format('Y-m-d\TH:i')"
                    help="After this the announcement disappears automatically."
                />
            </div>
        </div>

        <div class="panel p-6">
            <h3 class="panel-title mb-5">Notify supporters</h3>

            <x-form.checkbox
                name="notify_users" label="Send a dashboard notification"
                help="Creates a database notification for every active supporter. Use sparingly — reserve it for genuinely important news."
            />
        </div>
    </div>
</div>
