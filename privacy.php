<?php
require_once __DIR__ . '/includes/lang.php';
$pageTitle = t('privacy_title');
require 'includes/header.php'; 
?>

<div class="flex-grow ui-container py-12">
    
    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white mb-8 text-center">
        <?php echo t('privacy_title'); ?>
    </h1>
    
    <div class="ui-card p-8 md:p-10">
        
        <p class="text-lg text-gray-600 dark:text-gray-300 mb-6 leading-relaxed">
            <?php echo t('privacy_intro'); ?>
        </p>

        <hr class="my-8 border-gray-200 dark:border-zinc-700">

        <h3 class="text-xl font-bold text-primary mb-3"><?php echo t('privacy_s1'); ?></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-3">
            <?php echo t('privacy_collected'); ?>
        </p>
        <ul class="list-disc list-inside text-gray-600 dark:text-gray-300 space-y-2 mb-6 ml-2">
            <li><strong class="text-gray-900 dark:text-white"><?php echo t('privacy_id'); ?></strong></li>
            <li><strong class="text-gray-900 dark:text-white"><?php echo t('privacy_contact'); ?></strong></li>
            <li><strong class="text-gray-900 dark:text-white"><?php echo t('privacy_access'); ?></strong></li>
        </ul>

        <h3 class="text-xl font-bold text-primary mb-3 mt-8"><?php echo t('privacy_s2'); ?></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-3">
            <?php echo t('privacy_gdpr'); ?>
        </p>
        <ul class="list-disc list-inside text-gray-600 dark:text-gray-300 space-y-2 mb-6 ml-2">
            <li><?php echo t('privacy_https'); ?></li>
            <li><?php echo t('privacy_hash'); ?></li>
            <li><?php echo t('privacy_no_share'); ?></li>
        </ul>
        
        <h3 class="text-xl font-bold text-primary mb-3 mt-8"><?php echo t('privacy_s3'); ?></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-3">
            <?php echo t('privacy_stripe'); ?>
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            <?php echo t('privacy_no_card'); ?>
        </p>
        
        <h3 class="text-xl font-bold text-primary mb-3 mt-8"><?php echo t('privacy_s4'); ?></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            <?php echo t('privacy_confidential'); ?>
        </p>

        <h3 class="text-xl font-bold text-primary mb-3 mt-8"><?php echo t('privacy_s5'); ?></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            <?php echo t('privacy_cookies'); ?>
        </p>

        <div class="mt-10 p-5 bg-white/60 dark:bg-zinc-700/50 border border-gray-200 dark:border-zinc-600 rounded-xl flex items-start gap-3">
            <i class="fas fa-info-circle text-primary mt-1"></i>
            <small class="text-gray-500 dark:text-gray-400 text-sm">
                <?php echo t('privacy_footer_note'); ?>
            </small>
        </div>

    </div>
</div>

<?php require 'includes/footer.php'; ?>