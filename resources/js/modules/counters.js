import { $$ } from '../bootstrap';
import whenVisible from './visibility';

/**
 * Counts the homepage impact statistics up to the value rendered by Blade.
 * data-count-to holds the real database figure; data-count-prefix/suffix
 * carry the currency symbol or a "+".
 */
export default function initCounters() {
    const counters = $$('[data-count-to]');
    if (!counters.length) return;

    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const run = (element) => {
        const target = Number(element.dataset.countTo) || 0;
        const prefix = element.dataset.countPrefix || '';
        const suffix = element.dataset.countSuffix || '';
        const compact = element.dataset.countCompact === 'true';

        const format = (value) => {
            if (compact) {
                if (Math.abs(value) >= 1_000_000) return `${(value / 1_000_000).toFixed(1).replace(/\.0$/, '')}M`;
                if (Math.abs(value) >= 1_000) return `${(value / 1_000).toFixed(1).replace(/\.0$/, '')}K`;
            }
            return Math.round(value).toLocaleString();
        };

        const finalText = prefix + format(target) + suffix;

        if (reduced || target === 0) {
            element.textContent = finalText;
            return;
        }

        // Write the real figure first. requestAnimationFrame is paused in a
        // background tab, so if the animation never runs the correct number is
        // already on screen rather than a stranded "0".
        element.textContent = finalText;

        const duration = 1400;
        const start = performance.now();

        const step = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            // easeOutCubic
            const eased = 1 - Math.pow(1 - progress, 3);
            element.textContent = prefix + format(target * eased) + suffix;
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                element.textContent = finalText;
            }
        };

        requestAnimationFrame(step);
    };

    whenVisible(counters, run, { threshold: 0.3 });
}
