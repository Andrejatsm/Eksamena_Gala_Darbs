document.addEventListener('DOMContentLoaded', function () {
    const startsTimeInput = document.getElementById('starts_time');
    const endsTimeInput = document.getElementById('ends_time');

    if (!startsTimeInput || !endsTimeInput) {
        return;
    }

    const toMinutes = (value) => {
        const parts = String(value || '').split(':');
        if (parts.length < 2) return null;
        const h = parseInt(parts[0], 10);
        const m = parseInt(parts[1], 10);
        if (!Number.isFinite(h) || !Number.isFinite(m)) return null;
        return h * 60 + m;
    };

    const toTime = (minutes) => {
        const normalized = ((minutes % 1440) + 1440) % 1440;
        const h = String(Math.floor(normalized / 60)).padStart(2, '0');
        const m = String(normalized % 60).padStart(2, '0');
        return `${h}:${m}`;
    };

    const syncEndAfterStart = () => {
        const start = toMinutes(startsTimeInput.value);
        if (start === null) return;

        const end = toMinutes(endsTimeInput.value);
        if (end === null || end <= start) {
            endsTimeInput.value = toTime(start + 60);
        } else if (end - start > 240) {
            endsTimeInput.value = toTime(start + 240);
        }

        endsTimeInput.min = startsTimeInput.value;
    };

    startsTimeInput.addEventListener('change', syncEndAfterStart);
    startsTimeInput.addEventListener('input', syncEndAfterStart);
    syncEndAfterStart();
});
