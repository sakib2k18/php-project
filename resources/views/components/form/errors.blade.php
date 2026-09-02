@if ($errors->any())
    <div
        {{ $attributes->merge(['class' => 'rounded-xl border border-rose-200 bg-rose-50 p-4']) }}
        role="alert"
        aria-live="assertive"
    >
        <div class="flex items-start gap-3">
            <x-ui.icon name="exclamation" class="mt-0.5 size-5 shrink-0 text-rose-600" />
            <div class="min-w-0 flex-1">
                <h2 class="text-sm font-bold text-rose-800">
                    {{ trans_choice('There is 1 problem with your submission.|There are :count problems with your submission.', $errors->count(), ['count' => $errors->count()]) }}
                </h2>
                <ul class="mt-1.5 space-y-1 text-sm text-rose-700">
                    @foreach ($errors->all() as $message)
                        <li class="flex gap-1.5"><span aria-hidden="true">·</span><span>{{ $message }}</span></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
