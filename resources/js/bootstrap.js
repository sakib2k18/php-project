/**
 * Small shared helpers used by several modules.
 */

/** The CSRF token Laravel put in the page head. */
export const csrfToken = () =>
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

/** querySelectorAll as a real array. */
export const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

export const $ = (selector, root = document) => root.querySelector(selector);

/** Expose the token for any fetch() call that needs it. */
window.KT = window.KT || {};
window.KT.csrf = csrfToken();
