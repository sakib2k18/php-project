<x-error-page
    code="403"
    title="You do not have access to this"
    description="This area is restricted. The administration panel is limited to the KUET TRY administrator, and donation records are visible only to the supporter who created them."
    icon="shield-check"
>
    @auth
        <p class="mx-auto mt-5 max-w-sm rounded-xl border border-white/15 bg-white/10 p-4 text-sm text-white/70 backdrop-blur">
            You are signed in as <span class="font-semibold text-white">{{ auth()->user()->name }}</span>.
            If you believe you should have access, please get in touch.
        </p>
    @endauth
</x-error-page>
