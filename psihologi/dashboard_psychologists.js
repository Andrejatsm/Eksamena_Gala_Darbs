document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const psychologistsContainer = document.getElementById('psychologistsContainer');
    const paginationControls = document.getElementById('paginationControls');

    if (!searchInput || !psychologistsContainer || !paginationControls) {
        return;
    }

    let currentPage = 1;
    let currentSearch = '';

    const renderLoadingState = () => {
        psychologistsContainer.innerHTML = `
            <div class="col-span-full text-center py-10">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            </div>
        `;
    };

    function loadPsychologists(page = 1, search = '') {
        currentPage = page;
        currentSearch = search;
        renderLoadingState();

        fetch(`fetch_psychologists.php?page=${page}&search=${encodeURIComponent(search)}`)
            .then((response) => response.text())
            .then((data) => {
                psychologistsContainer.innerHTML = data;
                updatePagination();
            })
            .catch(() => {
                psychologistsContainer.innerHTML = `
                    <div class="col-span-full text-center py-12 text-red-600 dark:text-red-400 bg-white dark:bg-zinc-800 rounded-xl border border-dashed border-red-200 dark:border-red-900/40">
                        Neizdevās ielādēt psihologu sarakstu.
                    </div>
                `;
                paginationControls.innerHTML = '';
            });
    }

    function updatePagination() {
        const paginationData = document.getElementById('pagination-data');
        if (!paginationData) return;

        const totalPages = parseInt(paginationData.dataset.totalPages, 10);
        const currentPage = parseInt(paginationData.dataset.currentPage, 10);

        if (!Number.isFinite(totalPages) || totalPages <= 1) {
            paginationControls.innerHTML = '';
            return;
        }

        let paginationHTML = '';
        const prevDisabled = currentPage <= 1;
        const nextDisabled = currentPage >= totalPages;

        paginationHTML += `<li><button class="px-3 py-2 leading-tight border border-gray-300 dark:border-zinc-600 ${prevDisabled ? 'text-gray-300 dark:text-gray-600 cursor-not-allowed bg-gray-50 dark:bg-zinc-900' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700 bg-white dark:bg-zinc-800'} transition" data-page="${currentPage - 1}" ${prevDisabled ? 'disabled' : ''}>Iepriekšējais</button></li>`;
        for (let i = 1; i <= totalPages; i += 1) {
            const activeClass = i === currentPage ? 'bg-primary text-white' : 'bg-white dark:bg-zinc-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-zinc-700';
            paginationHTML += `<li><button class="px-3 py-2 leading-tight border border-gray-300 dark:border-zinc-600 ${activeClass} transition" data-page="${i}">${i}</button></li>`;
        }
        paginationHTML += `<li><button class="px-3 py-2 leading-tight border border-gray-300 dark:border-zinc-600 ${nextDisabled ? 'text-gray-300 dark:text-gray-600 cursor-not-allowed bg-gray-50 dark:bg-zinc-900' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700 bg-white dark:bg-zinc-800'} transition" data-page="${currentPage + 1}" ${nextDisabled ? 'disabled' : ''}>Tālāk</button></li>`;
        paginationControls.innerHTML = paginationHTML;

        paginationControls.querySelectorAll('button:not([disabled])').forEach((btn) => {
            btn.addEventListener('click', () => {
                const nextPage = parseInt(btn.dataset.page, 10);
                loadPsychologists(nextPage, currentSearch);
            });
        });
    }

    let searchTimeout;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadPsychologists(1, searchInput.value.trim());
        }, 300);
    });

    loadPsychologists();
});
