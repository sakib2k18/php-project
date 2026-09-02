import { $, $$ } from '../bootstrap';

/**
 * Mobile menu, admin off-canvas sidebar, dropdown menus and the sticky header.
 * Everything is keyboard accessible and closes on Escape / outside click.
 */
export default function initNavigation() {
    setupPanel('mobile-nav');
    setupPanel('admin-sidebar');
    setupDropdowns();
    setupStickyHeader();
    setupSubmenus();
}

/**
 * A slide-in panel driven by data attributes:
 *   <button data-panel-toggle="mobile-nav">
 *   <div data-panel="mobile-nav"> … <div data-panel-backdrop>
 */
function setupPanel(name) {
    const panel = $(`[data-panel="${name}"]`);
    if (!panel) return;

    const toggles = $$(`[data-panel-toggle="${name}"]`);
    const closers = $$(`[data-panel-close="${name}"]`, panel);
    const backdrop = $(`[data-panel-backdrop="${name}"]`);

    const open = () => {
        panel.dataset.open = 'true';
        backdrop?.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';
        toggles.forEach((t) => t.setAttribute('aria-expanded', 'true'));
        // Move focus into the panel for screen-reader and keyboard users.
        panel.querySelector('a, button')?.focus();
    };

    const close = () => {
        delete panel.dataset.open;
        backdrop?.setAttribute('hidden', '');
        document.body.style.overflow = '';
        toggles.forEach((t) => t.setAttribute('aria-expanded', 'false'));
    };

    const toggle = () => (panel.dataset.open === 'true' ? close() : open());

    toggles.forEach((t) => t.addEventListener('click', toggle));
    closers.forEach((c) => c.addEventListener('click', close));
    backdrop?.addEventListener('click', close);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && panel.dataset.open === 'true') close();
    });

    // A resize to desktop should never leave the body scroll-locked.
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024 && panel.dataset.open === 'true') close();
    });
}

/** <button data-dropdown-toggle="id"> … <div data-dropdown="id"> */
function setupDropdowns() {
    const toggles = $$('[data-dropdown-toggle]');

    const closeAll = (except = null) => {
        $$('[data-dropdown]').forEach((menu) => {
            if (menu === except) return;
            menu.setAttribute('hidden', '');
            $(`[data-dropdown-toggle="${menu.dataset.dropdown}"]`)?.setAttribute('aria-expanded', 'false');
        });
    };

    toggles.forEach((toggle) => {
        const menu = $(`[data-dropdown="${toggle.dataset.dropdownToggle}"]`);
        if (!menu) return;

        toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = !menu.hasAttribute('hidden');
            closeAll();
            if (!isOpen) {
                menu.removeAttribute('hidden');
                toggle.setAttribute('aria-expanded', 'true');
            }
        });
    });

    document.addEventListener('click', () => closeAll());
    document.addEventListener('keydown', (e) => e.key === 'Escape' && closeAll());
}

/** Collapsible groups inside the mobile navigation. */
function setupSubmenus() {
    $$('[data-submenu-toggle]').forEach((button) => {
        const target = $(`[data-submenu="${button.dataset.submenuToggle}"]`);
        if (!target) return;

        button.addEventListener('click', () => {
            const open = target.hasAttribute('hidden');
            target.toggleAttribute('hidden', !open);
            button.setAttribute('aria-expanded', String(open));
            button.querySelector('[data-chevron]')?.classList.toggle('rotate-180', open);
        });
    });
}

/** Adds a shadow + solid background to the header once the page is scrolled. */
function setupStickyHeader() {
    const header = $('[data-sticky-header]');
    if (!header) return;

    const update = () => header.toggleAttribute('data-scrolled', window.scrollY > 12);

    update();
    window.addEventListener('scroll', update, { passive: true });
}
