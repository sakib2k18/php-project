import { $$ } from '../bootstrap';
import whenVisible from './visibility';

/**
 * Animates campaign progress bars from 0 to their real value once they scroll
 * into view. The value itself always comes from the server.
 */
export default function initProgressBars() {
    const bars = $$('[data-progress]');
    if (!bars.length) return;

    whenVisible(bars, (bar) => {
        const percent = Math.min(100, Math.max(0, Number(bar.dataset.progress) || 0));

        // Set the width directly rather than inside requestAnimationFrame: rAF
        // is paused in a background tab, which would leave the bar empty. The
        // CSS transition on .progress-fill still produces the sweep.
        bar.style.width = `${percent}%`;
    });
}
