document.addEventListener('DOMContentLoaded', () => {
    const chartCanvas = document.getElementById('adminStatsChart');
    const accountsContainer = document.getElementById('adminAccountsContainer');
    const accountSearch = document.getElementById('adminAccountSearch');
    const roleFilter = document.getElementById('adminRoleFilter');
    const statusFilter = document.getElementById('adminStatusFilter');
    const resetAccountsBtn = document.getElementById('adminAccountsReset');
    const feedbackBox = document.getElementById('adminActionFeedback');
    const testPreviewModal = document.getElementById('testPreviewModal');
    const testPreviewFrame = document.getElementById('testPreviewFrame');
    const closeTestPreviewTop = document.getElementById('closeTestPreviewTop');
    const closeTestPreviewBottom = document.getElementById('closeTestPreviewBottom');
    const testPreviewBackdrop = document.getElementById('testPreviewBackdrop');
    const psychModal = document.getElementById('psychModal');
    const psychModalBackdrop = document.getElementById('psychModalBackdrop');
    const closePsychModalBtn = document.getElementById('closePsychModalBtn');
    const psychModalApproveBtn = document.getElementById('psychModalApproveBtn');
    const psychModalRejectBtn = document.getElementById('psychModalRejectBtn');
    const psychModalDeleteBtn = document.getElementById('psychModalDeleteBtn');
    const articleModal = document.getElementById('articleModal');
    const articleModalBackdrop = document.getElementById('articleModalBackdrop');
    const closeArticleModalBtn = document.getElementById('closeArticleModalBtn');
    let accountsPage = 1;
    let adminChart = null;

    const toPublicPath = (rawPath = '') => {
        const path = String(rawPath || '').trim();
        if (!path) {
            return '';
        }
        if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('/')) {
            return path;
        }
        if (path.startsWith('../')) {
            return path;
        }
        return `../${path.replace(/^\.?\//, '')}`;
    };

    const closeTestPreviewModal = () => {
        if (!testPreviewModal || !testPreviewFrame) return;
        testPreviewModal.classList.add('hidden');
        testPreviewFrame.src = '';
    };

    const closePsychModal = () => {
        if (psychModal) {
            psychModal.classList.add('hidden');
        }
    };

    const closeArticleModal = () => {
        if (articleModal) {
            articleModal.classList.add('hidden');
        }
    };

    const _appData = JSON.parse(document.getElementById('admin-dashboard-data')?.textContent || '{}');
    const adminDashboardChartData = _appData.chartStats ?? {};
    const adminAccountsConfig = _appData.accountsConfig ?? {};

    if (chartCanvas && window.Chart && adminDashboardChartData && Object.keys(adminDashboardChartData).length) {
        const chartData = adminDashboardChartData;
        adminChart = new window.Chart(chartCanvas, {
            type: 'bar',
            data: {
                labels: ['Lietotāji', 'Psihologi', 'Gaidošie psihologi', 'Pieraksti', 'Gaidošie raksti', 'Testi'],
                datasets: [{
                    label: 'Ierakstu skaits',
                    data: [
                        chartData.users ?? 0,
                        chartData.psychologists ?? 0,
                        chartData.pendingPsychologists ?? 0,
                        chartData.appointments ?? 0,
                        chartData.articles ?? 0,
                        chartData.tests ?? 0,
                    ],
                    backgroundColor: ['#14967f', '#095d7e', '#d97706', '#2563eb', '#ea580c', '#7c3aed'],
                    borderRadius: 10,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    x: {
                        ticks: {
                            color: '#6b7280',
                        },
                        grid: {
                            display: false,
                        },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            color: '#6b7280',
                        },
                        grid: {
                            color: 'rgba(107, 114, 128, 0.12)',
                        },
                    },
                },
            },
        });
    }

    const updateStats = (stats = {}) => {
        document.querySelectorAll('[data-stat-key]').forEach((element) => {
            const key = element.dataset.statKey;
            if (Object.prototype.hasOwnProperty.call(stats, key)) {
                element.textContent = String(stats[key] ?? 0);
            }
        });

        if (adminChart) {
            adminChart.data.datasets[0].data = [
                stats.users ?? 0,
                stats.psychologists ?? 0,
                stats.pendingPsychologists ?? 0,
                stats.appointments ?? 0,
                stats.articles ?? 0,
                stats.tests ?? 0,
            ];
            adminChart.update();
        }
    };

    const showFeedback = (message, type = 'success') => {
        if (!feedbackBox || !message) return;
        const styles = type === 'error'
            ? ['bg-red-50', 'dark:bg-red-900/20', 'border-red-200', 'dark:border-red-800', 'text-red-700', 'dark:text-red-400']
            : ['bg-green-50', 'dark:bg-green-900/20', 'border-green-200', 'dark:border-green-800', 'text-green-700', 'dark:text-green-400'];

        feedbackBox.className = 'mb-6 p-4 rounded-lg border ' + styles.join(' ');
        feedbackBox.innerHTML = `<i class="fas ${type === 'error' ? 'fa-triangle-exclamation' : 'fa-check-circle'} mr-2"></i>${message}`;
        feedbackBox.classList.remove('hidden');
    };

    const renderAccountsLoading = () => {
        if (!accountsContainer) return;
        accountsContainer.innerHTML = `
            <div class="px-6 py-12 text-center text-gray-600 dark:text-gray-400">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary mb-3"></div>
                <p>Ielādē kontu sarakstu...</p>
            </div>
        `;
    };

    const buildAccountsQuery = (page = 1) => {
        const params = new URLSearchParams();
        params.set('page', String(page));
        if (accountSearch?.value.trim()) {
            params.set('search', accountSearch.value.trim());
        }
        if (roleFilter?.value && roleFilter.value !== 'all') {
            params.set('role', roleFilter.value);
        }
        if (statusFilter?.value && statusFilter.value !== 'all') {
            params.set('status', statusFilter.value);
        }
        return params.toString();
    };

    const loadAccounts = async (page = 1) => {
        if (!accountsContainer || !adminAccountsConfig?.listUrl) return;
        accountsPage = page;
        renderAccountsLoading();
        try {
            const response = await fetch(`${adminAccountsConfig.listUrl}?${buildAccountsQuery(page)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const html = await response.text();
            accountsContainer.innerHTML = html;
        } catch {
            accountsContainer.innerHTML = `
                <div class="px-6 py-12 text-center text-red-600 dark:text-red-400">
                    <i class="fas fa-triangle-exclamation mr-2"></i>Neizdevās ielādēt kontu sarakstu.
                </div>
            `;
        }
    };

    const runAccountAction = async (action, accountId) => {
        if (!adminAccountsConfig?.actionUrl || !action || !accountId) return;
        const formData = new FormData();
        formData.set('action', action);
        formData.set('account_id', String(accountId));

        try {
            const response = await fetch(adminAccountsConfig.actionUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                showFeedback(payload.message || 'Neizdevās izpildīt darbību.', 'error');
                return;
            }

            if (payload.stats) {
                updateStats(payload.stats);
            }
            showFeedback(payload.message || 'Darbība izpildīta.');
            closePsychModal();
            loadAccounts(accountsPage);
        } catch {
            showFeedback('Notika kļūda, izpildot darbību.', 'error');
        }
    };

    document.querySelectorAll('.view-test-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (!testPreviewModal || !testPreviewFrame) return;
            testPreviewFrame.src = btn.dataset.testUrl || '';
            testPreviewModal.classList.remove('hidden');
        });
    });

    if (closeTestPreviewTop) {
        closeTestPreviewTop.addEventListener('click', closeTestPreviewModal);
    }
    if (closeTestPreviewBottom) {
        closeTestPreviewBottom.addEventListener('click', closeTestPreviewModal);
    }
    if (testPreviewBackdrop) {
        testPreviewBackdrop.addEventListener('click', closeTestPreviewModal);
    }
    if (psychModalBackdrop) {
        psychModalBackdrop.addEventListener('click', closePsychModal);
    }
    if (closePsychModalBtn) {
        closePsychModalBtn.addEventListener('click', closePsychModal);
    }
    const closePsychModalTopBtn = document.getElementById('closePsychModalTopBtn');
    if (closePsychModalTopBtn) {
        closePsychModalTopBtn.addEventListener('click', closePsychModal);
    }
    if (articleModalBackdrop) {
        articleModalBackdrop.addEventListener('click', closeArticleModal);
    }
    if (closeArticleModalBtn) {
        closeArticleModalBtn.addEventListener('click', closeArticleModal);
    }
    const closeArticleModalTopBtn = document.getElementById('closeArticleModalTopBtn');
    if (closeArticleModalTopBtn) {
        closeArticleModalTopBtn.addEventListener('click', closeArticleModal);
    }

    // Confirm before deleting an article in the modal
    document.querySelectorAll('.confirm-delete-article').forEach((btn) => {
        btn.closest('form')?.addEventListener('submit', (e) => {
            if (!window.confirm('Vai tiešām dzēst šo rakstu?')) e.preventDefault();
        });
    });

    // Confirm for forms with data-confirm-delete attribute (e.g. decline_test)
    document.querySelectorAll('form[data-confirm-delete]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (!window.confirm(form.dataset.confirmDelete || 'Vai tiešām turpināt?')) {
                e.preventDefault();
            }
        });
    });

    const tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            tabBtns.forEach((b) => b.classList.remove('bg-white', 'dark:bg-zinc-700', 'text-gray-900', 'dark:text-white', 'shadow-sm'));
            tabBtns.forEach((b) => b.classList.add('text-gray-600', 'dark:text-gray-400'));
            tabBtns.forEach((b) => b.classList.remove('text-gray-900', 'dark:text-white'));
            btn.classList.add('bg-white', 'dark:bg-zinc-700', 'text-gray-900', 'dark:text-white', 'shadow-sm');
            btn.classList.remove('text-gray-600', 'dark:text-gray-400');
            document.querySelectorAll('.tab-content').forEach((t) => t.classList.add('hidden'));
            const activeTab = document.getElementById(btn.dataset.tab);
            if (activeTab) {
                activeTab.classList.remove('hidden');
            }
        });
    });

    const openPsychModal = (btn) => {
            const psychModalName = document.getElementById('psychModalName');
            const psychModalSpec = document.getElementById('psychModalSpec');
            const psychModalExp = document.getElementById('psychModalExp');
            const psychModalEmail = document.getElementById('psychModalEmail');
            const psychModalPhone = document.getElementById('psychModalPhone');
            const psychModalDesc = document.getElementById('psychModalDesc');
            const certContainer = document.getElementById('psychModalCertContainer');

            if (!psychModalName || !psychModalSpec || !psychModalExp || !psychModalEmail || !psychModalPhone || !psychModalDesc || !psychModal || !certContainer || !psychModalApproveBtn || !psychModalRejectBtn || !psychModalDeleteBtn) {
                return;
            }

            psychModalName.textContent = btn.dataset.name || '';
            psychModalSpec.textContent = btn.dataset.spec || '';
            psychModalExp.textContent = btn.dataset.exp || '';
            psychModalEmail.textContent = btn.dataset.email || '';
            psychModalPhone.textContent = btn.dataset.phone || '';
            psychModalDesc.textContent = btn.dataset.desc || 'Apraksts nav sniegts.';
            psychModalApproveBtn.dataset.accountId = btn.dataset.id || '';
            psychModalRejectBtn.dataset.accountId = btn.dataset.id || '';
            psychModalDeleteBtn.dataset.accountId = btn.dataset.id || '';

            const isPending = btn.dataset.status === 'pending';
            psychModalApproveBtn.classList.toggle('hidden', !isPending);
            psychModalRejectBtn.classList.toggle('hidden', !isPending);

            if (btn.dataset.cert) {
                const certUrl = encodeURI(toPublicPath(btn.dataset.cert));
                certContainer.innerHTML = `<a href="${certUrl}" target="_blank" rel="noopener noreferrer" class="text-blue-500 hover:underline"><i class="fas fa-file-pdf mr-2"></i>Apskatīt failu</a>`;
            } else {
                certContainer.innerHTML = '<span class="text-red-500">Fails nav pievienots.</span>';
            }
            psychModal.classList.remove('hidden');
    };

    if (accountsContainer) {
        accountsContainer.addEventListener('click', (event) => {
            const pageBtn = event.target.closest('[data-admin-page]');
            if (pageBtn) {
                event.preventDefault();
                const nextPage = parseInt(pageBtn.dataset.adminPage || '1', 10);
                loadAccounts(Number.isFinite(nextPage) ? nextPage : 1);
                return;
            }

            const viewBtn = event.target.closest('.view-psych-btn');
            if (viewBtn) {
                openPsychModal(viewBtn);
                return;
            }

            const actionBtn = event.target.closest('[data-account-action]');
            if (actionBtn) {
                event.preventDefault();
                const action = actionBtn.dataset.accountAction || '';
                const accountId = actionBtn.dataset.accountId || '';
                const confirmMessage = actionBtn.dataset.confirm || '';
                if (confirmMessage && !window.confirm(confirmMessage)) {
                    return;
                }
                runAccountAction(action, accountId);
            }
        });
    }

    if (psychModalApproveBtn) {
        psychModalApproveBtn.addEventListener('click', () => {
            runAccountAction('approve_psych', psychModalApproveBtn.dataset.accountId || '');
        });
    }
    if (psychModalRejectBtn) {
        psychModalRejectBtn.addEventListener('click', () => {
            if (!window.confirm('Vai tiešām noraidīt šo profilu?')) return;
            runAccountAction('reject_psych', psychModalRejectBtn.dataset.accountId || '');
        });
    }
    if (psychModalDeleteBtn) {
        psychModalDeleteBtn.addEventListener('click', () => {
            if (!window.confirm('Vai tiešām dzēst šo psihologa kontu?')) return;
            runAccountAction('delete_psych', psychModalDeleteBtn.dataset.accountId || '');
        });
    }

    let accountSearchTimeout;
    if (accountSearch) {
        accountSearch.addEventListener('input', () => {
            clearTimeout(accountSearchTimeout);
            accountSearchTimeout = setTimeout(() => loadAccounts(1), 250);
        });
    }
    if (roleFilter) {
        roleFilter.addEventListener('change', () => loadAccounts(1));
    }
    if (statusFilter) {
        statusFilter.addEventListener('change', () => loadAccounts(1));
    }
    if (resetAccountsBtn) {
        resetAccountsBtn.addEventListener('click', () => {
            if (accountSearch) accountSearch.value = '';
            if (roleFilter) roleFilter.value = 'all';
            if (statusFilter) statusFilter.value = 'all';
            loadAccounts(1);
        });
    }

    loadAccounts(1);

    document.querySelectorAll('.view-article-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            const articleModalTitle = document.getElementById('articleModalTitle');
            const articleModalAuthor = document.getElementById('articleModalAuthor');
            const articleModalContent = document.getElementById('articleModalContent');
            const articleModalApproveId = document.getElementById('articleModalApproveId');
            const articleModalRejectId = document.getElementById('articleModalRejectId');
            const articleModalAccId = document.getElementById('articleModalAccId');

            if (!articleModalTitle || !articleModalAuthor || !articleModalContent || !articleModalApproveId || !articleModalRejectId || !articleModalAccId || !articleModal) {
                return;
            }

            articleModalTitle.textContent = btn.dataset.title || '';
            articleModalAuthor.textContent = 'Autors: ' + (btn.dataset.author || '');
            articleModalContent.innerHTML = btn.dataset.content || '';
            articleModalApproveId.value = btn.dataset.id || '';
            articleModalRejectId.value = btn.dataset.id || '';
            articleModalAccId.value = btn.dataset.acc || '';
            articleModal.classList.remove('hidden');
        });
    });
});
