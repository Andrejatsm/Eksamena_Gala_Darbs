(function () {
    'use strict';

    var prefix = document.body.dataset.pathPrefix || '';
    var apiUrl = prefix + 'api/notifications.php';

    // Izveido atgādinājuma banneri zem navigācijas
    var nav = document.querySelector('nav');
    if (!nav) return;

    var banner = document.createElement('div');
    banner.id = 'reminder-banner';
    banner.className = 'hidden';
    nav.insertAdjacentElement('afterend', banner);

    function minutesUntil(scheduledAt) {
        return Math.round((new Date(scheduledAt.replace(' ', 'T')) - new Date()) / 60000);
    }

    function formatTime(scheduledAt) {
        var diff = minutesUntil(scheduledAt);
        if (diff <= 0) return 'sākas tagad!';
        if (diff === 1) return 'sākas pēc 1 minūtes';
        return 'sākas pēc ' + diff + ' minūtēm';
    }

    function updateBanner(upcoming) {
        if (!upcoming || upcoming.length === 0) {
            banner.classList.add('hidden');
            banner.innerHTML = '';
            return;
        }

        var items = upcoming.map(function (appt) {
            var timeStr = formatTime(appt.scheduled_at);
            var icon = appt.consultation_type === 'online' ? 'fa-video' : 'fa-map-marker-alt';
            var name = appt.partner_name || '';
            return '<div class="flex items-center gap-2 text-sm">' +
                '<i class="fas ' + icon + '"></i>' +
                '<span><strong>' + escapeHtml(name) + '</strong> — ' + escapeHtml(timeStr) + '</span>' +
                '</div>';
        }).join('');

        banner.innerHTML =
            '<div class="bg-primary/10 dark:bg-primary/20 border-b border-primary/20 py-3 px-4">' +
                '<div class="max-w-7xl mx-auto flex items-center gap-4 flex-wrap">' +
                    '<div class="flex items-center gap-2 text-primary font-semibold text-sm">' +
                        '<i class="fas fa-bell animate-pulse"></i> Atgādinājums' +
                    '</div>' +
                    '<div class="flex flex-wrap gap-4 text-gray-700 dark:text-gray-300">' + items + '</div>' +
                '</div>' +
            '</div>';
        banner.classList.remove('hidden');
    }

    function updateUnreadBadges(unreadTotal, byAppointment) {
        // Pierakstu saite navigācijā
        document.querySelectorAll('a[href*="appointments.php"]').forEach(function (link) {
            var badge = link.querySelector('.unread-badge');
            if (unreadTotal > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'unread-badge ml-1 inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold text-white bg-red-500 rounded-full';
                    link.appendChild(badge);
                }
                badge.textContent = unreadTotal > 99 ? '99+' : unreadTotal;
            } else if (badge) {
                badge.remove();
            }
        });

        // Katras konsultācijas čata poga
        document.querySelectorAll('a[href*="chat.php?appointment_id="]').forEach(function (link) {
            var match = link.href.match(/appointment_id=(\d+)/);
            if (!match) return;
            var apptId = parseInt(match[1], 10);
            var count = byAppointment[apptId] || 0;

            var badge = link.querySelector('.chat-unread-badge');
            if (count > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'chat-unread-badge ml-1 inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold text-white bg-red-500 rounded-full';
                    link.appendChild(badge);
                }
                badge.textContent = count;
            } else if (badge) {
                badge.remove();
            }
        });
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function fetchNotifications() {
        fetch(apiUrl, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                updateBanner(data.upcoming || []);
                updateUnreadBadges(data.unread_total || 0, data.unread_by_appointment || {});
            })
            .catch(function () { /* tīkla kļūda – ignorē */ });
    }

    // Pirmais pieprasījums + atkārtojam ik 30 sekundes
    fetchNotifications();
    setInterval(fetchNotifications, 30000);

    // Apturēt polling, kad cilne nav aktīva
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) fetchNotifications();
    });
})();
