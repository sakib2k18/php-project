import { $, $$ } from '../bootstrap';

/**
 * Accessible confirmation dialog for destructive actions.
 *
 * <form method="POST" data-confirm="Delete this campaign?" data-confirm-action="Delete">
 * The form is only submitted after the user confirms, so a stray click can
 * never delete a record.
 */
export default function initConfirmations() {
    const dialog = buildDialog();
    let pending = null;

    const close = () => {
        dialog.root.setAttribute('hidden', '');
        document.body.style.overflow = '';
        pending = null;
    };

    const open = (options, onConfirm) => {
        pending = onConfirm;
        dialog.title.textContent = options.title;
        dialog.body.textContent = options.body;
        dialog.confirm.textContent = options.action;
        dialog.confirm.className = `btn ${options.danger ? 'btn-danger' : 'btn-primary'}`;
        dialog.root.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';
        dialog.confirm.focus();
    };

    dialog.confirm.addEventListener('click', () => {
        const run = pending;
        close();
        run?.();
    });

    dialog.cancel.addEventListener('click', close);
    dialog.backdrop.addEventListener('click', close);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !dialog.root.hasAttribute('hidden')) close();
    });

    $$('[data-confirm]').forEach((element) => {
        const isForm = element.tagName === 'FORM';

        const handler = (event) => {
            event.preventDefault();

            open(
                {
                    title: element.dataset.confirmTitle || 'Please confirm',
                    body: element.dataset.confirm,
                    action: element.dataset.confirmAction || 'Confirm',
                    danger: element.dataset.confirmTone !== 'primary',
                },
                () => (isForm ? element.submit() : (window.location.href = element.href))
            );
        };

        if (isForm) {
            element.addEventListener('submit', handler);
        } else {
            element.addEventListener('click', handler);
        }
    });
}

function buildDialog() {
    const root = document.createElement('div');
    root.className = 'fixed inset-0 z-[70] flex items-end justify-center p-4 sm:items-center';
    root.setAttribute('role', 'dialog');
    root.setAttribute('aria-modal', 'true');
    root.setAttribute('hidden', '');

    root.innerHTML = `
        <div data-backdrop class="absolute inset-0 bg-ink-950/50 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-md rounded-2xl border border-ink-100 bg-white p-6 shadow-[0_24px_56px_-12px_rgba(16,20,19,.28)]">
            <div class="flex items-start gap-4">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-rose-50 text-rose-600">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <h2 data-title class="text-base font-bold text-ink-900"></h2>
                    <p data-body class="mt-1.5 text-sm leading-relaxed text-ink-600"></p>
                </div>
            </div>
            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <button type="button" data-cancel class="btn btn-outline">Cancel</button>
                <button type="button" data-confirm-btn class="btn btn-danger"></button>
            </div>
        </div>
    `;

    document.body.appendChild(root);

    return {
        root,
        backdrop: $('[data-backdrop]', root),
        title: $('[data-title]', root),
        body: $('[data-body]', root),
        confirm: $('[data-confirm-btn]', root),
        cancel: $('[data-cancel]', root),
    };
}
