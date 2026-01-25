import './bootstrap';

import pell from 'pell';
import 'pell/dist/pell.css';

/**
 * Pell rich-text editor helper for Alpine/Livewire.
 * - Reads initial HTML from a hidden <textarea>.
 * - Writes changes back to that <textarea> and dispatches input event (so Livewire picks it up).
 * - Idempotent: safe to call multiple times.
 */
window.TeanyPell = {
    mount(editorEl, inputEl) {
        if (!editorEl || !inputEl) return;
        const isMounted = editorEl.dataset.pellMounted === '1';
        editorEl.dataset.pellMounted = '1';

        const minHeight = editorEl.dataset.minHeight || '240px';

        if (!isMounted) {
            pell.init({
                element: editorEl,
                defaultParagraphSeparator: 'p',
                styleWithCSS: false,
                actions: [
                    'bold',
                    'italic',
                    'underline',
                    'strikethrough',
                    'heading1',
                    'heading2',
                    'paragraph',
                    'quote',
                    'unorderedlist',
                    'orderedlist',
                    'link',
                ],
                onChange: (html) => {
                    const next = html ?? '';
                    editorEl.dataset.lastHtml = next;
                    inputEl.value = next;
                    inputEl.dispatchEvent(new Event('input', { bubbles: true }));
                },
            });
        }

        const contentEl = editorEl.querySelector('.pell-content');
        if (contentEl) {
            contentEl.style.minHeight = minHeight;
            // 同步内容：Livewire 可能在 mount 之后才把 textarea.value 写进去
            const current = contentEl.innerHTML || '';
            const next = inputEl.value || '';
            const last = editorEl.dataset.lastHtml ?? '';
            const isFocused = document.activeElement === contentEl || contentEl.contains(document.activeElement);

            if (!isFocused && next !== last && next !== current) {
                contentEl.innerHTML = next;
                editorEl.dataset.lastHtml = next;
            }
        }
    },

    scan() {
        document.querySelectorAll('[data-teany-pell-editor]').forEach((editorEl) => {
            const inputId = editorEl.dataset.inputId;
            if (!inputId) return;
            const inputEl = document.getElementById(inputId);
            window.TeanyPell.mount(editorEl, inputEl);
        });
    },
};

// Simple language tabs (no Alpine required)
window.TeanyLangTabs = {
    mount(root) {
        if (!root) return;
        if (root.dataset.langtabsMounted === '1') {
            // still sync visibility in case Livewire updated DOM
        } else {
            root.dataset.langtabsMounted = '1';
            root.addEventListener('click', (e) => {
                const btn = e.target?.closest?.('[data-teany-langtab]');
                if (!btn || !root.contains(btn)) return;
                e.preventDefault();
                const lang = btn.dataset.teanyLangtab;
                if (!lang) return;
                root.dataset.activeLang = String(lang);
                window.TeanyLangTabs.sync(root);
            });
        }

        window.TeanyLangTabs.sync(root);
    },

    sync(root) {
        const defaultLang = root.dataset.defaultLang || '';
        const active = root.dataset.activeLang || defaultLang;

        // Buttons
        root.querySelectorAll('[data-teany-langtab]').forEach((btn) => {
            const lang = btn.dataset.teanyLangtab;
            const activeClass = btn.dataset.activeClass || '';
            const inactiveClass = btn.dataset.inactiveClass || '';

            if (String(lang) === String(active)) {
                if (inactiveClass) btn.classList.remove(...inactiveClass.split(' ').filter(Boolean));
                if (activeClass) btn.classList.add(...activeClass.split(' ').filter(Boolean));
                btn.setAttribute('aria-selected', 'true');
            } else {
                if (activeClass) btn.classList.remove(...activeClass.split(' ').filter(Boolean));
                if (inactiveClass) btn.classList.add(...inactiveClass.split(' ').filter(Boolean));
                btn.setAttribute('aria-selected', 'false');
            }
        });

        // Panels
        let found = false;
        root.querySelectorAll('[data-teany-langpanel]').forEach((panel) => {
            const lang = panel.dataset.teanyLangpanel;
            const shouldShow = String(lang) === String(active);
            panel.classList.toggle('hidden', !shouldShow);
            if (shouldShow) found = true;
        });

        // Fallback to default if active missing
        if (!found && defaultLang) {
            root.dataset.activeLang = String(defaultLang);
            root.querySelectorAll('[data-teany-langpanel]').forEach((panel) => {
                const lang = panel.dataset.teanyLangpanel;
                panel.classList.toggle('hidden', String(lang) !== String(defaultLang));
            });
        }
    },

    scan() {
        document.querySelectorAll('[data-teany-langtabs]').forEach((root) => {
            window.TeanyLangTabs.mount(root);
        });
    },
};

// Auto-mount pell editors (no Alpine required)
const scheduleScan = (() => {
    let t = null;
    return () => {
        clearTimeout(t);
        t = setTimeout(() => {
            window.TeanyPell.scan();
            window.TeanyLangTabs.scan();
        }, 0);
    };
})();

document.addEventListener('DOMContentLoaded', scheduleScan);
document.addEventListener('livewire:navigated', scheduleScan);
document.addEventListener('livewire:load', scheduleScan);
document.addEventListener('livewire:update', scheduleScan);
document.addEventListener('livewire:init', () => {
    scheduleScan();
    // Best-effort hook support across Livewire versions
    try {
        if (window.Livewire?.hook) {
            window.Livewire.hook('morph.updated', scheduleScan);
            window.Livewire.hook('message.processed', scheduleScan);
        }
    } catch {
        // ignore
    }
});

// Fallback: observe DOM mutations and mount newly added editors
const observer = new MutationObserver(scheduleScan);
observer.observe(document.documentElement, { childList: true, subtree: true });
