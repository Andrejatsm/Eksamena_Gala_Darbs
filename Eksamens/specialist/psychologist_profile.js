(() => {
    let selectedSlotId = null;

    window.selectSlot = (slotId, slotTime) => {
        selectedSlotId = slotId;

        const selectedTime = document.getElementById('selectedTime');
        const bookingSummary = document.getElementById('bookingSummary');
        const paymentBtn = document.getElementById('paymentBtn');
        const stickyPanel = document.querySelector('.sticky');

        if (selectedTime) {
            selectedTime.textContent = slotTime;
        }
        if (bookingSummary) {
            bookingSummary.classList.remove('hidden');
        }
        if (paymentBtn) {
            paymentBtn.disabled = false;
        }

        if (stickyPanel) {
            stickyPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    };

    window.openPayment = (psychologistId) => {
        if (!selectedSlotId) {
            SaprastsToast.warning('Lūdzu, izvēlieties laiku.');
            return;
        }

        const nameElement = document.querySelector('[data-psychologist-name]');
        const psychologistName = nameElement ? nameElement.textContent.trim() : '';
        const basePathPrefix = document.body.dataset.pathPrefix || '../';

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `${basePathPrefix}api/checkout.php`;

        const fields = {
            psychologist_account_id: psychologistId,
            psihologs_vards: psychologistName,
            cena: 50,
            slot_id: selectedSlotId,
        };

        Object.entries(fields).forEach(([name, value]) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    };

    document.querySelectorAll('.slot-select-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            const slotId = btn.dataset.slotId;
            const slotTime = btn.dataset.slotTime;
            window.selectSlot(slotId, slotTime);
        });
    });

    const paymentBtn = document.getElementById('paymentBtn');
    if (paymentBtn) {
        paymentBtn.addEventListener('click', () => {
            const psychologistId = paymentBtn.dataset.psychologistId;
            window.openPayment(psychologistId);
        });
    }
})();
