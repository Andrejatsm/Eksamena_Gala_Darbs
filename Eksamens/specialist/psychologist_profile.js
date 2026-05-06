(() => {
    let selectedSlotId = null;

    window.selectSlot = (slotId, slotTime) => {
        selectedSlotId = slotId;

        const selectedTime = document.getElementById('selectedTime');
        const bookingSummary = document.getElementById('bookingSummary');
        const paymentBtn = document.getElementById('paymentBtn');
        const stickyPanel = document.querySelector('.sticky');

        if (selectedTime) selectedTime.textContent = slotTime;
        if (bookingSummary) bookingSummary.classList.remove('hidden');
        if (paymentBtn) paymentBtn.disabled = false;
        if (stickyPanel) stickyPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
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
            window.selectSlot(btn.dataset.slotId, btn.dataset.slotTime);
        });
    });

    const paymentBtn = document.getElementById('paymentBtn');
    if (paymentBtn) {
        paymentBtn.addEventListener('click', () => {
            window.openPayment(paymentBtn.dataset.psychologistId);
        });
    }

    // Slot pagination
    (function () {
        const PER_PAGE = 5;
        const container = document.getElementById('slotsContainer');
        const controls  = document.getElementById('slotPaginationControls');

        if (!container || !controls) return;

        const slots = Array.from(container.querySelectorAll('.slot-row'));
        let currentPage = 1;
        const totalPages = Math.ceil(slots.length / PER_PAGE);

        function render(page) {
            currentPage = page;
            const start = (page - 1) * PER_PAGE;
            const end   = start + PER_PAGE;
            slots.forEach((slot, i) => {
                slot.style.display = (i >= start && i < end) ? '' : 'none';
            });
            renderControls();
        }

        function renderControls() {
            if (totalPages <= 1) { controls.innerHTML = ''; return; }

            const prevDisabled = currentPage === 1;
            const nextDisabled = currentPage === totalPages;

            controls.innerHTML = `
                <button class="${prevDisabled ? 'pagination-btn-disabled' : 'pagination-btn'}" id="slotPrev" ${prevDisabled ? 'disabled' : ''}>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span class="text-sm text-gray-600 dark:text-gray-400 px-2">
                    ${currentPage} / ${totalPages}
                </span>
                <button class="${nextDisabled ? 'pagination-btn-disabled' : 'pagination-btn'}" id="slotNext" ${nextDisabled ? 'disabled' : ''}>
                    <i class="fas fa-chevron-right"></i>
                </button>
            `;

            document.getElementById('slotPrev')?.addEventListener('click', () => render(currentPage - 1));
            document.getElementById('slotNext')?.addEventListener('click', () => render(currentPage + 1));
        }

        render(1);
    })();

})();