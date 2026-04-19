document.addEventListener('DOMContentLoaded', function () {
    if (typeof SaprastsConfirm === 'undefined') return;

    // Confirm before deleting a slot (AJAX)
    document.querySelectorAll('form[data-confirm-delete]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            SaprastsConfirm.show(form.dataset.confirmDelete || 'Dzēst slotu?', { okText: 'Dzēst', type: 'danger' }).then((confirmed) => {
                if (!confirmed) return;

                const formData = new FormData(form);
                const actionUrl = form.getAttribute('action') || window.location.href;
                fetch(actionUrl, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                })
                .then((r) => r.json())
                .then((data) => {
                    if (data.success) {
                        SaprastsToast.success(data.message || 'Slots dzēsts.');
                        const row = form.closest('.slot-row') || form.closest('tr') || form.closest('.panel-card');
                        if (row) {
                            row.style.transition = 'opacity 0.3s, transform 0.3s';
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(20px)';
                            setTimeout(() => row.remove(), 300);
                        }
                    } else {
                        SaprastsToast.error(data.message || 'Kļūda dzēšot slotu.');
                    }
                })
                .catch(() => {
                    SaprastsToast.error('Tīkla kļūda. Mēģiniet vēlreiz.');
                });
            });
        });
    });
});
