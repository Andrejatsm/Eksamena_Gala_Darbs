<?php
require 'db.php';
require 'header.php';

if (!isset($_SESSION['account_id'], $_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}
?>

<div class="flex-grow ui-container py-10">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <h3 class="text-3xl font-bold text-gray-900 dark:text-white">Pieejamie psihologi</h3>
        <div class="relative w-full md:w-1/3">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </span>
            <input type="text" id="searchInput" class="ui-input pl-10" placeholder="Meklēt speciālistu...">
        </div>
    </div>
    
    <div id="psychologistsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="col-span-full text-center py-10">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        </div>
    </div>

    <div class="mt-10 flex justify-center">
        <nav aria-label="Pagination">
            <ul class="inline-flex items-center -space-x-px rounded-md shadow-sm bg-white dark:bg-zinc-800" id="paginationControls">
                </ul>
        </nav>
    </div>
</div>

<div id="psychologistModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('psychologistModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <div class="bg-white dark:bg-zinc-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start justify-center">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full" id="modalBodyContent">
                        </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-zinc-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                <button type="button" id="bookBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primaryHover focus:outline-none sm:w-auto sm:text-sm transition">Pierakstīties</button>
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-zinc-600 shadow-sm px-4 py-2 bg-white dark:bg-zinc-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="document.getElementById('psychologistModal').classList.add('hidden')">Aizvērt</button>
            </div>
        </div>
    </div>
</div>

<div id="bookingModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="booking-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('bookingModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
            <div class="bg-white dark:bg-zinc-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4" id="booking-title">Pierakstīties konsultācijai</h3>
                <form id="bookingForm" method="POST" action="checkout.php">
                    <input type="hidden" name="psychologist_account_id" id="bookingPsychologistId">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Konsultācijas veids</label>
                            <select name="consultation_type" class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2.5 border transition">
                                <option value="online">Tiešsaiste</option>
                                <option value="in_person">Klātienē</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vēlamais laiks</label>
                            <input type="datetime-local" name="scheduled_at" required class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2.5 border transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ziņojums speciālistam (neobligāti)</label>
                            <textarea name="message" rows="3" class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2.5 border transition" placeholder="Aprakstiet savu situāciju..."></textarea>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primaryHover focus:outline-none sm:w-auto sm:text-sm transition">Turpināt uz apmaksu</button>
                        <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-zinc-600 shadow-sm px-4 py-2 bg-white dark:bg-zinc-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="document.getElementById('bookingModal').classList.add('hidden')">Atcelt</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const psychologistsContainer = document.getElementById('psychologistsContainer');
    const paginationControls = document.getElementById('paginationControls');
    const psychologistModal = document.getElementById('psychologistModal');
    const modalBodyContent = document.getElementById('modalBodyContent');
    const bookingModal = document.getElementById('bookingModal');
    const bookBtn = document.getElementById('bookBtn');
    const bookingForm = document.getElementById('bookingForm');

    let currentPage = 1;

    function loadPsychologists(page = 1, search = '') {
        fetch(`fetch_psychologists.php?page=${page}&search=${encodeURIComponent(search)}`)
            .then(response => response.text())
            .then(data => {
                psychologistsContainer.innerHTML = data;
                updatePagination();
                attachDetailListeners();
            });
    }

    function updatePagination() {
        const paginationData = document.getElementById('pagination-data');
        if (!paginationData) return;

        const totalPages = parseInt(paginationData.dataset.totalPages);
        const currentPage = parseInt(paginationData.dataset.currentPage);

        let paginationHTML = '';
        for (let i = 1; i <= totalPages; i++) {
            const activeClass = i === currentPage ? 'bg-primary text-white' : 'bg-white dark:bg-zinc-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-zinc-700';
            paginationHTML += `<li><button class="px-3 py-2 leading-tight border border-gray-300 dark:border-zinc-600 ${activeClass} transition" data-page="${i}">${i}</button></li>`;
        }
        paginationControls.innerHTML = paginationHTML;

        // Attach pagination listeners
        paginationControls.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('click', () => {
                currentPage = parseInt(btn.dataset.page);
                loadPsychologists(currentPage, searchInput.value);
            });
        });
    }

    function attachDetailListeners() {
        document.querySelectorAll('.details-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const data = btn.dataset;
                modalBodyContent.innerHTML = `
                    <div class="text-center">
                        <img src="${data.attels}" class="w-24 h-24 rounded-full mx-auto mb-4 object-cover" alt="Psihologs">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">${data.vards}</h3>
                        <p class="text-sm text-primary font-medium">${data.spec}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">${data.pieredze} gadu pieredze</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">${data.apraksts || 'Nav apraksta.'}</p>
                        <p class="text-lg font-bold text-primary mt-4">${data.cena} €/stunda</p>
                    </div>
                `;
                bookingForm.querySelector('#bookingPsychologistId').value = data.psychologistId;
                psychologistModal.classList.remove('hidden');
            });
        });
    }

    // Book button
    bookBtn.addEventListener('click', () => {
        psychologistModal.classList.add('hidden');
        bookingModal.classList.remove('hidden');
    });

    // Search
    let searchTimeout;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadPsychologists(1, searchInput.value);
        }, 300);
    });

    // Initial load
    loadPsychologists();
});
</script>

<?php 
$conn->close();
require 'footer.php'; 
?>