document.addEventListener('DOMContentLoaded', function () {
    var confirmForms = document.querySelectorAll('form[data-confirm-delete]');
    if (!confirmForms.length) return;

    confirmForms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (form._confirmed) {
                form._confirmed = false;
                return;
            }

            e.preventDefault();

            if (typeof SaprastsConfirm === 'undefined') {
                form.submit();
                return;
            }

            SaprastsConfirm.show(form.dataset.confirmDelete || 'Dzēst slotu?', {
                okText: 'Dzēst',
                type: 'danger'
            }).then(function (confirmed) {
                if (!confirmed) return;
                form._confirmed = true;
                form.submit();
            });
        });
    });
});
