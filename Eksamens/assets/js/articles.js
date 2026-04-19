document.addEventListener('DOMContentLoaded', function () {
    if (typeof Quill !== 'undefined') {
        const editorDiv = document.getElementById('article-editor');
        const contentInput = document.getElementById('article-content-input');
        const form = document.getElementById('article-form');

        if (editorDiv && contentInput && form) {
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

            const existing = contentInput.value;
            if (existing) {
                quill.root.innerHTML = existing;
            }

            form.addEventListener('submit', function () {
                contentInput.value = quill.root.innerHTML;
            });
        }
    }

    if (typeof SaprastsConfirm === 'undefined') return;

    document.querySelectorAll('form[data-confirm-delete]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (form._confirmed) return;
            e.preventDefault();
            SaprastsConfirm.show(form.dataset.confirmDelete || 'Vai tiešām dzēst šo rakstu?', { okText: 'Dzēst', type: 'danger' }).then((confirmed) => {
                if (confirmed) {
                    form._confirmed = true;
                    form.submit();
                }
            });
        });
    });
});
