{{--
    Session flash messages.

    Rendered as hidden, escaped text nodes and picked up by resources/js/modules/toasts.js,
    which inserts them with textContent — so a message containing a user-supplied
    name can never become markup. The <noscript> block keeps the message visible
    when JavaScript is unavailable.
--}}
@foreach (['success', 'error', 'warning', 'info'] as $type)
    @if (session()->has($type))
        <span data-flash="{{ $type }}" hidden>{{ session($type) }}</span>

        <noscript>
            <div class="shell py-3">
                <div @class([
                    'rounded-xl border p-4 text-sm font-medium',
                    'border-emerald-200 bg-emerald-50 text-emerald-800' => $type === 'success',
                    'border-rose-200 bg-rose-50 text-rose-800' => $type === 'error',
                    'border-amber-200 bg-amber-50 text-amber-800' => $type === 'warning',
                    'border-sky-200 bg-sky-50 text-sky-800' => $type === 'info',
                ]) role="alert">{{ session($type) }}</div>
            </div>
        </noscript>
    @endif
@endforeach
