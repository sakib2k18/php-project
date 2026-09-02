import { $$ } from '../bootstrap';

const ALLOWED = ['image/jpeg', 'image/png', 'image/webp'];

/**
 * Client-side preview and pre-flight check for image uploads.
 * The same constraints are re-checked by Laravel on the server.
 */
export default function initImagePreviews() {
    $$('[data-image-input]').forEach((input) => {
        const preview = document.getElementById(input.dataset.imagePreview);
        const nameSlot = document.getElementById(input.dataset.imageName || '');
        const errorSlot = document.getElementById(`${input.id}-error`);
        const maxKb = Number(input.dataset.maxKb || 2048);

        input.addEventListener('change', () => {
            const file = input.files?.[0];

            if (!file) {
                if (nameSlot) nameSlot.textContent = 'No file selected';
                return;
            }

            const fail = (message) => {
                input.value = '';
                input.classList.add('field-error');
                if (errorSlot) {
                    errorSlot.textContent = message;
                    errorSlot.removeAttribute('hidden');
                }
                if (nameSlot) nameSlot.textContent = 'No file selected';
            };

            if (!ALLOWED.includes(file.type)) {
                return fail('Only JPG, PNG and WEBP images are accepted.');
            }

            if (file.size / 1024 > maxKb) {
                return fail(`The image must be smaller than ${(maxKb / 1024).toFixed(1)} MB.`);
            }

            input.classList.remove('field-error');
            errorSlot?.setAttribute('hidden', '');

            if (nameSlot) {
                nameSlot.textContent = `${file.name} · ${(file.size / 1024).toFixed(0)} KB`;
            }

            if (preview) {
                const url = URL.createObjectURL(file);
                preview.src = url;
                preview.classList.remove('hidden');
                preview.addEventListener('load', () => URL.revokeObjectURL(url), { once: true });
            }
        });
    });
}
