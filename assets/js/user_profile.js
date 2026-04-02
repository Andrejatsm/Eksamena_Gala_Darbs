(() => {
    const deleteModal = document.getElementById('deleteAccountModal');
    const openDeleteBtn = document.getElementById('openDeleteAccountModalBtn');
    const cancelDeleteBtn = document.getElementById('cancelDeleteAccountModalBtn');

    if (openDeleteBtn && deleteModal) {
        openDeleteBtn.addEventListener('click', () => deleteModal.classList.remove('hidden'));
    }
    if (cancelDeleteBtn && deleteModal) {
        cancelDeleteBtn.addEventListener('click', () => deleteModal.classList.add('hidden'));
    }

    // Close on backdrop click
    if (deleteModal) {
        deleteModal.addEventListener('click', (e) => {
            if (e.target === deleteModal) deleteModal.classList.add('hidden');
        });
    }
})();
