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

    // Katru reizi ielādējam tikai vajadzīgo lapu un meklēšanas frāzi, lai saraksts būtu ātrs arī pie lielāka psihologu skaita.
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

    // Lapošanas pogas tiek būvētas no HTML datu atribūtiem, ko backend ieliek neredzamā blokā.
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

        const btnTeal = 'px-3 py-1.5 rounded-lg bg-[#ccecee] text-[#095d7e] hover:bg-[#b8dde0] font-semibold text-sm transition';
        const btnTealDisabled = 'px-3 py-1.5 rounded-lg bg-[#ccecee]/40 text-[#095d7e]/40 font-semibold text-sm cursor-not-allowed';

        paginationHTML += `<button class="${prevDisabled ? btnTealDisabled : btnTeal}" data-page="${currentPage - 1}" ${prevDisabled ? 'disabled' : ''}><i class="fas fa-chevron-left mr-1"></i>Iepriekšējā</button>`;
        paginationHTML += `<span class="px-2 py-1.5 text-sm text-gray-600 dark:text-gray-400">Lapa ${currentPage} no ${totalPages}</span>`;
        paginationHTML += `<button class="${nextDisabled ? btnTealDisabled : btnTeal}" data-page="${currentPage + 1}" ${nextDisabled ? 'disabled' : ''}>Nākamā<i class="fas fa-chevron-right ml-1"></i></button>`;
        paginationControls.innerHTML = paginationHTML;

        paginationControls.querySelectorAll('button:not([disabled])').forEach((btn) => {
            btn.addEventListener('click', () => {
                const nextPage = parseInt(btn.dataset.page, 10);
                loadPsychologists(nextPage, currentSearch);
            });
        });
    }

    let searchTimeout;
    // Neliels debounce pasargā no liekiem pieprasījumiem katram taustiņa spiedienam.
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadPsychologists(1, searchInput.value.trim());
        }, 300);
    });

    loadPsychologists();
});
