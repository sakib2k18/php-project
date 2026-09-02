/**
 * Run a callback once, when an element first becomes visible.
 *
 * IntersectionObserver is the primary mechanism, but it is deliberately not the
 * only one. Several of the things that depend on it — the `.reveal` fade-in in
 * particular — start at `opacity: 0`, so a browser (or an embedded webview)
 * where the observer never fires would leave content permanently invisible.
 *
 * Three layers, each idempotent because `seen` guarantees one call per element:
 *   1. IntersectionObserver, when it is available.
 *   2. A throttled scroll/resize check.
 *   3. A hard timeout that runs everything still pending.
 *
 * @param {Element[]} elements
 * @param {(el: Element) => void} onVisible
 * @param {{ margin?: number, threshold?: number, failsafeMs?: number }} options
 */
export default function whenVisible(elements, onVisible, options = {}) {
    const { margin = 120, threshold = 0.15, failsafeMs = 2500 } = options;

    if (!elements.length) return;

    const pending = new Set(elements);

    const activate = (element) => {
        if (!pending.has(element)) return;
        pending.delete(element);
        onVisible(element);

        if (pending.size === 0) teardown();
    };

    const isNearViewport = (element) => {
        const rect = element.getBoundingClientRect();
        return rect.top < window.innerHeight + margin && rect.bottom > -margin;
    };

    const check = () => {
        // Copy first: activate() mutates the set.
        [...pending].forEach((element) => {
            if (isNearViewport(element)) activate(element);
        });
    };

    let ticking = false;
    const onScroll = () => {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(() => {
            ticking = false;
            check();
        });
    };

    let observer = null;
    let failsafe = null;

    const teardown = () => {
        observer?.disconnect();
        window.removeEventListener('scroll', onScroll);
        window.removeEventListener('resize', onScroll);
        clearTimeout(failsafe);
    };

    if ('IntersectionObserver' in window) {
        observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) activate(entry.target);
                });
            },
            { threshold, rootMargin: `${margin}px 0px ${margin}px 0px` }
        );

        elements.forEach((element) => observer.observe(element));
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });

    // Anything already on screen should not wait for a scroll event.
    check();

    // Last resort: never leave an element stuck in its hidden starting state.
    failsafe = setTimeout(() => {
        [...pending].forEach(activate);
    }, failsafeMs);
}
