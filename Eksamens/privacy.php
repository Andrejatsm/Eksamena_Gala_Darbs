<?php
require_once __DIR__ . '/includes/lang.php';
$pageTitle = t('privacy_title');
require 'includes/header.php'; 
?>

<div class="flex-grow ui-container py-12 max-w-6xl mx-auto">
    
    <div class="text-center mb-10">
        <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white mb-3">
            <i class="fas fa-shield-alt text-primary mr-2"></i><?php echo t('privacy_title'); ?>
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo t('privacy_last_updated', '2026-04-09'); ?></p>
    </div>

    <div class="ui-card p-8 md:p-10 space-y-10">

        <!-- Intro -->
        <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed">
            <?php echo t('privacy_intro'); ?>
        </p>

        <!-- 1. Data Controller -->
        <section>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary text-sm font-bold">1</span>
                <?php echo t('privacy_s1'); ?>
            </h3>
            <p class="text-gray-600 dark:text-gray-300 ml-10"><?php echo t('privacy_controller'); ?></p>
        </section>

        <hr class="border-gray-200 dark:border-zinc-700">

        <!-- 2. Data Collected -->
        <section>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary text-sm font-bold">2</span>
                <?php echo t('privacy_s2'); ?>
            </h3>
            <p class="text-gray-600 dark:text-gray-300 mb-3 ml-10"><?php echo t('privacy_collected'); ?></p>
            <ul class="space-y-2 ml-10">
                <li class="flex items-start gap-2 text-gray-600 dark:text-gray-300"><i class="fas fa-user text-primary mt-1 w-4 text-center"></i> <?php echo t('privacy_id'); ?></li>
                <li class="flex items-start gap-2 text-gray-600 dark:text-gray-300"><i class="fas fa-envelope text-primary mt-1 w-4 text-center"></i> <?php echo t('privacy_contact'); ?></li>
                <li class="flex items-start gap-2 text-gray-600 dark:text-gray-300"><i class="fas fa-key text-primary mt-1 w-4 text-center"></i> <?php echo t('privacy_access'); ?></li>
                <li class="flex items-start gap-2 text-gray-600 dark:text-gray-300"><i class="fas fa-id-badge text-primary mt-1 w-4 text-center"></i> <?php echo t('privacy_profile_data'); ?></li>
                <li class="flex items-start gap-2 text-gray-600 dark:text-gray-300"><i class="fas fa-chart-bar text-primary mt-1 w-4 text-center"></i> <?php echo t('privacy_usage_data'); ?></li>
            </ul>
        </section>

        <hr class="border-gray-200 dark:border-zinc-700">

        <!-- 3. Purpose & Legal Basis -->
        <section>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary text-sm font-bold">3</span>
                <?php echo t('privacy_s3'); ?>
            </h3>
            <p class="text-gray-600 dark:text-gray-300 mb-3 ml-10"><?php echo t('privacy_purpose_intro'); ?></p>
            <ul class="space-y-2 ml-10">
                <li class="flex items-start gap-2 text-gray-600 dark:text-gray-300"><i class="fas fa-sign-in-alt text-primary mt-1 w-4 text-center"></i> <?php echo t('privacy_purpose_auth'); ?></li>
                <li class="flex items-start gap-2 text-gray-600 dark:text-gray-300"><i class="fas fa-headset text-primary mt-1 w-4 text-center"></i> <?php echo t('privacy_purpose_service'); ?></li>
                <li class="flex items-start gap-2 text-gray-600 dark:text-gray-300"><i class="fas fa-tools text-primary mt-1 w-4 text-center"></i> <?php echo t('privacy_purpose_improve'); ?></li>
            </ul>
            <p class="text-gray-600 dark:text-gray-300 mt-4 ml-10 text-sm bg-gray-50 dark:bg-zinc-700/50 rounded-lg p-4"><?php echo t('privacy_legal_basis'); ?></p>
        </section>

        <hr class="border-gray-200 dark:border-zinc-700">

        <!-- 4. Data Security -->
        <section>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary text-sm font-bold">4</span>
                <?php echo t('privacy_s4'); ?>
            </h3>
            <p class="text-gray-600 dark:text-gray-300 mb-3 ml-10"><?php echo t('privacy_security_intro'); ?></p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 ml-10">
                <div class="flex items-start gap-2 text-gray-600 dark:text-gray-300 bg-[#e2fcd6]/30 dark:bg-[#14967f]/10 rounded-lg p-3">
                    <i class="fas fa-lock text-[#14967f] mt-0.5"></i>
                    <span class="text-sm"><?php echo t('privacy_https'); ?></span>
                </div>
                <div class="flex items-start gap-2 text-gray-600 dark:text-gray-300 bg-[#e2fcd6]/30 dark:bg-[#14967f]/10 rounded-lg p-3">
                    <i class="fas fa-hashtag text-[#14967f] mt-0.5"></i>
                    <span class="text-sm"><?php echo t('privacy_hash'); ?></span>
                </div>
                <div class="flex items-start gap-2 text-gray-600 dark:text-gray-300 bg-[#e2fcd6]/30 dark:bg-[#14967f]/10 rounded-lg p-3">
                    <i class="fas fa-shield-alt text-[#14967f] mt-0.5"></i>
                    <span class="text-sm"><?php echo t('privacy_encryption'); ?></span>
                </div>
                <div class="flex items-start gap-2 text-gray-600 dark:text-gray-300 bg-[#e2fcd6]/30 dark:bg-[#14967f]/10 rounded-lg p-3">
                    <i class="fas fa-clock text-[#14967f] mt-0.5"></i>
                    <span class="text-sm"><?php echo t('privacy_auto_cleanup'); ?></span>
                </div>
            </div>
            <p class="text-gray-600 dark:text-gray-300 mt-3 ml-10"><i class="fas fa-user-shield text-primary mr-1"></i> <?php echo t('privacy_no_share'); ?></p>
        </section>

        <hr class="border-gray-200 dark:border-zinc-700">

        <!-- 5. Data Recipients & Third Parties -->
        <section>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary text-sm font-bold">5</span>
                <?php echo t('privacy_s5'); ?>
            </h3>
            <p class="text-gray-600 dark:text-gray-300 mb-3 ml-10"><?php echo t('privacy_recipients_intro'); ?></p>
            <ul class="space-y-2 ml-10">
                <li class="flex items-start gap-2 text-gray-600 dark:text-gray-300"><i class="fas fa-credit-card text-primary mt-1 w-4 text-center"></i> <?php echo t('privacy_recipient_stripe'); ?></li>
                <li class="flex items-start gap-2 text-gray-600 dark:text-gray-300"><i class="fas fa-robot text-primary mt-1 w-4 text-center"></i> <?php echo t('privacy_recipient_google'); ?></li>
                <li class="flex items-start gap-2 text-gray-600 dark:text-gray-300"><i class="fas fa-video text-primary mt-1 w-4 text-center"></i> <?php echo t('privacy_recipient_jitsi'); ?></li>
            </ul>
            <p class="text-gray-600 dark:text-gray-300 mt-3 ml-10"><i class="fas fa-ban text-red-500 mr-1"></i> <?php echo t('privacy_no_sell'); ?></p>
        </section>

        <hr class="border-gray-200 dark:border-zinc-700">

        <!-- 6. International Data Transfers -->
        <section>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary text-sm font-bold">6</span>
                <?php echo t('privacy_s6'); ?>
            </h3>
            <p class="text-gray-600 dark:text-gray-300 ml-10"><?php echo t('privacy_transfers'); ?></p>
        </section>

        <hr class="border-gray-200 dark:border-zinc-700">

        <!-- 7. Payment Security -->
        <section>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary text-sm font-bold">7</span>
                <?php echo t('privacy_s7'); ?>
            </h3>
            <div class="ml-10 space-y-2">
                <p class="text-gray-600 dark:text-gray-300"><?php echo t('privacy_stripe'); ?></p>
                <p class="text-gray-600 dark:text-gray-300"><?php echo t('privacy_no_card'); ?></p>
            </div>
        </section>

        <hr class="border-gray-200 dark:border-zinc-700">

        <!-- 8. Consultation Confidentiality -->
        <section>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary text-sm font-bold">8</span>
                <?php echo t('privacy_s8'); ?>
            </h3>
            <div class="ml-10 space-y-2">
                <p class="text-gray-600 dark:text-gray-300"><?php echo t('privacy_confidential'); ?></p>
                <p class="text-gray-600 dark:text-gray-300 text-sm"><?php echo t('privacy_conf_detail'); ?></p>
            </div>
        </section>

        <hr class="border-gray-200 dark:border-zinc-700">

        <!-- 9. AI Assistant -->
        <section>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary text-sm font-bold">9</span>
                <?php echo t('privacy_s9'); ?>
            </h3>
            <div class="ml-10 space-y-2">
                <p class="text-gray-600 dark:text-gray-300"><?php echo t('privacy_ai'); ?></p>
                <p class="text-gray-600 dark:text-gray-300"><?php echo t('privacy_ai_data'); ?></p>
            </div>
        </section>

        <hr class="border-gray-200 dark:border-zinc-700">

        <!-- 10. Cookies -->
        <section>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary text-sm font-bold">10</span>
                <?php echo t('privacy_s10'); ?>
            </h3>
            <p class="text-gray-600 dark:text-gray-300 mb-3 ml-10"><?php echo t('privacy_cookies'); ?></p>
            <div class="ml-10 overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700">
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                        <tr class="text-gray-600 dark:text-gray-300">
                            <td class="px-4 py-3 font-medium whitespace-nowrap"><code class="text-primary">PHPSESSID</code></td>
                            <td class="px-4 py-3"><?php echo t('privacy_cookie_session'); ?></td>
                        </tr>
                        <tr class="text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-zinc-800/50">
                            <td class="px-4 py-3 font-medium whitespace-nowrap"><code class="text-primary">lang</code></td>
                            <td class="px-4 py-3"><?php echo t('privacy_cookie_lang'); ?></td>
                        </tr>
                        <tr class="text-gray-600 dark:text-gray-300">
                            <td class="px-4 py-3 font-medium whitespace-nowrap"><code class="text-primary">theme</code></td>
                            <td class="px-4 py-3"><?php echo t('privacy_cookie_theme'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="text-gray-600 dark:text-gray-300 mt-3 ml-10"><?php echo t('privacy_no_tracking'); ?></p>
        </section>

        <hr class="border-gray-200 dark:border-zinc-700">

        <!-- 11. Data Retention -->
        <section>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary text-sm font-bold">11</span>
                <?php echo t('privacy_s11'); ?>
            </h3>
            <p class="text-gray-600 dark:text-gray-300 ml-10"><?php echo t('privacy_retention'); ?></p>
        </section>

        <hr class="border-gray-200 dark:border-zinc-700">

        <!-- 12. Your Rights -->
        <section>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-[#095d7e]/10 text-[#095d7e] dark:text-[#ccecee] text-sm font-bold">12</span>
                <?php echo t('privacy_s12'); ?>
            </h3>
            <p class="text-gray-600 dark:text-gray-300 mb-3 ml-10"><?php echo t('privacy_rights_intro'); ?></p>
            <ul class="space-y-2 ml-10">
                <li class="flex items-start gap-2 text-gray-600 dark:text-gray-300"><i class="fas fa-eye text-[#095d7e] dark:text-[#ccecee] mt-1 w-4 text-center"></i> <?php echo t('privacy_right_access'); ?></li>
                <li class="flex items-start gap-2 text-gray-600 dark:text-gray-300"><i class="fas fa-pen text-[#095d7e] dark:text-[#ccecee] mt-1 w-4 text-center"></i> <?php echo t('privacy_right_rectify'); ?></li>
                <li class="flex items-start gap-2 text-gray-600 dark:text-gray-300"><i class="fas fa-trash-alt text-[#095d7e] dark:text-[#ccecee] mt-1 w-4 text-center"></i> <?php echo t('privacy_right_erase'); ?></li>
                <li class="flex items-start gap-2 text-gray-600 dark:text-gray-300"><i class="fas fa-pause-circle text-[#095d7e] dark:text-[#ccecee] mt-1 w-4 text-center"></i> <?php echo t('privacy_right_restrict'); ?></li>
                <li class="flex items-start gap-2 text-gray-600 dark:text-gray-300"><i class="fas fa-hand-paper text-[#095d7e] dark:text-[#ccecee] mt-1 w-4 text-center"></i> <?php echo t('privacy_right_object'); ?></li>
                <li class="flex items-start gap-2 text-gray-600 dark:text-gray-300"><i class="fas fa-file-export text-[#095d7e] dark:text-[#ccecee] mt-1 w-4 text-center"></i> <?php echo t('privacy_right_port'); ?></li>
                <li class="flex items-start gap-2 text-gray-600 dark:text-gray-300"><i class="fas fa-undo text-[#095d7e] dark:text-[#ccecee] mt-1 w-4 text-center"></i> <?php echo t('privacy_right_withdraw'); ?></li>
                <li class="flex items-start gap-2 text-gray-600 dark:text-gray-300"><i class="fas fa-gavel text-[#095d7e] dark:text-[#ccecee] mt-1 w-4 text-center"></i> <?php echo t('privacy_right_complaint'); ?></li>
            </ul>
            <p class="text-gray-600 dark:text-gray-300 mt-4 ml-10 text-sm"><?php echo t('privacy_rights_howto'); ?></p>
        </section>

        <!-- Footer note -->
        <div class="mt-6 p-5 bg-[#f1f9ff] dark:bg-[#095d7e]/10 border border-[#ccecee] dark:border-[#095d7e]/30 rounded-xl flex items-start gap-3">
            <i class="fas fa-info-circle text-primary mt-1"></i>
            <small class="text-gray-600 dark:text-gray-400 text-sm">
                <?php echo t('privacy_footer_note'); ?>
            </small>
        </div>

    </div>
</div>

<?php require 'includes/footer.php'; ?>