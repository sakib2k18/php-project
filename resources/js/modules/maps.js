import { $$ } from '../bootstrap';
import whenVisible from './visibility';

/**
 * OpenStreetMap via Leaflet.
 *
 * Coordinates are read from `data-*` attributes that Blade renders from the
 * database — no geocoding request is made on page load, which keeps us well
 * inside the Nominatim usage policy. Attribution is always displayed.
 * Leaflet is loaded lazily so the library is only downloaded on pages with a map.
 */
export default function initMaps() {
    const containers = $$('[data-map]');
    if (!containers.length) return;

    whenVisible(containers, render, { margin: 200, threshold: 0, failsafeMs: 4000 });
}

async function render(container) {
    let L;

    try {
        // Dynamic import: Leaflet only ships to pages that actually show a map.
        [{ default: L }] = await Promise.all([
            import('leaflet'),
            import('leaflet/dist/leaflet.css'),
        ]);
    } catch {
        fallback(container);
        return;
    }

    const lat = Number(container.dataset.mapLat);
    const lng = Number(container.dataset.mapLng);

    if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
        fallback(container);
        return;
    }

    try {
        const map = L.map(container, {
            center: [lat, lng],
            zoom: Number(container.dataset.mapZoom) || 14,
            scrollWheelZoom: false,
            attributionControl: true,
        });

        L.tileLayer(container.dataset.mapTiles, {
            maxZoom: Number(container.dataset.mapMaxZoom) || 19,
            attribution: container.dataset.mapAttribution,
        }).addTo(map);

        // Points come from the database, serialised into data-map-points.
        const points = safeParse(container.dataset.mapPoints) || [{ lat, lng, title: container.dataset.mapTitle }];
        const markers = [];

        points.forEach((point) => {
            if (!Number.isFinite(Number(point.lat)) || !Number.isFinite(Number(point.lng))) return;

            const marker = L.marker([Number(point.lat), Number(point.lng)], {
                icon: pinIcon(L),
                title: point.title || '',
            }).addTo(map);

            if (point.title) {
                marker.bindPopup(popupHtml(point));
            }

            markers.push(marker);
        });

        if (markers.length > 1) {
            map.fitBounds(L.featureGroup(markers).getBounds().pad(0.25));
        }

        // Click to enable scroll zoom — avoids hijacking the page scroll.
        map.on('click', () => map.scrollWheelZoom.enable());
        map.on('mouseout', () => map.scrollWheelZoom.disable());

        container.querySelector('[data-map-loading]')?.remove();
    } catch {
        fallback(container);
    }
}

function pinIcon(L) {
    return L.divIcon({
        className: '',
        html: `<span style="display:grid;place-items:center;width:34px;height:34px;border-radius:9999px;background:#0f7d5a;box-shadow:0 6px 16px -4px rgba(12,66,52,.5);border:3px solid #fff;">
                 <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                   <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/>
                 </svg>
               </span>`,
        iconSize: [34, 34],
        iconAnchor: [17, 17],
        popupAnchor: [0, -16],
    });
}

function popupHtml(point) {
    const escape = (value) =>
        String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    const link = point.url
        ? `<a href="${escape(point.url)}" style="color:#0f7d5a;font-weight:600;text-decoration:underline;">View details</a>`
        : '';

    return `<strong style="display:block;color:#1b201e;">${escape(point.title)}</strong>
            ${point.subtitle ? `<span style="color:#545e5a;">${escape(point.subtitle)}</span><br>` : ''}
            ${link}`;
}

function safeParse(value) {
    try {
        return value ? JSON.parse(value) : null;
    } catch {
        return null;
    }
}

/** If the tiles or the library cannot load, show an address card instead. */
function fallback(container) {
    container.innerHTML = `
        <div class="flex h-full flex-col items-center justify-center gap-2 rounded-2xl bg-ink-50 p-6 text-center">
            <svg class="size-8 text-ink-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
            </svg>
            <p class="text-sm font-medium text-ink-600">Map temporarily unavailable</p>
            <p class="max-w-xs text-xs text-ink-500">${container.dataset.mapTitle || ''}</p>
        </div>
    `;
}
