import { $, $$ } from '../bootstrap';

/**
 * Checkbox dropdowns over a real <select multiple>.
 *
 * The native select remains the source of truth: the server renders the
 * current selection into it (old input, or a saved application), this mirrors
 * that into the panel on load, and every checkbox writes back to the matching
 * <option>. Without JavaScript the native control stays visible and the whole
 * thing still works — see the [data-multiselect-ready] rules in app.css.
 *
 *   <div data-multiselect>
 *     <select multiple data-multiselect-native> …
 *     <button data-multiselect-toggle data-rules="required"> …
 *     <div data-multiselect-panel> <input type="checkbox"> …
 */
export default function initMultiselects() {
    const roots = $$('[data-multiselect]');
    if (!roots.length) return;

    const closeAll = (except = null) => {
        roots.forEach((root) => {
            if (root === except) return;
            close(root);
        });
    };

    roots.forEach((root) => {
        const select = $('select[data-multiselect-native]', root);
        const toggle = $('[data-multiselect-toggle]', root);
        const panel = $('[data-multiselect-panel]', root);
        const summary = $('[data-multiselect-summary]', root);

        if (!select || !toggle || !panel || !summary) return;

        const boxes = $$('input[type="checkbox"]', panel);
        const placeholder = summary.dataset.placeholder || '';

        /**
         * The closed button is also the field the shared validation module
         * reads, so its value has to track the selection.
         */
        const refresh = () => {
            const chosen = Array.from(select.selectedOptions).map((option) => option.textContent.trim());

            toggle.value = chosen.join(',');

            if (!chosen.length) {
                summary.textContent = placeholder;
                summary.classList.add('text-ink-400');
            } else {
                summary.textContent = chosen.length <= 3 ? chosen.join(', ') : `${chosen.length} activities selected`;
                summary.classList.remove('text-ink-400');
            }
        };

        /** checkbox → its matching <option>. */
        const write = () => {
            const checked = new Set(boxes.filter((box) => box.checked).map((box) => box.value));
            Array.from(select.options).forEach((option) => {
                option.selected = checked.has(option.value);
            });

            refresh();

            // Let the validation module clear (or raise) its message straight
            // away rather than waiting for the button to lose focus.
            toggle.dispatchEvent(new Event('blur'));
        };

        // Load the server's selection into the panel without writing back —
        // the two already agree, and a stray change event would be noise.
        boxes.forEach((box) => {
            const option = Array.from(select.options).find((o) => o.value === box.value);
            box.checked = Boolean(option?.selected);
        });
        refresh();

        // Hands the control over from the native select to the dropdown.
        root.setAttribute('data-multiselect-ready', '');

        boxes.forEach((box) => box.addEventListener('change', write));

        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            const isOpen = !panel.hasAttribute('hidden');
            closeAll(root);
            panel.toggleAttribute('hidden', isOpen);
            toggle.setAttribute('aria-expanded', String(!isOpen));
        });

        // A click inside the panel (on a checkbox, or the gap between rows) must
        // not dismiss it — only a click genuinely outside the control does.
        panel.addEventListener('click', (event) => event.stopPropagation());
    });

    document.addEventListener('click', () => closeAll());
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;

        const open = roots.find((root) => {
            const panel = $('[data-multiselect-panel]', root);
            return panel && !panel.hasAttribute('hidden');
        });
        if (!open) return;

        close(open);
        $('[data-multiselect-toggle]', open)?.focus();
    });
}

function close(root) {
    $('[data-multiselect-panel]', root)?.setAttribute('hidden', '');
    $('[data-multiselect-toggle]', root)?.setAttribute('aria-expanded', 'false');
}
