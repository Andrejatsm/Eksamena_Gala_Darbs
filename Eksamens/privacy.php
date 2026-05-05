<?php
require_once __DIR__ . '/includes/lang.php';
$pageTitle = t('privacy_title');
require 'includes/header.php'; 
?>

<div class="flex-grow ui-container py-12 max-w-6xl mx-auto">

    <div class="mb-10">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
            <?php echo t('privacy_title'); ?>
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo t('privacy_last_updated', '2026-04-09'); ?></p>
    </div>

    <div class="ui-card p-8 md:p-10">

        <p class="text-gray-600 dark:text-gray-300 mb-10 leading-relaxed">
            <?php echo t('privacy_intro'); ?>
        </p>

        <!-- 1. Data Controller -->
        <section class="mb-8">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-2">
                1. <?php echo t('privacy_s1'); ?>
            </h2>
            <p class="text-gray-600 dark:text-gray-300"><?php echo t('privacy_controller'); ?></p>
        </section>

        <hr class="border-gray-200 dark:border-zinc-700 mb-8">

        <!-- 2. Data Collected -->
        <section class="mb-8">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-2">
                2. <?php echo t('privacy_s2'); ?>
            </h2>
            <p class="text-gray-600 dark:text-gray-300 mb-3"><?php echo t('privacy_collected'); ?></p>
            <ul class="list-disc list-inside space-y-1 text-gray-600 dark:text-gray-300">
                <li><?php echo t('privacy_id'); ?></li>
                <li><?php echo t('privacy_contact'); ?></li>
                <li><?php echo t('privacy_access'); ?></li>
                <li><?php echo t('privacy_profile_data'); ?></li>
                <li><?php echo t('privacy_usage_data'); ?></li>
            </ul>
        </section>

        <hr class="border-gray-200 dark:border-zinc-700 mb-8">

        <!-- 3. Purpose & Legal Basis -->
        <section class="mb-8">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-2">
                3. <?php echo t('privacy_s3'); ?>
            </h2>
            <p class="text-gray-600 dark:text-gray-300 mb-3"><?php echo t('privacy_purpose_intro'); ?></p>
            <ul class="list-disc list-inside space-y-1 text-gray-600 dark:text-gray-300 mb-3">
                <li><?php echo t('privacy_purpose_auth'); ?></li>
                <li><?php echo t('privacy_purpose_service'); ?></li>
                <li><?php echo t('privacy_purpose_improve'); ?></li>
            </ul>
            <p class="text-gray-600 dark:text-gray-300 text-sm"><?php echo t('privacy_legal_basis'); ?></p>
        </section>

        <hr class="border-gray-200 dark:border-zinc-700 mb-8">

        <!-- 4. Data Security -->
        <section class="mb-8">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-2">
                4. <?php echo t('privacy_s4'); ?>
            </h2>
            <p class="text-gray-600 dark:text-gray-300 mb-3"><?php echo t('privacy_security_intro'); ?></p>
            <ul class="list-disc list-inside space-y-1 text-gray-600 dark:text-gray-300 mb-3">
                <li><?php echo t('privacy_https'); ?></li>
                <li><?php echo t('privacy_hash'); ?></li>
                <li><?php echo t('privacy_encryption'); ?></li>
                <li><?php echo t('privacy_auto_cleanup'); ?></li>
            </ul>
            <p class="text-gray-600 dark:text-gray-300"><?php echo t('privacy_no_share'); ?></p>
        </section>

        <hr class="border-gray-200 dark:border-zinc-700 mb-8">

        <!-- 5. Data Recipients & Third Parties -->
        <section class="mb-8">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-2">
                5. <?php echo t('privacy_s5'); ?>
            </h2>
            <p class="text-gray-600 dark:text-gray-300 mb-3"><?php echo t('privacy_recipients_intro'); ?></p>
            <ul class="list-disc list-inside space-y-1 text-gray-600 dark:text-gray-300 mb-3">
                <li><?php echo t('privacy_recipient_stripe'); ?></li>
                <li><?php echo t('privacy_recipient_google'); ?></li>
                <li><?php echo t('privacy_recipient_jitsi'); ?></li>
            </ul>
            <p class="text-gray-600 dark:text-gray-300"><?php echo t('privacy_no_sell'); ?></p>
        </section>

        <hr class="border-gray-200 dark:border-zinc-700 mb-8">

        <!-- 6. International Data Transfers -->
        <section class="mb-8">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-2">
                6. <?php echo t('privacy_s6'); ?>
            </h2>
            <p class="text-gray-600 dark:text-gray-300"><?php echo t('privacy_transfers'); ?></p>
        </section>

        <hr class="border-gray-200 dark:border-zinc-700 mb-8">

        <!-- 7. Payment Security -->
        <section class="mb-8">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-2">
                7. <?php echo t('privacy_s7'); ?>
            </h2>
            <p class="text-gray-600 dark:text-gray-300 mb-2"><?php echo t('privacy_stripe'); ?></p>
            <p class="text-gray-600 dark:text-gray-300"><?php echo t('privacy_no_card'); ?></p>
        </section>

        <hr class="border-gray-200 dark:border-zinc-700 mb-8">

        <!-- 8. Consultation Confidentiality -->
        <section class="mb-8">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-2">
                8. <?php echo t('privacy_s8'); ?>
            </h2>
            <p class="text-gray-600 dark:text-gray-300 mb-2"><?php echo t('privacy_confidential'); ?></p>
            <p class="text-gray-600 dark:text-gray-300 text-sm"><?php echo t('privacy_conf_detail'); ?></p>
        </section>

        <hr class="border-gray-200 dark:border-zinc-700 mb-8">

        <!-- 9. AI Assistant -->
        <section class="mb-8">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-2">
                9. <?php echo t('privacy_s9'); ?>
            </h2>
            <p class="text-gray-600 dark:text-gray-300 mb-2"><?php echo t('privacy_ai'); ?></p>
            <p class="text-gray-600 dark:text-gray-300"><?php echo t('privacy_ai_data'); ?></p>
        </section>

        <hr class="border-gray-200 dark:border-zinc-700 mb-8">

        <!-- 10. Cookies -->
        <section class="mb-8">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-2">
                10. <?php echo t('privacy_s10'); ?>
            </h2>
            <p class="text-gray-600 dark:text-gray-300 mb-4"><?php echo t('privacy_cookies'); ?></p>
            <div class="overflow-hidden rounded border border-gray-200 dark:border-zinc-700 mb-4">
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                        <tr class="text-gray-600 dark:text-gray-300">
                            <td class="px-4 py-3 font-mono whitespace-nowrap w-32">PHPSESSID</td>
                            <td class="px-4 py-3"><?php echo t('privacy_cookie_session'); ?></td>
                        </tr>
                        <tr class="text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-zinc-800/50">
                            <td class="px-4 py-3 font-mono whitespace-nowrap">lang</td>
                            <td class="px-4 py-3"><?php echo t('privacy_cookie_lang'); ?></td>
                        </tr>
                        <tr class="text-gray-600 dark:text-gray-300">
                            <td class="px-4 py-3 font-mono whitespace-nowrap">theme</td>
                            <td class="px-4 py-3"><?php echo t('privacy_cookie_theme'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="text-gray-600 dark:text-gray-300"><?php echo t('privacy_no_tracking'); ?></p>
        </section>

        <hr class="border-gray-200 dark:border-zinc-700 mb-8">

        <!-- 11. Data Retention -->
        <section class="mb-8">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-2">
                11. <?php echo t('privacy_s11'); ?>
            </h2>
            <p class="text-gray-600 dark:text-gray-300"><?php echo t('privacy_retention'); ?></p>
        </section>

        <hr class="border-gray-200 dark:border-zinc-700 mb-8">

        <!-- 12. Your Rights -->
        <section class="mb-8">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-2">
                12. <?php echo t('privacy_s12'); ?>
            </h2>
            <p class="text-gray-600 dark:text-gray-300 mb-3"><?php echo t('privacy_rights_intro'); ?></p>
            <ul class="list-disc list-inside space-y-1 text-gray-600 dark:text-gray-300 mb-3">
                <li><?php echo t('privacy_right_access'); ?></li>
                <li><?php echo t('privacy_right_rectify'); ?></li>
                <li><?php echo t('privacy_right_erase'); ?></li>
                <li><?php echo t('privacy_right_restrict'); ?></li>
                <li><?php echo t('privacy_right_object'); ?></li>
                <li><?php echo t('privacy_right_port'); ?></li>
                <li><?php echo t('privacy_right_withdraw'); ?></li>
                <li><?php echo t('privacy_right_complaint'); ?></li>
            </ul>
            <p class="text-gray-600 dark:text-gray-300 text-sm"><?php echo t('privacy_rights_howto'); ?></p>
        </section>

        <hr class="border-gray-200 dark:border-zinc-700 mb-8">

        <!-- Footer note -->
        <p class="text-sm text-gray-500 dark:text-gray-400">
            <?php echo t('privacy_footer_note'); ?>
        </p>

    </div>
</div>

<?php require 'includes/footer.php'; ?>