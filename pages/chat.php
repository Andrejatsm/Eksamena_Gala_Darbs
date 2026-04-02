<?php
session_start();
$pageTitle = "Čats";
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
    "SELECT a.id, a.user_account_id, a.psychologist_account_id, a.status, a.consultation_type, a.scheduled_at,
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

if (!$is_user && !$is_psychologist) {
    header("Location: " . ($role === 'psychologist' ? '../specialist/specialist_dashboard.php' : 'appointments.php'));
    exit();
}

$chat_partner_name = $is_user ? $appt['psychologist_name'] : $appt['user_name'];
$back_url = $is_user ? 'appointments.php' : '../specialist/specialist_dashboard.php';

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
                        <?php echo $appt['consultation_type'] === 'online' ? 'Tiešsaistē' : 'Klātienē'; ?>
                    </p>
                </div>
            </div>
            <?php if ($appt['consultation_type'] === 'online' && $appt['status'] === 'approved'): ?>
                <a href="video_call.php?appointment_id=<?php echo $appointment_id; ?>" class="button-primary px-4 py-2 text-sm">
                    <i class="fas fa-video mr-2"></i>Videozvans
                </a>
            <?php endif; ?>
        </div>

        <!-- Chat messages area -->
        <div id="chatMessages" class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-200 dark:border-zinc-700 shadow-sm h-[60vh] overflow-y-auto p-4 space-y-3 mb-4">
            <div class="flex justify-center py-8">
                <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
            </div>
        </div>

        <!-- Message input -->
        <?php if ($appt['status'] === 'approved'): ?>
        <form id="chatForm" class="flex gap-2">
            <input type="text" id="chatInput" maxlength="5000" placeholder="Rakstiet ziņojumu..." autocomplete="off"
                class="flex-1 input-control" required>
            <button type="submit" class="button-primary px-5 py-2.5 flex-shrink-0">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
        <?php else: ?>
        <div class="text-center text-gray-500 dark:text-gray-400 text-sm py-3">
            Čats ir pieejams tikai apstiprinātiem pierakstiem.
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
    window.CHAT_CONFIG = {
        appointmentId: <?php echo $appointment_id; ?>,
        currentUserId: <?php echo $account_id; ?>,
        apiUrl: '../api/chat.php'
    };
</script>
<script src="../assets/js/chat.js"></script>

<?php require '../includes/footer.php'; ?>
