(function () {
    'use strict';

    var prefix = document.body.dataset.pathPrefix || '';
    var apiUrl = prefix + 'api/notifications.php';

    // Zvaniņa elementi
    var bellBtn = document.getElementById('notification-bell-btn');
    var bellBadge = document.getElementById('notification-bell-badge');
    var dropdown = document.getElementById('notification-dropdown');
    var notifList = document.getElementById('notification-list');
    var countLabel = document.getElementById('notification-count-label');
    var mobileBellBtn = document.getElementById('mobile-notification-btn');
    var mobileBadge = document.getElementById('mobile-notification-badge');

    // Ja nav zvaniņa (nav ielogojies) — izejam
    if (!bellBtn) return;

    var isDropdownOpen = false;

    // Toggle dropdown
    bellBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        isDropdownOpen = !isDropdownOpen;
        dropdown.classList.toggle('hidden', !isDropdownOpen);
    });

    // Mobile bell opens dropdown too (shows toast summary)
    if (mobileBellBtn) {
        mobileBellBtn.addEventListener('click', function () {
            // Scroll to top and open desktop dropdown if visible, else show toast
            if (dropdown) {
                isDropdownOpen = true;
                dropdown.classList.remove('hidden');
                var wrapper = document.getElementById('notification-bell-wrapper');
                if (wrapper) wrapper.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }

    // Aizvērt dropdown, klikšķinot ārpusē
    document.addEventListener('click', function (e) {
        if (isDropdownOpen && !dropdown.contains(e.target) && e.target !== bellBtn) {
            isDropdownOpen = false;
            dropdown.classList.add('hidden');
        }
    });

    function minutesUntil(scheduledAt) {
        return Math.round((new Date(scheduledAt.replace(' ', 'T')) - new Date()) / 60000);
    }

    function formatTime(scheduledAt) {
        var L = window.LANG || {};
        var diff = minutesUntil(scheduledAt);
        if (diff <= 0) return L.starts_now || 'sākas tagad!';
        if (diff === 1) return L.in_1_min || 'pēc 1 min';
        return (L.in_x_min || 'pēc %d min').replace('%d', diff);
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function updateBellBadge(total) {
        if (total > 0) {
            bellBadge.textContent = total > 99 ? '99+' : total;
            bellBadge.classList.remove('hidden');
            if (mobileBadge) {
                mobileBadge.textContent = total > 99 ? '99+' : total;
                mobileBadge.classList.remove('hidden');
            }
        } else {
            bellBadge.classList.add('hidden');
            if (mobileBadge) mobileBadge.classList.add('hidden');
        }
    }

    function renderNotifications(upcoming, unreadTotal, byAppointment) {
        var L = window.LANG || {};
        var items = [];
        var totalBadge = 0;

        // Gaidāmās sesijas
        if (upcoming && upcoming.length > 0) {
            upcoming.forEach(function (appt) {
                var timeStr = formatTime(appt.scheduled_at);
                var icon = appt.consultation_type === 'online' ? 'fa-video text-blue-500' : 'fa-map-marker-alt text-green-500';
                var name = appt.partner_name || '';
                var chatLink = appt.chat_active ? ' <a href="' + prefix + 'pages/chat.php?appointment_id=' + appt.id + '" class="text-primary hover:underline text-xs ml-1">' + (L.open_chat || 'Čats') + '</a>' : '';

                items.push(
                    '<div class="px-4 py-3 border-b border-gray-50 dark:border-zinc-700/50 hover:bg-gray-50 dark:hover:bg-zinc-700/30 transition">' +
                        '<div class="flex items-start gap-3">' +
                            '<div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0 mt-0.5">' +
                                '<i class="fas ' + icon + ' text-xs"></i>' +
                            '</div>' +
                            '<div class="flex-1 min-w-0">' +
                                '<p class="text-sm font-medium text-gray-900 dark:text-white truncate">' + escapeHtml(name) + '</p>' +
                                '<p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">' +
                                    '<i class="fas fa-clock mr-1"></i>' + escapeHtml(timeStr) + chatLink +
                                '</p>' +
                            '</div>' +
                        '</div>' +
                    '</div>'
                );
                totalBadge++;
            });
        }

        // Nelasītās čata ziņas
        if (byAppointment) {
            Object.keys(byAppointment).forEach(function (apptId) {
                var count = byAppointment[apptId];
                if (count > 0) {
                    items.push(
                        '<div class="px-4 py-3 border-b border-gray-50 dark:border-zinc-700/50 hover:bg-gray-50 dark:hover:bg-zinc-700/30 transition">' +
                            '<div class="flex items-start gap-3">' +
                                '<div class="w-8 h-8 rounded-full bg-red-50 dark:bg-red-900/20 flex items-center justify-center flex-shrink-0 mt-0.5">' +
                                    '<i class="fas fa-comment-dots text-red-500 text-xs"></i>' +
                                '</div>' +
                                '<div class="flex-1 min-w-0">' +
                                    '<p class="text-sm font-medium text-gray-900 dark:text-white">' + count + ' ' + (L.unread_messages || 'nelasīta(s) ziņa(s)') + '</p>' +
                                    '<a href="' + prefix + 'pages/chat.php?appointment_id=' + apptId + '" class="text-xs text-primary hover:underline">' + (L.open_chat || 'Atvērt čatu') + '</a>' +
                                '</div>' +
                            '</div>' +
                        '</div>'
                    );
                    totalBadge += count;
                }
            });
        }

        if (items.length === 0) {
            notifList.innerHTML =
                '<div class="px-4 py-8 text-center">' +
                    '<div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-zinc-700 flex items-center justify-center mx-auto mb-3">' +
                        '<i class="fas fa-bell-slash text-gray-400 dark:text-gray-500"></i>' +
                    '</div>' +
                    '<p class="text-sm text-gray-500 dark:text-gray-400">' + (L.no_new_notifications || 'Nav jaunu paziņojumu') + '</p>' +
                '</div>';
        } else {
            notifList.innerHTML = items.join('');
        }

        if (countLabel) {
            countLabel.textContent = items.length > 0 ? (L.notifications_count || '%d paziņojum(i)').replace('%d', items.length) : '';
        }

        updateBellBadge(totalBadge);

        // Atjaunojam arī badge uz appointment saitēm lapā
        updatePageBadges(unreadTotal, byAppointment);
    }

    function updatePageBadges(unreadTotal, byAppointment) {
        // Pierakstu saite navigācijā
        document.querySelectorAll('a[href*="appointments.php"]').forEach(function (link) {
            // Neaiztiekam zvaniņa dropdown saites
            if (link.closest('#notification-dropdown')) return;
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
        if (byAppointment) {
            document.querySelectorAll('a[href*="chat.php?appointment_id="]').forEach(function (link) {
                if (link.closest('#notification-dropdown')) return;
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
    }

    function fetchNotifications() {
        fetch(apiUrl, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                renderNotifications(
                    data.upcoming || [],
                    data.unread_total || 0,
                    data.unread_by_appointment || {}
                );
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
