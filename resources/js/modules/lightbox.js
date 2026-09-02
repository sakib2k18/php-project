import { $, $$ } from '../bootstrap';

/**
 * Gallery lightbox — keyboard navigable, focus trapped, no dependencies.
 *
 * Markup: <button data-lightbox data-lightbox-src="…" data-lightbox-title="…"
 *                 data-lightbox-caption="…">
 */
export default function initLightbox() {
    const triggers = $$('[data-lightbox]');
    if (!triggers.length) return;

    const ui = build();
    let index = 0;

    const show = (i) => {
        index = (i + triggers.length) % triggers.length;
        const trigger = triggers[index];

        ui.image.src = trigger.dataset.lightboxSrc;
        ui.image.alt = trigger.dataset.lightboxTitle || '';
        ui.title.textContent = trigger.dataset.lightboxTitle || '';
        ui.caption.textContent = trigger.dataset.lightboxCaption || '';
        ui.counter.textContent = `${index + 1} / ${triggers.length}`;
        ui.caption.toggleAttribute('hidden', !trigger.dataset.lightboxCaption);
    };

    const open = (i) => {
        show(i);
        ui.root.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';
        ui.close.focus();
    };

    const close = () => {
        ui.root.setAttribute('hidden', '');
        document.body.style.overflow = '';
        ui.image.src = '';
        triggers[index]?.focus();
    };

    triggers.forEach((trigger, i) => trigger.addEventListener('click', () => open(i)));

    ui.close.addEventListener('click', close);
    ui.backdrop.addEventListener('click', close);
    ui.prev.addEventListener('click', () => show(index - 1));
    ui.next.addEventListener('click', () => show(index + 1));

    document.addEventListener('keydown', (event) => {
        if (ui.root.hasAttribute('hidden')) return;

        if (event.key === 'Escape') close();
        if (event.key === 'ArrowRight') show(index + 1);
        if (event.key === 'ArrowLeft') show(index - 1);
        // Keep focus inside the dialog.
        if (event.key === 'Tab') {
            const focusable = $$('button', ui.root);
            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    // Swipe on touch devices.
    let startX = null;
    ui.root.addEventListener('touchstart', (e) => (startX = e.touches[0].clientX), { passive: true });
    ui.root.addEventListener('touchend', (e) => {
        if (startX === null) return;
        const delta = e.changedTouches[0].clientX - startX;
        if (Math.abs(delta) > 60) show(index + (delta < 0 ? 1 : -1));
        startX = null;
    });
}

function build() {
    const root = document.createElement('div');
    root.className = 'fixed inset-0 z-[80] flex flex-col';
    root.setAttribute('role', 'dialog');
    root.setAttribute('aria-modal', 'true');
    root.setAttribute('aria-label', 'Photo viewer');
    root.setAttribute('hidden', '');

    root.innerHTML = `
        <div data-backdrop class="absolute inset-0 bg-ink-950/92"></div>

        <div class="relative flex items-center justify-between gap-4 p-4 text-white/80">
            <span data-counter class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold tabular-nums"></span>
            <button type="button" data-close class="rounded-lg p-2 transition hover:bg-white/10 hover:text-white" aria-label="Close viewer">
                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="relative flex min-h-0 flex-1 items-center justify-center px-2 sm:px-16">
            <button type="button" data-prev class="absolute left-1 z-10 grid size-11 place-items-center rounded-full bg-white/10 text-white transition hover:bg-white/20 sm:left-4" aria-label="Previous photo">
                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                </svg>
            </button>

            <img data-image src="" alt="" class="max-h-full max-w-full rounded-xl object-contain shadow-2xl">

            <button type="button" data-next class="absolute right-1 z-10 grid size-11 place-items-center rounded-full bg-white/10 text-white transition hover:bg-white/20 sm:right-4" aria-label="Next photo">
                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                </svg>
            </button>
        </div>

        <div class="relative mx-auto w-full max-w-3xl p-5 text-center">
            <h2 data-title class="text-base font-semibold text-white"></h2>
            <p data-caption class="mt-1 text-sm leading-relaxed text-white/70"></p>
        </div>
    `;

    document.body.appendChild(root);

    return {
        root,
        backdrop: $('[data-backdrop]', root),
        image: $('[data-image]', root),
        title: $('[data-title]', root),
        caption: $('[data-caption]', root),
        counter: $('[data-counter]', root),
        close: $('[data-close]', root),
        prev: $('[data-prev]', root),
        next: $('[data-next]', root),
    };
}
