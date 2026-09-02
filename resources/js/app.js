/**
 * KUET TRY — front-end entry point.
 *
 * Every behaviour lives in its own module under resources/js/modules and is
 * initialised here. Blade templates contain markup and `data-*` hooks only;
 * there are no inline <script> blocks with application logic anywhere.
 */

import './bootstrap';

import initNavigation from './modules/navigation';
import initToasts from './modules/toasts';
import initFormValidation from './modules/form-validation';
import initPasswordToggles from './modules/password-toggle';
import initConfirmations from './modules/confirm';
import initImagePreviews from './modules/image-preview';
import initProgressBars from './modules/progress';
import initReveal from './modules/reveal';
import initCounters from './modules/counters';
import initLightbox from './modules/lightbox';
import initFilters from './modules/filters';
import initWeather from './modules/weather';
import initMaps from './modules/maps';
import initCharts from './modules/charts';
import initCookieConsent from './modules/cookie-consent';
import initClipboard from './modules/clipboard';

const boot = () => {
    initNavigation();
    initToasts();
    initFormValidation();
    initPasswordToggles();
    initConfirmations();
    initImagePreviews();
    initProgressBars();
    initReveal();
    initCounters();
    initLightbox();
    initFilters();
    initWeather();
    initMaps();
    initCharts();
    initCookieConsent();
    initClipboard();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot, { once: true });
} else {
    boot();
}
