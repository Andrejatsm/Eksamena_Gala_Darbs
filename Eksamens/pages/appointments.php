<?php
session_start();
require_once __DIR__ . '/../includes/lang.php';
$pageTitle = t('appointments_title');
require '../includes/db.php';

if (!isset($_SESSION['account_id'], $_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

$account_id = (int)$_SESSION['account_id'];
$message = "";
$status_classes = [
    'pending' => 'bg-[#ccecee] text-[#095d7e] dark:bg-[#095d7e]/20 dark:text-[#ccecee]',
    'approved' => 'bg-[#e2fcd6] text-[#14967f] dark:bg-[#14967f]/20 dark:text-[#e2fcd6]',
    'rejected' => 'bg-[#f1f9ff] text-[#095d7e] border border-[#ccecee] dark:bg-[#095d7e]/10 dark:text-[#ccecee]',
    'cancelled' => 'bg-[#f1f9ff] text-[#095d7e] border border-[#ccecee] dark:bg-[#095d7e]/10 dark:text-[#ccecee]',
    'rescheduled' => 'bg-[#ccecee] text-[#095d7e] dark:bg-[#095d7e]/20 dark:text-[#ccecee]',
];
$status_labels = [
    'pending' => t('status_pending'),
    'approved' => t('status_approved'),
    'rejected' => t('status_cancelled'),
    'cancelled' => t('status_cancelled'),
    'rescheduled' => t('status_rescheduled'),
];

// Apstrādājam pieraksta atcelšanu vai pārcelšanu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'], $_POST['action'])) {
    $appointment_id = (int)$_POST['appointment_id'];
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    $success = false;
    
    if ($_POST['action'] === 'cancel') {
            // Vispirms iegūstam pieraksta info, lai atbrīvotu slotu
            $getStmt = $conn->prepare("SELECT scheduled_at, psychologist_account_id FROM appointments WHERE id = ? AND user_account_id = ?");
            $getStmt->bind_param("ii", $appointment_id, $account_id);
            $getStmt->execute();
            $apptRow = $getStmt->get_result()->fetch_assoc();
            $getStmt->close();

            $status = 'cancelled';
            $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ? AND user_account_id = ?");
            $stmt->bind_param("sii", $status, $appointment_id, $account_id);
            $stmt->execute();
            $stmt->close();
            $success = true;

            // Atbrīvojam slotu, lai citi lietotāji var pierakstīties
            if ($apptRow && !empty($apptRow['scheduled_at'])) {
                $freeStmt = $conn->prepare("UPDATE availability_slots SET is_booked = 0 WHERE psychologist_account_id = ? AND starts_at = ?");
                $freeStmt->bind_param("is", $apptRow['psychologist_account_id'], $apptRow['scheduled_at']);
                $freeStmt->execute();
                $freeStmt->close();
            }
            $message = t('appointment_cancelled');

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => $success, 'message' => $message]);
                exit();
            }
    } elseif ($_POST['action'] === 'reschedule' && !empty($_POST['new_time'])) {
            $raw_new_time = trim($_POST['new_time']);
            $status = 'rescheduled';
            $new_time = null;
            $new_timestamp = false;

            if (($dateObj = DateTime::createFromFormat('Y-m-d\TH:i', $raw_new_time)) !== false) {
                $new_timestamp = $dateObj->getTimestamp();
                $new_time = $dateObj->format('Y-m-d H:i:00');
            } elseif (($timestamp = strtotime($raw_new_time)) !== false) {
                $new_timestamp = $timestamp;
                $new_time = date('Y-m-d H:i:00', $timestamp);
            }

            $getStmt = $conn->prepare("SELECT scheduled_at, psychologist_account_id FROM appointments WHERE id = ? AND user_account_id = ?");
            $getStmt->bind_param("ii", $appointment_id, $account_id);
            $getStmt->execute();
            $apptRow = $getStmt->get_result()->fetch_assoc();
            $getStmt->close();

            if (!$apptRow) {
                $message = t('appointment_not_found');
                $success = false;
            } elseif ($new_time === null || $new_timestamp === false) {
                $message = t('appointment_reschedule_failed');
                $success = false;
            } elseif ($new_timestamp < time()) {
                $message = t('appointment_reschedule_failed');
                $success = false;
            } else {
                $old_time = $apptRow['scheduled_at'];
                $psychologist_id = (int)$apptRow['psychologist_account_id'];

                if ($old_time !== $new_time) {
                    $slotStmt = $conn->prepare("SELECT id FROM availability_slots WHERE psychologist_account_id = ? AND starts_at = ? AND is_booked = 0 LIMIT 1");
                    $slotStmt->bind_param("is", $psychologist_id, $new_time);
                    $slotStmt->execute();
                    $slotResult = $slotStmt->get_result();
                    $slot = $slotResult->fetch_assoc();
                    $slotStmt->close();

                    if (!$slot) {
                        $message = t('appointment_reschedule_failed');
                        $success = false;
                    } else {
                        $stmt = $conn->prepare("UPDATE appointments SET scheduled_at = ?, status = ? WHERE id = ? AND user_account_id = ?");
                        $stmt->bind_param("ssii", $new_time, $status, $appointment_id, $account_id);
                        $stmt->execute();
                        $stmt->close();

                        $freeStmt = $conn->prepare("UPDATE availability_slots SET is_booked = 0 WHERE psychologist_account_id = ? AND starts_at = ?");
                        $freeStmt->bind_param("is", $psychologist_id, $old_time);
                        $freeStmt->execute();
                        $freeStmt->close();

                        $bookStmt = $conn->prepare("UPDATE availability_slots SET is_booked = 1 WHERE id = ?");
                        $bookStmt->bind_param("i", $slot['id']);
                        $bookStmt->execute();
                        $bookStmt->close();

                        $message = t('appointment_rescheduled', date('d.m.Y H:i', strtotime($new_time)));
                        $success = true;
                    }
                } else {
                    $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ? AND user_account_id = ?");
                    $stmt->bind_param("sii", $status, $appointment_id, $account_id);
                    $stmt->execute();
                    $stmt->close();
                    $message = t('appointment_rescheduled', date('d.m.Y H:i', strtotime($new_time)));
                    $success = true;
                }
            }

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => $success, 'message' => $message]);
                exit();
            }
    }
}

// Iegūstam pierakstus
$per_page = 8;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;

$count_stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE user_account_id = ? AND scheduled_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)");
$count_stmt->bind_param("i", $account_id);
$count_stmt->execute();
$total_appointments = (int)$count_stmt->get_result()->fetch_row()[0];
$count_stmt->close();
$total_pages = (int)ceil($total_appointments / $per_page);
$page = min($page, max(1, $total_pages));

$sql = "SELECT a.id, a.scheduled_at, a.consultation_type, a.status, a.chat_activated_at, p.full_name FROM appointments a 
        JOIN psychologist_profiles p ON a.psychologist_account_id = p.account_id 
        WHERE a.user_account_id = ? AND a.scheduled_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
        ORDER BY a.scheduled_at DESC
        LIMIT {$per_page} OFFSET {$offset}";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();
$appointments = [];
while($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}
$stmt->close();

require '../includes/header.php';
?>

<div class="page-shell page-surface">
    <div class="page-heading">
        <h1 class="page-title"><?php echo t('appointments_title'); ?></h1>
        <p class="page-subtitle"><?php echo t('appointments_subtitle'); ?></p>
    </div>

    <?php if(!empty($message)): ?>
        <div class="alert-success">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="space-y-4">
        <?php foreach($appointments as $appt): ?>
            <div class="panel-card">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($appt['full_name']); ?></h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            <i class="fas fa-calendar"></i> <?php echo date('d.m.Y H:i', strtotime($appt['scheduled_at'])); ?>
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <i class="fas fa-video"></i> <?php echo $appt['consultation_type'] === 'online' ? t('online') : t('in_person'); ?>
                        </p>
                        <span class="inline-block mt-2 px-2 py-1 text-xs rounded-full <?php echo $status_classes[$appt['status']] ?? 'bg-gray-100 text-gray-800'; ?>">
                            <?php echo $status_labels[$appt['status']] ?? ucfirst((string)$appt['status']); ?>
                        </span>

                        <?php if($appt['status'] === 'approved'): ?>
                            <?php if(!empty($appt['chat_activated_at'])): ?>
                            <div class="flex gap-2 mt-3">
                                <a href="<?php echo htmlspecialchars($pathPrefix); ?>pages/chat.php?appointment_id=<?php echo (int)$appt['id']; ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary/10 text-primary rounded-lg hover:bg-primary/20 transition text-sm font-medium">
                                    <i class="fas fa-comments"></i> <?php echo t('chat'); ?>
                                </a>
                                <?php if($appt['consultation_type'] === 'online'): ?>
                                <a href="<?php echo htmlspecialchars($pathPrefix); ?>pages/video_call.php?appointment_id=<?php echo (int)$appt['id']; ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-500/10 text-green-600 dark:text-green-400 rounded-lg hover:bg-green-500/20 transition text-sm font-medium">
                                    <i class="fas fa-video"></i> <?php echo t('video_call'); ?>
                                </a>
                                <?php endif; ?>
                            </div>
                            <?php else: ?>
                            <p class="text-xs text-amber-600 dark:text-amber-400 mt-3">
                                <i class="fas fa-clock mr-1"></i> <?php echo t('waiting_activation'); ?>
                            </p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <?php if($appt['status'] === 'pending' || $appt['status'] === 'approved'): ?>
                        <div class="flex gap-2">
                            <button type="button" class="open-reschedule-btn text-[#095d7e] hover:text-[#14967f] dark:text-[#ccecee] hover:bg-[#ccecee]/30 dark:hover:bg-[#095d7e]/20 px-3 py-2 rounded-lg transition text-sm" data-appointment-id="<?php echo (int)$appt['id']; ?>">
                                <i class="fas fa-calendar-alt"></i> <?php echo t('reschedule'); ?>
                            </button>
                            <form method="POST" action="" class="inline">
                                <input type="hidden" name="appointment_id" value="<?php echo $appt['id']; ?>">
                                <button type="submit" name="action" value="cancel" class="cancel-appt-btn text-[#095d7e] hover:text-[#14967f] dark:text-[#ccecee] hover:bg-[#ccecee]/30 dark:hover:bg-[#095d7e]/20 px-3 py-2 rounded-lg transition text-sm">
                                    <i class="fas fa-trash"></i> <?php echo t('cancel'); ?>
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if(empty($appointments)): ?>
        <div class="empty-card">
            <p class="text-gray-500 dark:text-gray-400 mb-4"><?php echo t('no_appointments'); ?></p>
            <a href="<?php echo htmlspecialchars($pathPrefix); ?>pages/dashboard.php" class="button-primary">
                <?php echo t('find_specialist'); ?>
            </a>
        </div>
    <?php endif; ?>

    <?php if ($total_pages > 1): ?>
    <div class="flex justify-center items-center gap-2 mt-6">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>" class="pagination-btn"><i class="fas fa-chevron-left mr-1"></i><?php echo t('previous'); ?></a>
        <?php else: ?>
            <span class="pagination-btn-disabled"><i class="fas fa-chevron-left mr-1"></i><?php echo t('previous'); ?></span>
        <?php endif; ?>
        <span class="text-sm text-gray-600 dark:text-gray-400 px-2"><?php echo t('page_of', $page, $total_pages); ?></span>
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>" class="pagination-btn"><?php echo t('next'); ?><i class="fas fa-chevron-right ml-1"></i></a>
        <?php else: ?>
            <span class="pagination-btn-disabled"><?php echo t('next'); ?><i class="fas fa-chevron-right ml-1"></i></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Pieraksta pārcelšanas modālis -->
<div id="rescheduleModal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
        <div class="relative bg-surface dark:bg-zinc-800 rounded-2xl border border-[#ccecee] dark:border-zinc-700 shadow-2xl w-full max-w-md">
            <div class="px-6 pt-6 pb-4">
                <div class="flex justify-between items-center mb-5">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white"><?php echo t('reschedule_title'); ?></h3>
                    <button id="closeRescheduleModalBtn" type="button" class="text-gray-400 hover:text-[#095d7e] dark:hover:text-[#ccecee] transition p-1">
                        <i class="fas fa-times fa-lg"></i>
                    </button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="appointment_id" id="modal_appointment_id">
                    <input type="hidden" name="action" value="reschedule">
                    <div class="mb-4">
                        <label for="new_time" class="field-label"><?php echo t('new_time'); ?></label>
                        <input type="datetime-local" name="new_time" id="new_time" required class="input-control mt-1">
                    </div>
                    <div class="border-t border-[#ccecee] dark:border-zinc-700 -mx-6 px-6 pt-4 mt-2 flex justify-end gap-2 bg-[#f1f9ff] dark:bg-zinc-700/30 rounded-b-2xl">
                        <button type="button" id="closeRescheduleModalBtnFooter" class="px-4 py-2 bg-surface dark:bg-zinc-700 border border-[#ccecee] dark:border-zinc-600 text-[#095d7e] dark:text-[#ccecee] rounded-lg hover:bg-[#ccecee] dark:hover:bg-zinc-600 transition font-semibold"><?php echo t('cancel'); ?></button>
                        <button type="submit" class="button-primary px-6 py-2"><?php echo t('confirm_new_time'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/appointments.js"></script>

<?php require '../includes/footer.php'; ?>
