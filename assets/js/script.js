document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.getElementById('theme-toggle');
    const themeToggleMobile = document.getElementById('theme-toggle-mobile');
    const htmlElement = document.documentElement;

    const applyTheme = (isDark) => {
        if (isDark) {
            htmlElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
            if (themeToggle) themeToggle.checked = true;
            if (themeToggleMobile) themeToggleMobile.checked = true;
        } else {
            htmlElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
            if (themeToggle) themeToggle.checked = false;
            if (themeToggleMobile) themeToggleMobile.checked = false;
        }
    };

    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        applyTheme(true);
    } else {
        applyTheme(false);
    }

    if (themeToggle) themeToggle.addEventListener('change', (e) => applyTheme(e.target.checked));
    if (themeToggleMobile) themeToggleMobile.addEventListener('change', (e) => applyTheme(e.target.checked));
});