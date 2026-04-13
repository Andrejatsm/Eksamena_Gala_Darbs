(() => {
    const userMenuBtn = document.getElementById('user-menu-button');
    const userDropdown = document.getElementById('user-dropdown');

    if (!userMenuBtn || !userDropdown) {
        return;
    }

    userMenuBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        userDropdown.classList.toggle('hidden');
    });

    document.addEventListener('click', (e) => {
        if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
            userDropdown.classList.add('hidden');
        }
    });
})();

// Prevent "resend form data" prompt on page refresh after POST submission.
(function () {
    if (!window.history || typeof window.history.replaceState !== 'function') {
        return;
    }

    document.addEventListener('submit', function () {
        try {
            sessionStorage.setItem('saprasts_post_submitted', '1');
        } catch (e) {
            // Ignore storage access issues.
        }
    }, true);

    window.addEventListener('load', function () {
        try {
            if (sessionStorage.getItem('saprasts_post_submitted') === '1') {
                window.history.replaceState({}, document.title, window.location.href);
                sessionStorage.removeItem('saprasts_post_submitted');
            }
        } catch (e) {
            // Ignore storage access issues.
        }
    });
})();
