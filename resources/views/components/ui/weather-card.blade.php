@props(['weather' => null])

<div
    data-weather
    {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-800 via-brand-700 to-brand-900 p-6 text-white shadow-[var(--shadow-lift)]']) }}
>
    <div class="grain pointer-events-none absolute inset-0 opacity-70"></div>

    <div class="relative">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-white/60">Local conditions</p>
                <p class="mt-1 flex items-center gap-1.5 text-sm font-semibold text-white/90">
                    <x-ui.icon name="map-pin" class="size-4 text-white/60" />
                    {{ config('site.location.city') }}, {{ config('site.location.country') }}
                </p>
            </div>

            <button
                type="button"
                data-weather-refresh="{{ route('api.weather') }}"
                class="rounded-lg p-2 text-white/70 transition hover:bg-white/10 hover:text-white"
                aria-label="Refresh weather"
            >
                <x-ui.icon name="refresh" class="size-4" />
            </button>
        </div>

        {{-- Loading skeleton, revealed while a refresh is in flight. --}}
        <div data-weather-skeleton hidden class="mt-6 space-y-3">
            <div class="skeleton h-12 w-32 !bg-white/15"></div>
            <div class="skeleton h-4 w-40 !bg-white/10"></div>
        </div>

        @if ($weather)
            <div data-weather-body class="mt-5">
                <div class="flex items-center gap-4">
                    <x-ui.icon :name="$weather['icon']" class="size-12 shrink-0 text-accent-300" />
                    <div>
                        <p class="display text-5xl leading-none">
                            <span data-weather-temp>{{ $weather['temperature'] }}°</span>
                        </p>
                        <p data-weather-condition class="mt-1 text-sm font-semibold text-white/85">{{ $weather['condition'] }}</p>
                    </div>
                </div>

                <dl class="mt-5 grid grid-cols-3 gap-3 border-t border-white/15 pt-4 text-center">
                    <div>
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-white/50">Feels like</dt>
                        <dd data-weather-feels class="mt-0.5 text-sm font-bold">{{ $weather['feels_like'] }}°C</dd>
                    </div>
                    <div class="border-x border-white/15">
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-white/50">Humidity</dt>
                        <dd data-weather-humidity class="mt-0.5 text-sm font-bold">{{ $weather['humidity'] }}%</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-white/50">Wind</dt>
                        <dd data-weather-wind class="mt-0.5 text-sm font-bold">{{ $weather['wind_speed'] }} km/h</dd>
                    </div>
                </dl>

                @if (! empty($weather['daily']))
                    <div class="mt-4 grid grid-cols-3 gap-2">
                        @foreach ($weather['daily'] as $day)
                            <div data-weather-day class="rounded-xl bg-white/10 p-2.5 text-center">
                                <p data-day-label class="text-[10px] font-bold uppercase tracking-wider text-white/60">{{ $day['label'] }}</p>
                                <x-ui.icon :name="$day['icon']" class="mx-auto my-1.5 size-5 text-white/80" />
                                <p class="text-xs font-bold">
                                    <span data-day-max>{{ $day['max'] }}°</span>
                                    <span class="font-medium text-white/50"> / <span data-day-min>{{ $day['min'] }}°</span></span>
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif

                <p class="mt-4 flex items-center justify-between text-[10px] text-white/45">
                    <span>{{ $weather['attribution'] }}</span>
                    <span data-weather-stamp>Updated {{ \Illuminate\Support\Carbon::parse($weather['fetched_at'])->diffForHumans() }}</span>
                </p>
            </div>
        @else
            {{-- Graceful fallback: the API failed, the page keeps working. --}}
            <div data-weather-body class="mt-6 rounded-xl border border-white/15 bg-white/5 p-5 text-center">
                <x-ui.icon name="cloud" class="mx-auto size-8 text-white/40" />
                <p class="mt-2.5 text-sm font-semibold text-white/85">Weather data temporarily unavailable</p>
                <p class="mt-1 text-xs leading-relaxed text-white/55">
                    We could not reach the weather service. Everything else on this page works as normal —
                    try the refresh button in a moment.
                </p>
            </div>
        @endif
    </div>
</div>
