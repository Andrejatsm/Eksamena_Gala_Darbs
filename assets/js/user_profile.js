document.addEventListener('DOMContentLoaded', function () {
    const openBtn = document.getElementById('openDeleteAccountModalBtn');
    const modal = document.getElementById('deleteAccountModal');

    if (openBtn && modal) {
        openBtn.addEventListener('click', function () {
            modal.classList.remove('hidden');
        });
    }
});
