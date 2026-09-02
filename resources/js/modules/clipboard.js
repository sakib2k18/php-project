import { $$ } from '../bootstrap';

/** Copy-to-clipboard for bank details, donation references and share links. */
export default function initClipboard() {
    $$('[data-copy]').forEach((button) => {
        button.addEventListener('click', async () => {
            const value = button.dataset.copy;

            try {
                await navigator.clipboard.writeText(value);
                window.KT?.toast?.(button.dataset.copyMessage || 'Copied to clipboard.', 'success');
            } catch {
                // Clipboard API is unavailable over plain http on some browsers.
                const helper = document.createElement('textarea');
                helper.value = value;
                helper.style.position = 'fixed';
                helper.style.opacity = '0';
                document.body.appendChild(helper);
                helper.select();
                document.execCommand('copy');
                helper.remove();
                window.KT?.toast?.('Copied to clipboard.', 'success');
            }
        });
    });

    // Print buttons (donation receipt).
    $$('[data-print]').forEach((button) => button.addEventListener('click', () => window.print()));
}
