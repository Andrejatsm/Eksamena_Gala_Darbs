/**
 * Saprasts — universāla modālā paziņojumu sistēma.
 * Aizvieto alert(), confirm() un banner notifikācijas ar vienotu UI.
 *
 * API:
 *   SaprastsToast.success(msg)          — zaļš toast
 *   SaprastsToast.error(msg)            — sarkans toast
 *   SaprastsToast.info(msg)             — zils toast
 *   SaprastsToast.warning(msg)          — dzeltens toast
 *   SaprastsConfirm.show(msg)           — atgriež Promise<boolean>
 *   SaprastsConfirm.show(msg, options)  — ar pielāgotu pogu tekstu
 */
(function () {
    'use strict';

    /* ── Toast Container ─────────────────────────────── */
    var container = document.createElement('div');
    container.id = 'saprasts-toast-container';
    container.className = 'fixed top-4 right-4 z-[9999] flex flex-col gap-3 pointer-events-none max-w-sm w-full';
    document.body.appendChild(container);

    var ICONS = {
        success: 'fa-check-circle',
        error: 'fa-times-circle',
        info: 'fa-info-circle',
        warning: 'fa-exclamation-triangle'
    };

    var COLORS = {
        success: {
            bg: 'bg-[#e2fcd6] dark:bg-[#1a3a1a]',
            border: 'border-[#14967f]/30 dark:border-[#14967f]/40',
            text: 'text-[#095d7e] dark:text-[#ccecee]',
            icon: 'text-[#14967f]'
        },
        error: {
            bg: 'bg-red-50 dark:bg-red-900/20',
            border: 'border-red-200 dark:border-red-800/50',
            text: 'text-red-700 dark:text-red-300',
            icon: 'text-red-500'
        },
        info: {
            bg: 'bg-[#f1f9ff] dark:bg-[#0d2d3a]',
            border: 'border-[#ccecee] dark:border-[#095d7e]/40',
            text: 'text-[#095d7e] dark:text-[#ccecee]',
            icon: 'text-[#14967f]'
        },
        warning: {
            bg: 'bg-[#ccecee]/40 dark:bg-[#095d7e]/20',
            border: 'border-[#095d7e]/30 dark:border-[#095d7e]/40',
            text: 'text-[#095d7e] dark:text-[#ccecee]',
            icon: 'text-[#095d7e] dark:text-[#ccecee]'
        }
    };

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function showToast(message, type, duration) {
        type = type || 'info';
        duration = duration || 4000;
        var c = COLORS[type] || COLORS.info;
        var icon = ICONS[type] || ICONS.info;

        var toast = document.createElement('div');
        toast.className = 'pointer-events-auto flex items-start gap-3 p-4 rounded-xl border shadow-lg transition-all duration-300 opacity-0 translate-x-4 ' +
            c.bg + ' ' + c.border;

        toast.innerHTML =
            '<div class="flex-shrink-0 mt-0.5"><i class="fas ' + icon + ' ' + c.icon + '"></i></div>' +
            '<div class="flex-1 text-sm font-medium ' + c.text + '">' + escapeHtml(message) + '</div>' +
            '<button class="flex-shrink-0 ' + c.text + ' hover:opacity-70 transition" aria-label="Aizvērt">' +
                '<i class="fas fa-times text-xs"></i>' +
            '</button>';

        toast.querySelector('button').addEventListener('click', function () {
            removeToast(toast);
        });

        container.appendChild(toast);

        // Animate in
        requestAnimationFrame(function () {
            toast.classList.remove('opacity-0', 'translate-x-4');
            toast.classList.add('opacity-100', 'translate-x-0');
        });

        // Auto-remove
        setTimeout(function () {
            removeToast(toast);
        }, duration);
    }

    function removeToast(toast) {
        if (!toast || !toast.parentNode) return;
        toast.classList.remove('opacity-100', 'translate-x-0');
        toast.classList.add('opacity-0', 'translate-x-4');
        setTimeout(function () {
            if (toast.parentNode) toast.parentNode.removeChild(toast);
        }, 300);
    }

    /* ── Confirm Modal ───────────────────────────────── */
    var modalHtml =
        '<div id="saprasts-confirm-backdrop" class="fixed inset-0 z-[10000] bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity duration-200 opacity-0">' +
            '<div id="saprasts-confirm-box" class="bg-white dark:bg-zinc-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-zinc-700 w-full max-w-md transform transition-all duration-200 scale-95 opacity-0">' +
                '<div class="p-6">' +
                    '<div class="flex items-center gap-3 mb-4">' +
                        '<div class="w-10 h-10 rounded-full bg-[#ccecee] dark:bg-[#095d7e]/30 flex items-center justify-center flex-shrink-0">' +
                            '<i class="fas fa-exclamation-triangle text-[#095d7e] dark:text-[#ccecee]"></i>' +
                        '</div>' +
                        '<h3 class="text-lg font-bold text-gray-900 dark:text-white">Apstiprinājums</h3>' +
                    '</div>' +
                    '<p id="saprasts-confirm-message" class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed"></p>' +
                '</div>' +
                '<div class="border-t border-gray-100 dark:border-zinc-700 px-6 py-4 flex justify-end gap-3">' +
                    '<button id="saprasts-confirm-cancel" class="px-4 py-2 text-sm font-semibold rounded-lg border border-[#ccecee] dark:border-zinc-600 text-[#095d7e] dark:text-[#ccecee] bg-white dark:bg-zinc-700 hover:bg-[#f1f9ff] dark:hover:bg-zinc-600 transition">Atcelt</button>' +
                    '<button id="saprasts-confirm-ok" class="px-4 py-2 text-sm font-semibold rounded-lg bg-[#095d7e] hover:bg-[#14967f] text-white transition">Apstiprināt</button>' +
                '</div>' +
            '</div>' +
        '</div>';

    var modalContainer = document.createElement('div');
    modalContainer.innerHTML = modalHtml;
    var confirmModal = modalContainer.firstChild;
    document.body.appendChild(confirmModal);
    confirmModal.classList.add('hidden');

    var confirmResolve = null;

    function showConfirm(message, options) {
        options = options || {};
        var msgEl = document.getElementById('saprasts-confirm-message');
        var okBtn = document.getElementById('saprasts-confirm-ok');
        var cancelBtn = document.getElementById('saprasts-confirm-cancel');
        var backdrop = document.getElementById('saprasts-confirm-backdrop');
        var box = document.getElementById('saprasts-confirm-box');

        if (msgEl) msgEl.textContent = message;
        if (okBtn) okBtn.textContent = options.okText || 'Apstiprināt';
        if (cancelBtn) cancelBtn.textContent = options.cancelText || 'Atcelt';

        // Style OK button based on type
        if (okBtn) {
            okBtn.className = 'px-4 py-2 text-sm font-semibold rounded-lg text-white transition ';
            if (options.type === 'danger') {
                okBtn.className += 'bg-red-500 hover:bg-red-600';
            } else {
                okBtn.className += 'bg-[#095d7e] hover:bg-[#14967f]';
            }
        }

        confirmModal.classList.remove('hidden');

        // Animate in
        requestAnimationFrame(function () {
            if (backdrop) {
                backdrop.classList.remove('opacity-0');
                backdrop.classList.add('opacity-100');
            }
            if (box) {
                box.classList.remove('scale-95', 'opacity-0');
                box.classList.add('scale-100', 'opacity-100');
            }
        });

        return new Promise(function (resolve) {
            confirmResolve = resolve;
        });
    }

    function closeConfirm(result) {
        var backdrop = document.getElementById('saprasts-confirm-backdrop');
        var box = document.getElementById('saprasts-confirm-box');

        if (backdrop) {
            backdrop.classList.remove('opacity-100');
            backdrop.classList.add('opacity-0');
        }
        if (box) {
            box.classList.remove('scale-100', 'opacity-100');
            box.classList.add('scale-95', 'opacity-0');
        }

        setTimeout(function () {
            confirmModal.classList.add('hidden');
        }, 200);

        if (confirmResolve) {
            confirmResolve(result);
            confirmResolve = null;
        }
    }

    document.getElementById('saprasts-confirm-ok').addEventListener('click', function () {
        closeConfirm(true);
    });
    document.getElementById('saprasts-confirm-cancel').addEventListener('click', function () {
        closeConfirm(false);
    });
    document.getElementById('saprasts-confirm-backdrop').addEventListener('click', function (e) {
        if (e.target === e.currentTarget) closeConfirm(false);
    });

    /* ── Public API ──────────────────────────────────── */
    window.SaprastsToast = {
        success: function (msg, duration) { showToast(msg, 'success', duration); },
        error: function (msg, duration) { showToast(msg, 'error', duration); },
        info: function (msg, duration) { showToast(msg, 'info', duration); },
        warning: function (msg, duration) { showToast(msg, 'warning', duration); }
    };

    window.SaprastsConfirm = {
        show: showConfirm
    };
})();
