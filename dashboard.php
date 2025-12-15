<?php
require 'db.php';
require 'header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}
?>

<div class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <h3 class="text-3xl font-bold text-gray-900 dark:text-white">Pieejamie psihologi</h3>
        <div class="relative w-full md:w-1/3">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </span>
            <input type="text" id="searchInput" class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-full leading-5 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary sm:text-sm shadow-sm transition" placeholder="Meklēt speciālistu...">
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
            <div class="bg-gray-50 dark:bg-zinc-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-zinc-600 shadow-sm px-4 py-2 bg-white dark:bg-zinc-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="document.getElementById('psychologistModal').classList.add('hidden')">Aizvērt</button>
            </div>
        </div>
    </div>
</div>

<?php 
$conn->close();
require 'footer.php'; 
?>