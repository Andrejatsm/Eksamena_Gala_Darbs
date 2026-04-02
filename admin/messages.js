(() => {
    document.querySelectorAll('form[data-confirm-delete]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (!window.confirm(form.dataset.confirmDelete || 'Vai tiešām dzēst šo ziņojumu?')) {
                e.preventDefault();
            }
        });
    });
})();
