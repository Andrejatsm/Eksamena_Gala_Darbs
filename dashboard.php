<?php
session_start();

if (!isset($_SESSION['account_id'], $_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

require 'header.php';
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
        <div class="flex items-center gap-2" id="paginationControls"></div>
    </div>
</div>

<script src="psihologi/dashboard_psychologists.js?v=<?php echo filemtime(__DIR__ . '/psihologi/dashboard_psychologists.js'); ?>"></script>

<?php 
require 'footer.php'; 
?>