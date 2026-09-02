<div class="grid gap-6 xl:grid-cols-[1fr_20rem]">

    <div class="space-y-6">
        <div class="panel p-6">
            <h3 class="panel-title mb-5">Event details</h3>

            <div class="space-y-5">
                <x-form.field
                    name="title" label="Event title" :required="true"
                    :value="$event->title"
                    placeholder="e.g. Free Medical Camp — Phultala"
                    rules="required|min:5|max:180"
                />

                <x-form.field
                    name="slug" label="URL slug"
                    :value="$event->slug"
                    placeholder="Leave empty to generate from the title"
                />

                <x-form.textarea
                    name="description" label="Description" :required="true"
                    :value="$event->description"
                    placeholder="What happens at this event, who it is for, and what to bring. Basic HTML is supported."
                    rules="required|min:30" :rows="10"
                />
            </div>
        </div>

        <div class="panel p-6">
            <h3 class="panel-title mb-5">When &amp; where</h3>

            <div class="grid gap-5 sm:grid-cols-3">
                <x-form.field
                    name="event_date" label="Date" type="date" :required="true"
                    :value="$event->event_date?->toDateString()" rules="required"
                />

                <x-form.field
                    name="start_time" label="Start time" type="time"
                    :value="$event->start_time ? substr($event->start_time, 0, 5) : null"
                />

                <x-form.field
                    name="end_time" label="End time" type="time"
                    :value="$event->end_time ? substr($event->end_time, 0, 5) : null"
                />
            </div>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <x-form.field
                    name="location" label="Location"
                    :value="$event->location" icon="map-pin"
                    placeholder="e.g. Phultala Union Parishad Ground, Khulna"
                    rules="max:180"
                />

                <x-form.field
                    name="organizer" label="Organiser"
                    :value="$event->organizer"
                    placeholder="e.g. KUET TRY Medical Wing"
                    rules="max:120"
                />
            </div>

            <div class="mt-5 grid gap-5 sm:grid-cols-3">
                <x-form.field
                    name="capacity" label="Capacity" type="number"
                    :value="$event->capacity" min="1" step="1"
                    placeholder="e.g. 120"
                    help="Optional"
                />

                <x-form.field
                    name="latitude" label="Latitude" type="number"
                    :value="$event->latitude" step="0.0000001" min="-90" max="90"
                    placeholder="22.8993100"
                />

                <x-form.field
                    name="longitude" label="Longitude" type="number"
                    :value="$event->longitude" step="0.0000001" min="-180" max="180"
                    placeholder="89.5022890"
                />
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="panel p-6">
            <h3 class="panel-title mb-5">Publishing</h3>

            <x-form.select
                name="status" label="Status" :required="true"
                :value="$event->status" :options="$statuses" rules="required"
                help="Only published events appear on the public site."
            />
        </div>

        <div class="panel p-6">
            <h3 class="panel-title mb-5">Event image</h3>
            <x-form.image name="image" label="Upload an image" :current="$event->image_url" />
        </div>
    </div>
</div>
