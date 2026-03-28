(() => {
    const modal = document.getElementById('rescheduleModal');
    const appointmentInput = document.getElementById('modal_appointment_id');
    const closeRescheduleModalBtn = document.getElementById('closeRescheduleModalBtn');
    const openRescheduleBtns = document.querySelectorAll('.open-reschedule-btn');

    window.openRescheduleModal = (appointmentId) => {
        if (!modal || !appointmentInput) return;
        appointmentInput.value = appointmentId;
        modal.classList.remove('hidden');
    };

    window.closeRescheduleModal = () => {
        if (!modal) return;
        modal.classList.add('hidden');
    };

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
})();
