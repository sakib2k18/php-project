import { $$ } from '../bootstrap';
import whenVisible from './visibility';

/** Gentle fade-up as sections enter the viewport. */
export default function initReveal() {
    const items = $$('.reveal');
    if (!items.length) return;

    const show = (element) => {
        element.style.opacity = '1';
        element.classList.remove('reveal');
    };

    // Respect the visitor's motion preference: show everything immediately.
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        items.forEach(show);
        return;
    }

    let index = 0;

    whenVisible(items, (element) => {
        element.style.animationDelay = `${Math.min((index % 6) * 70, 280)}ms`;
        element.classList.add('is-visible');
        index++;
    });
}
