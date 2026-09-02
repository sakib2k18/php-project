import { $$ } from '../bootstrap';

/**
 * Filter/search UX helpers for the public listings and the admin tables.
 * The filtering itself is done server-side by Eloquent — this only submits
 * the form at the right moment and keeps the controls feeling instant.
 */
export default function initFilters() {
    // A <select> or checkbox inside a filter form submits it on change.
    $$('[data-filter-form]').forEach((form) => {
        $$('select, input[type="checkbox"], input[type="date"]', form).forEach((control) => {
            control.addEventListener('change', () => {
                showBusy(form);
                form.submit();
            });
        });

        // Debounced search: 450ms after the visitor stops typing.
        const search = form.querySelector('input[type="search"], input[data-filter-search]');
        if (search) {
            let timer;
            search.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    showBusy(form);
                    form.submit();
                }, 450);
            });
        }
    });

    // "Clear all" resets every control and reloads the clean URL.
    $$('[data-filter-reset]').forEach((button) => {
        button.addEventListener('click', () => {
            window.location.href = button.dataset.filterReset;
        });
    });
}

function showBusy(form) {
    form.setAttribute('aria-busy', 'true');
    form.querySelectorAll('[data-filter-busy]').forEach((el) => el.removeAttribute('hidden'));
}
