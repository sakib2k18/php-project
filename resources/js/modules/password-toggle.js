import { $$ } from '../bootstrap';

const EYE = '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>';
const EYE_OFF = '<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243"/>';

/**
 * Show/hide toggle for password inputs.
 * <button data-password-toggle="password-field-id">
 */
export default function initPasswordToggles() {
    $$('[data-password-toggle]').forEach((button) => {
        const input = document.getElementById(button.dataset.passwordToggle);
        const icon = button.querySelector('svg');
        if (!input || !icon) return;

        button.addEventListener('click', () => {
            const revealed = input.type === 'text';

            input.type = revealed ? 'password' : 'text';
            icon.innerHTML = revealed ? EYE : EYE_OFF;
            button.setAttribute('aria-label', revealed ? 'Show password' : 'Hide password');
            button.setAttribute('aria-pressed', String(!revealed));
            input.focus();
        });
    });

    initStrengthMeters();
}

/** A simple strength hint on registration / password-change forms. */
function initStrengthMeters() {
    $$('[data-password-strength]').forEach((input) => {
        const meter = document.getElementById(input.dataset.passwordStrength);
        if (!meter) return;

        const bars = Array.from(meter.querySelectorAll('[data-bar]'));
        const label = meter.querySelector('[data-strength-label]');

        input.addEventListener('input', () => {
            const value = input.value;
            let score = 0;

            if (value.length >= 8) score++;
            if (/[a-z]/.test(value) && /[A-Z]/.test(value)) score++;
            if (/\d/.test(value)) score++;
            if (/[^A-Za-z0-9]/.test(value) || value.length >= 14) score++;

            const tones = ['bg-ink-200', 'bg-rose-400', 'bg-amber-400', 'bg-emerald-400', 'bg-emerald-500'];
            const words = ['Too short', 'Weak', 'Fair', 'Good', 'Strong'];

            bars.forEach((bar, index) => {
                bar.className = `h-1 flex-1 rounded-full transition-colors ${index < score ? tones[score] : 'bg-ink-200'}`;
            });

            if (label) label.textContent = value ? words[score] : '';
        });
    });
}
