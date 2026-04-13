(() => {
    document.querySelectorAll('form[data-confirm-delete]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (form._confirmed) return;
            e.preventDefault();
            SaprastsConfirm.show(form.dataset.confirmDelete || 'Vai tiešām dzēst šo ziņojumu?', { okText: 'Dzēst', type: 'danger' }).then((confirmed) => {
                if (confirmed) {
                    form._confirmed = true;
                    form.submit();
                }
            });
        });
    });
})();
