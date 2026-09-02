{{--
    Shared field set for the volunteer application, used by both the create and
    the edit form so the two can never drift apart.
--}}

<div class="grid gap-5 sm:grid-cols-2">
    <x-form.field
        name="name" label="Full name" :required="true"
        :value="$volunteer->name ?? auth()->user()->name" icon="user"
        rules="required|min:3|max:120" autocomplete="name"
    />

    <x-form.field
        name="email" label="Email address" type="email" :required="true"
        :value="$volunteer->email ?? auth()->user()->email" icon="mail"
        rules="required|email|max:150" autocomplete="email"
    />
</div>

<div class="grid gap-5 sm:grid-cols-2">
    <x-form.field
        name="phone" label="Phone number" type="tel" :required="true"
        :value="$volunteer->phone ?? auth()->user()->phone" icon="phone"
        rules="required|phone" autocomplete="tel"
        help="We coordinate field teams by phone, so this one is required."
    />

    <x-form.field
        name="student_id" label="Student / organisation ID"
        :value="$volunteer->student_id ?? auth()->user()->student_id"
        placeholder="e.g. 1807042"
        help="Optional"
    />
</div>

<div class="grid gap-5 sm:grid-cols-2">
    <x-form.field
        name="institution" label="Institution or employer"
        :value="$volunteer->institution ?? 'Khulna University of Engineering & Technology'"
        rules="max:150"
    />

    <x-form.field
        name="address" label="Address"
        :value="$volunteer->address ?? auth()->user()->address" icon="map-pin"
        rules="max:255" autocomplete="street-address"
    />
</div>

<div class="grid gap-5 sm:grid-cols-2">
    <x-form.select
        name="availability" label="When are you usually free?" :required="true"
        :value="$volunteer->availability ?? null"
        :options="$availability" placeholder="Choose your availability…"
        rules="required"
    />

    <x-form.select
        name="preferred_activity" label="What would you like to do?" :required="true"
        :value="$volunteer->preferred_activity ?? null"
        :options="collect($activities)->mapWithKeys(fn ($a) => [$a => $a])->all()"
        placeholder="Choose an activity…"
        rules="required"
    />
</div>

<x-form.textarea
    name="skills" label="Skills you can bring" :required="true"
    :value="$volunteer->skills ?? null"
    placeholder="e.g. First aid, driving (light vehicle licence), photography, teaching mathematics, spreadsheet work…"
    rules="required|min:5|max:500" :rows="3" :maxlength="500"
    help="Be honest — “willing to carry things and learn” is a perfectly good answer."
/>

<x-form.textarea
    name="motivation" label="Why do you want to volunteer?" :required="true"
    :value="$volunteer->motivation ?? null"
    placeholder="A few sentences about what brought you here."
    rules="required|min:30|max:1500" :rows="5" :maxlength="1500"
/>
