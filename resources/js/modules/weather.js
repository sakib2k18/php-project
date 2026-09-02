import { $ } from '../bootstrap';

/**
 * Weather widget refresh.
 *
 * The browser never calls Open-Meteo directly — it asks our own
 * /api/weather endpoint, which is rate limited, cached and degrades to a
 * friendly message when the provider is unreachable.
 */
export default function initWeather() {
    const widget = $('[data-weather]');
    if (!widget) return;

    const button = widget.querySelector('[data-weather-refresh]');
    const body = widget.querySelector('[data-weather-body]');
    const skeleton = widget.querySelector('[data-weather-skeleton]');
    const stamp = widget.querySelector('[data-weather-stamp]');

    if (!button) return;

    button.addEventListener('click', async () => {
        button.setAttribute('aria-disabled', 'true');
        button.querySelector('svg')?.classList.add('animate-spin');
        skeleton?.removeAttribute('hidden');
        body?.setAttribute('hidden', '');

        try {
            const response = await fetch(`${button.dataset.weatherRefresh}?refresh=1`, {
                headers: { Accept: 'application/json' },
            });

            const payload = await response.json();

            if (!payload.ok) {
                window.KT?.toast?.(payload.message || 'Weather data is temporarily unavailable.', 'warning');
            } else {
                paint(widget, payload.data);
                if (stamp) stamp.textContent = 'Updated just now';
                window.KT?.toast?.('Weather updated.', 'success');
            }
        } catch {
            window.KT?.toast?.('Weather data is temporarily unavailable.', 'warning');
        } finally {
            button.removeAttribute('aria-disabled');
            button.querySelector('svg')?.classList.remove('animate-spin');
            skeleton?.setAttribute('hidden', '');
            body?.removeAttribute('hidden');
        }
    });
}

/** Writes the refreshed values into the existing markup. */
function paint(widget, data) {
    const set = (selector, value) => {
        const node = widget.querySelector(selector);
        if (node) node.textContent = value;
    };

    set('[data-weather-temp]', `${data.temperature}°`);
    set('[data-weather-condition]', data.condition);
    set('[data-weather-feels]', `${data.feels_like}°C`);
    set('[data-weather-humidity]', `${data.humidity}%`);
    set('[data-weather-wind]', `${data.wind_speed} km/h`);

    widget.querySelectorAll('[data-weather-day]').forEach((node, index) => {
        const day = data.daily?.[index];
        if (!day) return;

        node.querySelector('[data-day-label]').textContent = day.label;
        node.querySelector('[data-day-max]').textContent = `${day.max}°`;
        node.querySelector('[data-day-min]').textContent = `${day.min}°`;
    });
}
