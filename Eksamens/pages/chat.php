<?php
session_start();
require_once __DIR__ . '/../includes/lang.php';
$pageTitle = t('chat_title');
require '../includes/db.php';

if (!isset($_SESSION['account_id'], $_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit();
}

$account_id = (int)$_SESSION['account_id'];
$role = $_SESSION['role'];
$appointment_id = (int)($_GET['appointment_id'] ?? 0);

if ($appointment_id <= 0) {
    header("Location: " . ($role === 'psychologist' ? '../specialist/specialist_dashboard.php' : 'appointments.php'));
    exit();
}

// Verify participant access
$stmt = $conn->prepare(
    "SELECT a.id, a.user_account_id, a.psychologist_account_id, a.status, a.consultation_type, a.scheduled_at, a.chat_activated_at, a.user_session_id, a.psychologist_session_id,
            p.full_name AS psychologist_name,
            CONCAT(u.first_name, ' ', u.last_name) AS user_name
     FROM appointments a
     JOIN psychologist_profiles p ON a.psychologist_account_id = p.account_id
     JOIN user_profiles u ON a.user_account_id = u.account_id
     WHERE a.id = ?"
);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$appt = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$appt) {
    header("Location: " . ($role === 'psychologist' ? '../specialist/specialist_dashboard.php' : 'appointments.php'));
    exit();
}

$is_user = (int)$appt['user_account_id'] === $account_id;
$is_psychologist = (int)$appt['psychologist_account_id'] === $account_id;
$endedEarly = isset($_GET['ended']) && $_GET['ended'] === '1';

if (!$is_user && !$is_psychologist) {
    header("Location: " . ($role === 'psychologist' ? '../specialist/specialist_dashboard.php' : 'appointments.php'));
    exit();
}

$chat_partner_name = $is_user ? $appt['psychologist_name'] : $appt['user_name'];
$back_url = $is_user ? 'appointments.php' : '../specialist/specialist_dashboard.php';
$chat_active = $appt['status'] === 'approved' && !empty($appt['chat_activated_at']);
$accessBlocked = false;
$blockedMessage = '';
$currentSessionId = session_id();

if ($is_user) {
    $existingSession = $appt['user_session_id'] ?? '';
    if ($existingSession !== '' && $existingSession !== $currentSessionId) {
        $accessBlocked = true;
        $blockedMessage = 'Šis pieraksts jau tiek izmantots citā pārlūkprogrammā vai ierīcē.';
    } elseif ($existingSession === '') {
        $lockStmt = $conn->prepare("UPDATE appointments SET user_session_id = ? WHERE id = ? AND (user_session_id IS NULL OR user_session_id = '')");
        $lockStmt->bind_param("si", $currentSessionId, $appointment_id);
        $lockStmt->execute();
        $lockStmt->close();
    }
} else {
    $existingSession = $appt['psychologist_session_id'] ?? '';
    if ($existingSession !== '' && $existingSession !== $currentSessionId) {
        $accessBlocked = true;
        $blockedMessage = 'Šo sesiju jau atvēris cits psihologs vai pārlūkprogramma.';
    } elseif ($existingSession === '') {
        $lockStmt = $conn->prepare("UPDATE appointments SET psychologist_session_id = ? WHERE id = ? AND (psychologist_session_id IS NULL OR psychologist_session_id = '')");
        $lockStmt->bind_param("si", $currentSessionId, $appointment_id);
        $lockStmt->execute();
        $lockStmt->close();
    }
}

require '../includes/header.php';
?>

<div class="page-shell page-surface">
    <div class="max-w-3xl mx-auto">
        <!-- Chat header -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <a href="<?php echo $back_url; ?>" class="text-primary hover:text-primaryHover transition">
                    <i class="fas fa-arrow-left text-lg"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($chat_partner_name); ?></h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <?php echo date('d.m.Y H:i', strtotime($appt['scheduled_at'])); ?> &middot;
                        <?php echo $appt['consultation_type'] === 'online' ? t('online') : t('in_person'); ?>
                    </p>
                </div>
            </div>
            <?php if ($appt['consultation_type'] === 'online' && $chat_active): ?>
                <div class="flex gap-2">
                    <a href="<?php echo htmlspecialchars($pathPrefix); ?>pages/video_call.php?appointment_id=<?php echo $appointment_id; ?>" class="button-primary px-4 py-2 text-sm">
                        <i class="fas fa-video mr-2"></i><?php echo t('video_call'); ?>
                    </a>
                    <?php if ($is_psychologist): ?>
                    <button id="endMeetingBtn" type="button" class="px-4 py-2 text-sm rounded-lg bg-red-500 text-white hover:bg-red-600 transition"
                        onclick="endMeetingEarly()">
                        <i class="fas fa-stop-circle mr-2"></i><?php echo t('end_meeting'); ?>
                    </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($endedEarly): ?>
        <div class="mb-4 rounded-2xl bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-700 p-4 text-amber-800 dark:text-amber-100">
            <i class="fas fa-info-circle mr-2"></i><?php echo t('meeting_ended_early'); ?>
        </div>
        <?php endif; ?>

        <!-- Chat messages area -->
        <div id="chatMessages" class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-200 dark:border-zinc-700 shadow-sm h-[60vh] overflow-y-auto p-4 space-y-3 mb-4">
            <div class="flex justify-center py-8">
                <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
            </div>
        </div>

        <!-- Message input -->
        <?php if ($accessBlocked): ?>
        <div class="text-center text-red-600 dark:text-red-400 text-sm py-4">
            <i class="fas fa-lock mr-1"></i> <?php echo htmlspecialchars($blockedMessage); ?>
        </div>
        <?php elseif ($chat_active): ?>
        <form id="chatForm" class="flex gap-2">
            <input type="text" id="chatInput" maxlength="5000" placeholder="<?php echo t('write_message'); ?>" autocomplete="off"
                class="flex-1 input-control" required>
            <button type="submit" class="button-primary px-5 py-2.5 flex-shrink-0">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
        <?php elseif ($appt['status'] === 'approved'): ?>
        <div class="text-center text-amber-600 dark:text-amber-400 text-sm py-3">
            <i class="fas fa-clock mr-1"></i> <?php echo t('session_not_activated'); ?>
        </div>
        <?php else: ?>
        <div class="text-center text-gray-500 dark:text-gray-400 text-sm py-3">
                        <?php echo t('chat_approved_only'); ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!$accessBlocked): ?>
<script>
    window.CHAT_CONFIG = {
        appointmentId: <?php echo $appointment_id; ?>,
        currentUserId: <?php echo $account_id; ?>,
        apiUrl: '../api/chat.php',
        isPsychologist: <?php echo $is_psychologist ? 'true' : 'false'; ?>
    };

    async function endMeetingEarly() {
        const confirmed = await SaprastsConfirm.show('<?php echo t('confirm_end_meeting'); ?>', { okText: '<?php echo t('end_meeting'); ?>', type: 'danger' });
        if (!confirmed) return;

        const formData = new FormData();
        formData.append('action', 'end_meeting');
        formData.append('appointment_id', window.CHAT_CONFIG.appointmentId);

        const res = await fetch(window.CHAT_CONFIG.apiUrl, {
            method: 'POST',
            body: formData
        });
        const result = await res.json();

        if (result.success) {
            window.location.href = 'chat.php?appointment_id=' + window.CHAT_CONFIG.appointmentId + '&ended=1';
        } else {
            SaprastsToast.error(result.error || '<?php echo t('meeting_end_failed'); ?>');
        }
    }
</script>
<script src="../assets/js/chat.js"></script>
<?php endif; ?>

<?php require '../includes/footer.php'; ?>
