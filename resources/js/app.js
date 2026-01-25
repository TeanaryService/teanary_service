import './bootstrap';

import Quill from 'quill';
import 'quill/dist/quill.snow.css';

// Quill rich-text editor helper for Livewire (no Alpine required)
window.TeanyQuill = {
    mount(editorEl, inputEl) {
        if (!editorEl || !inputEl) return;
        // Lazy mount: skip hidden panels (language tabs)
        if (editorEl.closest('.hidden')) return;

        const uploadUrl = editorEl.dataset.uploadUrl || '';
        const minHeight = editorEl.dataset.minHeight || '240px';

        let quill = editorEl.__teanyQuill;
        if (!quill) {
            const imageHandler = () => {
                const picker = document.createElement('input');
                picker.type = 'file';
                picker.accept = 'image/*';
                picker.style.display = 'none';
                document.body.appendChild(picker);

                picker.addEventListener('change', async () => {
                    const file = picker.files && picker.files[0];
                    document.body.removeChild(picker);
                    if (!file) return;

                    // Fallback: if no uploadUrl, ask URL
                    if (!uploadUrl) {
                        const url = window.prompt('请输入图片 URL');
                        if (!url) return;
                        const range = quill.getSelection(true);
                        quill.insertEmbed(range.index, 'image', url, 'user');
                        quill.setSelection(range.index + 1, 0, 'silent');
                        return;
                    }

                    try {
                        const fd = new FormData();
                        fd.append('image', file);
                        const resp = await window.axios.post(uploadUrl, fd, {
                            headers: { 'Content-Type': 'multipart/form-data' },
                        });
                        const url = resp?.data?.url;
                        if (!url) return;
                        const range = quill.getSelection(true);
                        quill.insertEmbed(range.index, 'image', url, 'user');
                        quill.setSelection(range.index + 1, 0, 'silent');
                    } catch (e) {
                        console.error(e);
                        window.alert('图片上传失败，请重试');
                    }
                });

                picker.click();
            };

            quill = new Quill(editorEl, {
                theme: 'snow',
                modules: {
                    toolbar: {
                        container: [
                            [{ header: [1, 2, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            ['link', 'image'],
                            ['clean'],
                        ],
                        handlers: {
                            image: imageHandler,
                        },
                    },
                },
            });

            editorEl.__teanyQuill = quill;

            // Initial content
            quill.root.innerHTML = inputEl.value || '';
            editorEl.dataset.lastHtml = quill.root.innerHTML || '';

            quill.on('text-change', () => {
                const html = quill.root.innerHTML || '';
                editorEl.dataset.lastHtml = html;
                inputEl.value = html;
                inputEl.dispatchEvent(new Event('input', { bubbles: true }));
            });

            // Styling
            quill.root.style.minHeight = minHeight;
        } else {
            // Sync content after Livewire updates textarea
            const last = editorEl.dataset.lastHtml ?? '';
            const next = inputEl.value || '';
            if (!quill.hasFocus() && next !== last && next !== quill.root.innerHTML) {
                quill.root.innerHTML = next;
                editorEl.dataset.lastHtml = next;
            }
            quill.root.style.minHeight = minHeight;
        }
    },

    scan() {
        document.querySelectorAll('[data-teany-quill-editor]').forEach((editorEl) => {
            const inputId = editorEl.dataset.inputId;
            if (!inputId) return;
            const inputEl = document.getElementById(inputId);
            window.TeanyQuill.mount(editorEl, inputEl);
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
                // mount editors inside newly-visible panel
                window.TeanyQuill?.scan?.();
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

// Auto-mount editors (no Alpine required)
const scheduleScan = (() => {
    let t = null;
    return () => {
        clearTimeout(t);
        t = setTimeout(() => {
            window.TeanyLangTabs.scan();
            window.TeanyQuill.scan();
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
