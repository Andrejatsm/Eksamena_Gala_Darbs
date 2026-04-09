<?php
session_start();

if (!isset($_SESSION['account_id'], $_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

require '../includes/header.php';
?>

<div class="flex-grow ui-container py-10">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h3 class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo t('available_psychologists'); ?></h3>
        <div class="relative w-full md:w-1/3">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </span>
            <input type="text" id="searchInput" class="ui-input pl-10" placeholder="<?php echo t('search_specialist'); ?>">
        </div>
    </div>

    <div id="filterBar" class="flex flex-wrap gap-3 mb-8 items-end">
        <div class="w-full sm:w-auto">
            <label for="filterSpecialization" class="field-label"><?php echo t('specialization'); ?></label>
            <select id="filterSpecialization" class="select-control text-sm py-2">
                <option value=""><?php echo t('all_specializations'); ?></option>
            </select>
        </div>
        <div class="w-full sm:w-auto">
            <label for="filterConsultation" class="field-label"><?php echo t('consultation_type'); ?></label>
            <select id="filterConsultation" class="select-control text-sm py-2">
                <option value=""><?php echo t('all_types'); ?></option>
                <option value="online"><?php echo t('online'); ?></option>
                <option value="in_person"><?php echo t('in_person'); ?></option>
            </select>
        </div>
        <div class="w-full sm:w-auto">
            <label for="filterExperience" class="field-label"><?php echo t('experience_min'); ?></label>
            <select id="filterExperience" class="select-control text-sm py-2">
                <option value=""><?php echo t('any_experience'); ?></option>
                <option value="1"><?php echo t('years_1'); ?></option>
                <option value="3"><?php echo t('years_3'); ?></option>
                <option value="5"><?php echo t('years_5'); ?></option>
                <option value="10"><?php echo t('years_10'); ?></option>
            </select>
        </div>
        <button type="button" id="clearFiltersBtn" class="button-secondary text-sm py-2 px-4 h-[2.5rem]">
            <i class="fas fa-times mr-1"></i> <?php echo t('clear'); ?>
        </button>
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

<script src="../specialist/dashboard_psychologists.js?v=<?php echo filemtime(__DIR__ . '/../specialist/dashboard_psychologists.js'); ?>"></script>

<?php 
require '../includes/footer.php'; 
?>