import { $, $$ } from '../bootstrap';

const ICONS = {
    success: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
    error: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>',
    warning: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>',
    info: '<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>',
};

const TONES = {
    success: 'border-emerald-200 bg-white text-emerald-800',
    error: 'border-rose-200 bg-white text-rose-800',
    warning: 'border-amber-200 bg-white text-amber-800',
    info: 'border-sky-200 bg-white text-sky-800',
};

const ICON_TONES = {
    success: 'bg-emerald-50 text-emerald-600',
    error: 'bg-rose-50 text-rose-600',
    warning: 'bg-amber-50 text-amber-600',
    info: 'bg-sky-50 text-sky-600',
};

/**
 * Turns the flash messages rendered server-side into stacked toasts, and
 * exposes window.KT.toast() for anything that needs to raise one client-side.
 */
export default function initToasts() {
    const stack = ensureStack();

    // Flash payloads written by the Blade layout.
    $$('[data-flash]').forEach((node) => {
        push(stack, node.dataset.flash || 'info', node.textContent.trim());
        node.remove();
    });

    window.KT = window.KT || {};
    window.KT.toast = (message, type = 'info') => push(stack, type, message);
}

function ensureStack() {
    let stack = $('#kt-toasts');

    if (!stack) {
        stack = document.createElement('div');
        stack.id = 'kt-toasts';
        stack.className = 'pointer-events-none fixed inset-x-4 top-4 z-[60] flex flex-col items-end gap-2 sm:inset-x-auto sm:right-6 sm:top-6 sm:w-full sm:max-w-sm';
        stack.setAttribute('role', 'status');
        stack.setAttribute('aria-live', 'polite');
        document.body.appendChild(stack);
    }

    return stack;
}

function push(stack, type, message) {
    if (!message) return;

    const tone = TONES[type] ? type : 'info';

    const toast = document.createElement('div');
    toast.className = `pointer-events-auto flex w-full items-start gap-3 rounded-xl border ${TONES[tone]} p-3.5 shadow-[0_12px_32px_-8px_rgba(16,20,19,.18)]`;
    toast.style.animation = 'kt-toast-in .32s cubic-bezier(.22,.61,.36,1)';

    toast.innerHTML = `
        <span class="grid size-8 shrink-0 place-items-center rounded-lg ${ICON_TONES[tone]}">
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true">${ICONS[tone]}</svg>
        </span>
        <p class="flex-1 pt-1 text-sm font-medium leading-snug"></p>
        <button type="button" class="shrink-0 rounded-md p-1 text-ink-400 transition hover:bg-ink-100 hover:text-ink-700" aria-label="Dismiss notification">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    `;

    // textContent, never innerHTML — flash text can contain user-supplied names.
    toast.querySelector('p').textContent = message;

    const dismiss = () => {
        toast.style.transition = 'opacity .25s ease, transform .25s ease';
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(16px)';
        setTimeout(() => toast.remove(), 260);
    };

    toast.querySelector('button').addEventListener('click', dismiss);
    stack.appendChild(toast);

    setTimeout(dismiss, 6000);
}
