document.addEventListener('DOMContentLoaded', function () {
    if (typeof Quill === 'undefined') return;

    const editorDiv = document.getElementById('article-editor');
    const contentInput = document.getElementById('article-content-input');
    const form = document.getElementById('article-form');

    if (!editorDiv || !contentInput || !form) return;

    const quill = new Quill(editorDiv, {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ header: [1, 2, 3, false] }],
                ['blockquote'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link'],
                ['clean']
            ]
        },
        placeholder: 'Rakstiet raksta saturu šeit...'
    });

    // Pre-fill editor if editing existing content
    const existing = contentInput.value;
    if (existing) {
        quill.root.innerHTML = existing;
    }

    form.addEventListener('submit', function () {
        contentInput.value = quill.root.innerHTML;
    });
});

// Confirm before deleting an article
document.querySelectorAll('form[data-confirm-delete]').forEach((form) => {
    form.addEventListener('submit', (e) => {
        if (!window.confirm(form.dataset.confirmDelete || 'Dzēst rakstu?')) {
            e.preventDefault();
        }
    });
});
