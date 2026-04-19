<?php
session_start();
require_once __DIR__ . '/../includes/lang.php';
$pageTitle = t('video_call_title');
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

// Verify access
$stmt = $conn->prepare(
    "SELECT a.id, a.user_account_id, a.psychologist_account_id, a.status, a.consultation_type, a.scheduled_at, a.chat_activated_at, a.user_session_id, a.psychologist_session_id,
            p.full_name AS psychologist_name,
            CONCAT(u.first_name, ' ', u.last_name) AS user_name
     FROM appointments a
     JOIN psychologist_profiles p ON a.psychologist_account_id = p.account_id
     JOIN user_profiles u ON a.user_account_id = u.account_id
     WHERE a.id = ? AND a.status = 'approved' AND a.consultation_type = 'online' AND a.chat_activated_at IS NOT NULL"
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
$back_url = "chat.php?appointment_id=" . $appointment_id;

$accessBlocked = false;
$blockedMessage = '';
$currentSessionId = session_id();

if (!$is_user && !$is_psychologist) {
    header("Location: " . ($role === 'psychologist' ? '../specialist/specialist_dashboard.php' : 'appointments.php'));
    exit();
}

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

if ($accessBlocked) {
    require '../includes/header.php';
    ?>
    <div class="page-shell page-surface">
        <div class="max-w-3xl mx-auto py-12">
            <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-red-200 dark:border-red-800 p-8 text-center">
                <i class="fas fa-lock text-4xl text-red-600 dark:text-red-400 mb-4"></i>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Piekļuve bloķēta</h1>
                <p class="text-gray-600 dark:text-gray-400 mb-6"><?php echo htmlspecialchars($blockedMessage); ?></p>
                <a href="<?php echo $back_url; ?>" class="button-primary px-6 py-3"><?php echo t('cancel'); ?></a>
            </div>
        </div>
    </div>
    <?php
    require '../includes/footer.php';
    exit();
}

$display_name = htmlspecialchars($_SESSION['display_name'] ?? 'Lietotājs');
$chat_partner_name = $is_user ? $appt['psychologist_name'] : $appt['user_name'];
$back_url = "chat.php?appointment_id=" . $appointment_id;

require '../includes/header.php';
?>

<div class="page-shell page-surface">
    <div class="max-w-5xl mx-auto">
        <!-- Call header -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <a href="<?php echo $back_url; ?>" class="text-primary hover:text-primaryHover transition">
                    <i class="fas fa-arrow-left text-lg"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                        <?php echo t('video_call_with', htmlspecialchars($chat_partner_name)); ?>
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <?php echo date('d.m.Y H:i', strtotime($appt['scheduled_at'])); ?>
                    </p>
                </div>
            </div>
            <?php if ($is_psychologist): ?>
                <button id="endMeetingBtn" type="button" class="button-primary px-4 py-2 text-sm bg-red-500 hover:bg-red-600">
                    <i class="fas fa-stop-circle mr-2"></i><?php echo t('end_meeting'); ?>
                </button>
            <?php else: ?>
                <a href="<?php echo $back_url; ?>" class="button-primary px-4 py-2 text-sm bg-red-500 hover:bg-red-600" id="endCallBtn">
                    <i class="fas fa-phone-slash mr-2"></i><?php echo t('end_call'); ?>
                </a>
            <?php endif; ?>
        </div>

        <!-- Jitsi container -->
        <div id="jitsiContainer" class="bg-black rounded-2xl overflow-hidden shadow-xl" style="height: 70vh;">
            <div class="flex items-center justify-center h-full text-white">
                <div class="text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-white mb-4"></div>
                    <p><?php echo t('connecting_video'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://meet.jit.si/external_api.js"></script>
<script>
    // Jitsi domain — meet.jit.si prasa autorizāciju. Kad pārcelsi uz serveri,
    // uzstādi savu Jitsi instanci (Docker) un nomaini domēnu šeit.
    window.VIDEO_CONFIG = {
        appointmentId: <?php echo $appointment_id; ?>,
        displayName: <?php echo json_encode($_SESSION['display_name'] ?? 'Lietotājs'); ?>,
        apiUrl: '../api/video_room.php',
        endApiUrl: '../api/chat.php',
        isPsychologist: <?php echo $is_psychologist ? 'true' : 'false'; ?>,
        jitsiDomain: 'meet.jit.si',
        endMeetingLabels: {
            confirmMessage: '<?php echo t('confirm_end_meeting'); ?>',
            okText: '<?php echo t('end_meeting'); ?>',
            errorMessage: '<?php echo t('meeting_end_failed'); ?>'
        }
    };
</script>
<script src="../assets/js/video_call.js"></script>

<?php require '../includes/footer.php'; ?>
