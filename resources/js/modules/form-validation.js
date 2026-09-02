import { $$ } from '../bootstrap';

/**
 * Progressive client-side validation.
 *
 * This exists purely for UX — every rule enforced here is also enforced by a
 * Laravel Form Request on the server. Disabling JavaScript loses the instant
 * feedback and nothing else.
 */
export default function initFormValidation() {
    $$('form[data-validate]').forEach(setupForm);
    $$('[data-char-count]').forEach(setupCounter);
}

function setupForm(form) {
    const fields = $$('[data-rules]', form);

    fields.forEach((field) => {
        // Validate on blur, then live once the field has been touched.
        field.addEventListener('blur', () => validateField(field));
        field.addEventListener('input', () => {
            if (field.dataset.touched === 'true') validateField(field);
        });
        field.addEventListener('blur', () => (field.dataset.touched = 'true'), { once: true });
    });

    form.addEventListener('submit', (event) => {
        const invalid = fields.filter((field) => !validateField(field));

        if (invalid.length) {
            event.preventDefault();
            invalid[0].focus();
            invalid[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            window.KT?.toast?.('Please correct the highlighted fields.', 'warning');
            return;
        }

        lockSubmit(form);
    });
}

/** Prevents an accidental double submission of a donation or a long form. */
function lockSubmit(form) {
    const button = form.querySelector('[type="submit"]');
    if (!button || button.dataset.noLock === 'true') return;

    const label = button.dataset.loadingLabel || 'Working…';

    button.setAttribute('aria-disabled', 'true');
    button.dataset.originalHtml = button.innerHTML;
    button.innerHTML = `<svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
            <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z"></path>
        </svg><span>${label}</span>`;

    // If the browser blocks navigation (validation elsewhere), restore the button.
    setTimeout(() => {
        if (button.dataset.originalHtml) {
            button.removeAttribute('aria-disabled');
            button.innerHTML = button.dataset.originalHtml;
            delete button.dataset.originalHtml;
        }
    }, 8000);
}

function validateField(field) {
    const rules = (field.dataset.rules || '').split('|').map((r) => r.trim()).filter(Boolean);
    const value = (field.value ?? '').trim();
    const label = field.dataset.label || field.getAttribute('aria-label') || 'This field';

    let message = null;

    for (const rule of rules) {
        const [name, argument] = rule.split(':');

        if (name === 'required' && value === '') {
            message = `${label} is required.`;
        } else if (value === '') {
            continue; // other rules only apply to filled fields
        } else if (name === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(value)) {
            message = 'Enter a valid email address.';
        } else if (name === 'min' && value.length < Number(argument)) {
            message = `${label} must be at least ${argument} characters.`;
        } else if (name === 'max' && value.length > Number(argument)) {
            message = `${label} may not exceed ${argument} characters.`;
        } else if (name === 'numeric' && Number.isNaN(Number(value))) {
            message = `${label} must be a number.`;
        } else if (name === 'minval' && Number(value) < Number(argument)) {
            message = `${label} must be at least ${Number(argument).toLocaleString()}.`;
        } else if (name === 'maxval' && Number(value) > Number(argument)) {
            message = `${label} may not exceed ${Number(argument).toLocaleString()}.`;
        } else if (name === 'phone' && !/^[0-9+\-\s()]{6,30}$/.test(value)) {
            message = 'Enter a valid phone number.';
        } else if (name === 'url' && !/^https?:\/\/.+/i.test(value)) {
            message = 'Enter a full URL starting with http:// or https://';
        } else if (name === 'match') {
            const other = document.getElementById(argument);
            if (other && other.value !== field.value) message = 'The two values do not match.';
        } else if (name === 'notfuture' && new Date(value) > new Date(new Date().toDateString() + ' 23:59:59')) {
            message = `${label} cannot be in the future.`;
        } else if (name === 'accepted' && !field.checked) {
            message = 'Please tick this box to continue.';
        }

        if (message) break;
    }

    // Checkboxes carry their own required semantics.
    if (field.type === 'checkbox' && rules.includes('required') && !field.checked) {
        message = 'Please tick this box to continue.';
    }

    render(field, message);

    return message === null;
}

function render(field, message) {
    const slot = document.getElementById(`${field.id}-error`);

    if (message) {
        field.setAttribute('aria-invalid', 'true');
        field.classList.add('field-error');
        if (slot) {
            slot.textContent = message;
            slot.removeAttribute('hidden');
        }
    } else {
        field.removeAttribute('aria-invalid');
        field.classList.remove('field-error');
        // Never wipe a server-rendered error until the user actually fixes it.
        if (slot && slot.dataset.server !== 'true') {
            slot.textContent = '';
            slot.setAttribute('hidden', '');
        }
        if (slot && slot.dataset.server === 'true') {
            slot.dataset.server = 'false';
            slot.textContent = '';
            slot.setAttribute('hidden', '');
        }
    }
}

/** Live "142 / 500" counters under textareas. */
function setupCounter(field) {
    const output = document.getElementById(field.dataset.charCount);
    if (!output) return;

    const max = Number(field.getAttribute('maxlength')) || Number(field.dataset.charMax) || 0;

    const update = () => {
        const length = field.value.length;
        output.textContent = max ? `${length} / ${max}` : String(length);
        output.classList.toggle('text-rose-600', max > 0 && length > max * 0.95);
    };

    field.addEventListener('input', update);
    update();
}
