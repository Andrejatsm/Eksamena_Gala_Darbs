<?php
if (!isset($pathPrefix)) {
    $scriptDir = trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $depth = $scriptDir === '' ? 0 : substr_count($scriptDir, '/') + 1;
    $pathPrefix = str_repeat('../', $depth);
}
?>

<footer class="mt-auto bg-surface dark:bg-zinc-900 border-t border-[#ccecee] dark:border-zinc-800 py-8 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                
                <div class="text-center md:text-left">
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                        &copy; <?php echo date("Y"); ?> Saprasts. <?php echo t('all_rights'); ?>
                    </p>
                </div>

                <div class="flex items-center space-x-6 text-sm font-medium">
                    <a href="<?php echo htmlspecialchars($pathPrefix); ?>privacy.php" class="text-gray-500 dark:text-gray-400 hover:text-primary transition-colors"><?php echo t('privacy_policy'); ?></a>
                    <button id="openContactModalBtn" type="button" class="text-gray-500 dark:text-gray-400 hover:text-primary transition-colors focus:outline-none"><?php echo t('contact_us'); ?></button>
                </div>
                
            </div>
        </div>
    </footer>

    <button id="chat-toggle-btn" class="fixed bottom-6 right-6 z-50 bg-primary hover:bg-primaryHover text-white p-4 rounded-full shadow-lg transition-all transform hover:scale-110 flex items-center justify-center w-14 h-14">
        <i class="fas fa-comment-dots text-2xl"></i>
    </button>

    <div id="chat-container" class="hidden fixed bottom-24 right-6 z-50 w-80 md:w-96 h-[500px] bg-white dark:bg-zinc-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-zinc-700 flex flex-col overflow-hidden transition-all duration-300">
        <div class="bg-gradient-to-r from-primary to-primaryHover p-4 flex justify-between items-center text-white shadow-sm">
            <div class="font-bold flex items-center gap-2">
                <i class="fas fa-robot"></i> <?php echo t('ai_assistant'); ?>
            </div>
            <button id="chat-close-btn" class="hover:text-gray-200 focus:outline-none transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="chat-messages" class="flex-1 p-4 overflow-y-auto space-y-3 bg-gray-50 dark:bg-zinc-900/50 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-zinc-600">
            <div class="flex justify-start">
                <div class="bg-white dark:bg-zinc-700 text-gray-800 dark:text-gray-200 rounded-2xl rounded-tl-sm py-3 px-4 max-w-[85%] text-sm shadow-sm border border-gray-100 dark:border-zinc-600">
                    <?php echo t('ai_greeting'); ?>
                </div>
            </div>
        </div>
        <div class="p-3 border-t border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 flex gap-2">
            <input type="text" id="chat-input" placeholder="<?php echo t('write_message'); ?>" class="flex-1 bg-gray-100 dark:bg-zinc-700 text-gray-900 dark:text-white rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary transition placeholder-gray-400">
            <button id="chat-send-btn" class="bg-primary hover:bg-primaryHover text-white rounded-full w-10 h-10 flex items-center justify-center transition shadow-sm transform hover:scale-110">
                <i class="fas fa-paper-plane text-sm"></i>
            </button>
        </div>
    </div>

    <div id="contactModal" class="hidden fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div id="contactModalBackdrop" class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
            <div class="relative bg-surface dark:bg-zinc-800 rounded-2xl border border-[#ccecee] dark:border-zinc-700 shadow-2xl w-full sm:max-w-lg">
                <div class="px-6 pt-6 pb-4">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-5" id="modal-title"><?php echo t('contact_modal_title'); ?></h3>
                    <div class="space-y-4">
                        <div>
                            <label class="field-label"><?php echo t('your_email'); ?></label>
                            <input type="email" id="contactEmail" class="input-control">
                        </div>
                        <div>
                            <label class="field-label"><?php echo t('message'); ?></label>
                            <textarea id="contactMessage" rows="3" class="textarea-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="bg-[#f1f9ff] dark:bg-zinc-700/30 border-t border-[#ccecee] dark:border-zinc-700 px-6 py-4 flex flex-row-reverse gap-2 rounded-b-2xl">
                    <button type="button" id="sendContactBtn" class="button-primary px-6 py-2"><?php echo t('send'); ?></button>
                    <button id="closeContactModalBtn" type="button" class="px-4 py-2 bg-surface dark:bg-zinc-700 border border-[#ccecee] dark:border-zinc-600 text-[#095d7e] dark:text-[#ccecee] rounded-lg hover:bg-[#ccecee] dark:hover:bg-zinc-600 transition font-semibold"><?php echo t('cancel'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <script>
    window.LANG = <?php echo json_encode([
        'fill_fields'        => t('js_fill_fields'),
        'send_error'         => t('js_send_error'),
        'thinking'           => t('js_thinking'),
        'error_prefix'       => t('js_error_prefix'),
        'server_error'       => t('js_server_error'),
        'starts_now'         => t('js_starts_now'),
        'in_1_min'           => t('js_in_1_min'),
        'in_x_min'           => t('js_in_x_min'),
        'unread_messages'    => t('js_unread_messages'),
        'open_chat'          => t('js_open_chat'),
        'no_new_notifications'=> t('no_new_notifications'),
        'notifications_count'=> t('js_notifications_count'),
        'load_error'         => t('js_load_error'),
        'previous'           => t('previous'),
        'next'               => t('next'),
        'page_of'            => t('page_of'),
        'session_not_active' => t('js_session_not_active'),
        'no_messages'        => t('js_no_messages'),
    ], JSON_UNESCAPED_UNICODE); ?>;
    </script>
    <script src="<?php echo htmlspecialchars($pathPrefix); ?>assets/js/script.js"></script>
    <script src="<?php echo htmlspecialchars($pathPrefix); ?>assets/js/footer_ui.js"></script>
    <script src="<?php echo htmlspecialchars($pathPrefix); ?>assets/js/notifications.js"></script>
</body>
</html>