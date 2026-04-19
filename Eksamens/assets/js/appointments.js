document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('rescheduleModal');
    const appointmentInput = document.getElementById('modal_appointment_id');
    const closeRescheduleModalBtn = document.getElementById('closeRescheduleModalBtn');
    const openRescheduleBtns = document.querySelectorAll('.open-reschedule-btn');

    // Saglabājam izvēlētā pieraksta ID hidden laukā, lai forma zinātu, kuru ierakstu pārcelt.
    window.openRescheduleModal = (appointmentId) => {
        if (!modal || !appointmentInput) return;
        appointmentInput.value = appointmentId;
        modal.classList.remove('hidden');
    };

    window.closeRescheduleModal = () => {
        if (!modal) return;
        modal.classList.add('hidden');
    };

    // Aizveram modāli arī tad, ja lietotājs uzklikšķina ārpus paša satura bloka.
    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            window.closeRescheduleModal();
        }
    });

    openRescheduleBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            const appointmentId = btn.dataset.appointmentId;
            window.openRescheduleModal(appointmentId);
        });
    });

    if (closeRescheduleModalBtn) {
        closeRescheduleModalBtn.addEventListener('click', window.closeRescheduleModal);
    }

    const closeRescheduleModalBtnFooter = document.getElementById('closeRescheduleModalBtnFooter');
    if (closeRescheduleModalBtnFooter) {
        closeRescheduleModalBtnFooter.addEventListener('click', window.closeRescheduleModal);
    }

    // Confirm before cancelling an appointment (AJAX)
    document.querySelectorAll('.cancel-appt-btn').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            SaprastsConfirm.show('Vai tiešām vēlaties atcelt pierakstu?', { okText: 'Jā, atcelt', type: 'danger' }).then((confirmed) => {
                if (!confirmed) return;

                const form = btn.closest('form');
                if (!form) return;

                const formData = new FormData(form);
                // Submit button value isn't included by FormData — add manually
                if (btn.name && btn.value) {
                    formData.set(btn.name, btn.value);
                }
                const actionUrl = form.getAttribute('action') || window.location.href;
                fetch(actionUrl, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                })
                .then((r) => r.json())
                .then((data) => {
                    if (data.success) {
                        SaprastsToast.success(data.message || 'Pieraksts atcelts.');
                        // Noņemam kartīti no DOM
                        const card = btn.closest('.panel-card') || btn.closest('.appointment-card');
                        if (card) {
                            card.style.transition = 'opacity 0.3s, transform 0.3s';
                            card.style.opacity = '0';
                            card.style.transform = 'scale(0.95)';
                            setTimeout(() => card.remove(), 300);
                        }
                    } else {
                        SaprastsToast.error(data.message || 'Kļūda atceļot pierakstu.');
                    }
                })
                .catch(() => {
                    SaprastsToast.error('Tīkla kļūda. Mēģiniet vēlreiz.');
                });
            });
        });
    });
});
