import { $ } from '../bootstrap';

/**
 * Cookie notice.
 *
 * The site only sets cookies it genuinely needs (session, CSRF, remember-me and
 * the "recently viewed campaigns" convenience cookie), so this is a plain
 * acknowledgement rather than a tracking opt-in. The choice itself is stored in
 * localStorage so it does not add another cookie.
 */
const KEY = 'kt.cookie-notice.v1';

export default function initCookieConsent() {
    const banner = $('[data-cookie-notice]');
    if (!banner) return;

    let acknowledged = false;
    try {
        acknowledged = window.localStorage.getItem(KEY) === 'ack';
    } catch {
        // Private mode / storage disabled — just show the notice.
    }

    if (acknowledged) return;

    // Delay slightly so it does not fight with the hero for attention.
    setTimeout(() => banner.removeAttribute('hidden'), 900);

    banner.querySelector('[data-cookie-accept]')?.addEventListener('click', () => {
        try {
            window.localStorage.setItem(KEY, 'ack');
        } catch {
            /* ignore */
        }
        banner.setAttribute('hidden', '');
    });
}
