<?php
session_start();
require_once __DIR__ . '/../includes/lang.php';
require '../includes/db.php';

// Pārbaude vai ir psihologs
if (!isset($_SESSION['account_id'], $_SESSION['role']) || $_SESSION['role'] !== 'psychologist') {
    header("Location: ../auth/login.php");
    exit();
}

$psihologs_id = (int)$_SESSION['account_id'];

// Statusa maiņas loģika
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'], $_POST['appoint_id'])) {
    $appoint_id = (int)$_POST['appoint_id'];
    $redirect_after = true;

    if ($_POST['action'] === 'activate') {
        // Psihologs aktivizē čatu/video pirms sesijas
        // First verify the appointment is owned by this psychologist and is approved
        $checkStmt = $conn->prepare("SELECT id FROM appointments WHERE id = ? AND psychologist_account_id = ? AND status = 'approved' AND chat_activated_at IS NULL");
        $checkStmt->bind_param("ii", $appoint_id, $psihologs_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $checkStmt->close();
        
        if ($checkResult->num_rows === 0) {
            $_SESSION['flash_message'] = 'Pieraksts nav pieejams aktivizēšanai.';
            $_SESSION['flash_type'] = 'error';
        } else {
            // Now update it
            $updateStmt = $conn->prepare("UPDATE appointments SET chat_activated_at = NOW() WHERE id = ?");
            $updateStmt->bind_param("i", $appoint_id);
            $updateStmt->execute();
            $affected = $updateStmt->affected_rows;
            $updateStmt->close();
            
            if ($affected > 0) {
                $_SESSION['flash_message'] = 'Sesija aktivizēta sekmīgi!';
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = 'Neizdevās aktivizēt sesiju.';
                $_SESSION['flash_type'] = 'error';
            }
        }
    } else {
        $status = ($_POST['action'] === 'accept') ? 'approved' : 'rejected';

        // Ja noraidām, vispirms iegūstam pieraksta laiku, lai atbrīvotu slotu
        $apptRow = null;
        if ($status === 'rejected') {
            $getStmt = $conn->prepare("SELECT scheduled_at FROM appointments WHERE id = ? AND psychologist_account_id = ?");
            $getStmt->bind_param("ii", $appoint_id, $psihologs_id);
            $getStmt->execute();
            $apptRow = $getStmt->get_result()->fetch_assoc();
            $getStmt->close();
        }

        $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ? AND psychologist_account_id = ?");
        $stmt->bind_param("sii", $status, $appoint_id, $psihologs_id);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        // Atbrīvojam slotu, lai citi var pierakstīties
        if ($status === 'rejected' && $apptRow && !empty($apptRow['scheduled_at'])) {
            $freeStmt = $conn->prepare("UPDATE availability_slots SET is_booked = 0 WHERE psychologist_account_id = ? AND starts_at = ?");
            $freeStmt->bind_param("is", $psihologs_id, $apptRow['scheduled_at']);
            $freeStmt->execute();
            $freeStmt->close();
        }
        
        $statusMsg = $status === 'approved' ? 'Pieraksts apstiprināts.' : 'Pieraksts noraidīts.';
        $_SESSION['flash_message'] = $statusMsg;
        $_SESSION['flash_type'] = 'success';
    }
    
    if ($redirect_after) {
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
}

// Iegūstam pieteikumus
$sql = "SELECT * FROM appointments WHERE psychologist_account_id = ? AND scheduled_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $psihologs_id);
$stmt->execute();
$result = $stmt->get_result();

$flash_message = $_SESSION['flash_message'] ?? '';
$flash_type = $_SESSION['flash_type'] ?? 'success';
if (!empty($flash_message)) {
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
}

$pageTitle = t('specialist_panel');
require '../includes/header.php';
?>

<div class="min-h-screen page-surface dark:bg-zinc-900">
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <?php if (!empty($flash_message)): ?>
        <div class="mb-6 p-4 rounded-lg border <?php echo $flash_type === 'error' ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-400' : 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 text-green-700 dark:text-green-400'; ?>">
            <i class="fas <?php echo $flash_type === 'error' ? 'fa-triangle-exclamation' : 'fa-check-circle'; ?> mr-2"></i><?php echo htmlspecialchars($flash_message); ?>
        </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="mb-12">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white"><?php echo t('welcome', htmlspecialchars($_SESSION['display_name'] ?? '')); ?></h1>
                    <p class="text-xl text-gray-600 dark:text-gray-400 mt-2"><?php echo t('manage_practice'); ?></p>
                </div>
                <div class="flex gap-3">
                    <a href="<?php echo htmlspecialchars($pathPrefix); ?>pages/articles.php" class="px-6 py-3 bg-primary text-white font-bold rounded-lg hover:bg-primaryHover transition shadow-lg">
                        <i class="fas fa-plus mr-2"></i><?php echo t('write_article'); ?>
                    </a>
                    <a href="<?php echo htmlspecialchars($pathPrefix); ?>pages/availability.php" class="px-6 py-3 bg-gray-200 dark:bg-zinc-700 text-gray-900 dark:text-white font-bold rounded-lg hover:bg-gray-300 dark:hover:bg-zinc-600 transition">
                        <i class="fas fa-calendar-plus mr-2"></i><?php echo t('add_time_btn'); ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
            <?php
            // Iegūstam statistiku
            $stats = [
                'total_appointments' => 0,
                'pending_appointments' => 0,
                'approved_appointments' => 0,
                'total_articles' => 0
            ];

            $countForPsychologist = function (string $query) use ($conn, $psihologs_id): int {
                $stmt = $conn->prepare($query);
                if (!$stmt) {
                    return 0;
                }
                $stmt->bind_param("i", $psihologs_id);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                return isset($row['count']) ? (int)$row['count'] : 0;
            };

            $stats['total_appointments'] = $countForPsychologist("SELECT COUNT(*) AS count FROM appointments WHERE psychologist_account_id = ?");
            $stats['pending_appointments'] = $countForPsychologist("SELECT COUNT(*) AS count FROM appointments WHERE psychologist_account_id = ? AND status = 'pending'");
            $stats['approved_appointments'] = $countForPsychologist("SELECT COUNT(*) AS count FROM appointments WHERE psychologist_account_id = ? AND status = 'approved'");
            $stats['total_articles'] = $countForPsychologist("SELECT COUNT(*) AS count FROM articles WHERE psychologist_account_id = ?");
            ?>

            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase"><?php echo t('total_appointments'); ?></p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $stats['total_appointments']; ?></p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check text-primary text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase"><?php echo t('pending_count'); ?></p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $stats['pending_appointments']; ?></p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-clock text-primary text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase"><?php echo t('approved_count'); ?></p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $stats['approved_appointments']; ?></p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-check-circle text-primary text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase"><?php echo t('articles_count'); ?></p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $stats['total_articles']; ?></p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-file-alt text-primary text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="table-card">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-zinc-700">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white"><?php echo t('appointment_management'); ?></h3>
                    <p class="text-gray-600 dark:text-gray-400 mt-1"><?php echo t('review_appointments'); ?></p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                        <thead class="bg-gray-50 dark:bg-zinc-700/50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider"><?php echo t('client'); ?></th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider"><?php echo t('date'); ?></th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider"><?php echo t('type'); ?></th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider"><?php echo t('status'); ?></th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider"><?php echo t('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-700/30 transition">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center font-bold text-primary text-sm mr-3">
                                                    <?php echo strtoupper(substr($row['user_name_snapshot'] ?? '', 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($row['user_name_snapshot'] ?? ''); ?></p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($row['user_email_snapshot'] ?? ''); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            <?php echo date("d.m.Y H:i", strtotime($row['scheduled_at'] ?? $row['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                            <?php echo $row['consultation_type'] === 'online' ? t('online') : t('in_person'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $statusColor = match($row['status']) {
                                                'approved' => 'bg-[#e2fcd6] text-[#14967f] dark:bg-[#14967f]/20 dark:text-[#e2fcd6]',
                                                'rejected' => 'bg-[#f1f9ff] text-[#095d7e] border border-[#ccecee] dark:bg-[#095d7e]/10 dark:text-[#ccecee]',
                                                'cancelled' => 'bg-[#f1f9ff] text-[#095d7e] border border-[#ccecee] dark:bg-[#095d7e]/10 dark:text-[#ccecee]',
                                                'rescheduled' => 'bg-[#ccecee] text-[#095d7e] dark:bg-[#095d7e]/20 dark:text-[#ccecee]',
                                                default => 'bg-[#ccecee] text-[#095d7e] dark:bg-[#095d7e]/20 dark:text-[#ccecee]',
                                            };
                                            $statusLabel = match($row['status']) {
                                                'pending' => t('status_pending'),
                                                'approved' => t('status_approved'),
                                                'rejected' => t('status_cancelled'),
                                                'cancelled' => t('status_cancelled'),
                                                'rescheduled' => t('status_rescheduled'),
                                                default => ucfirst((string)$row['status']),
                                            };
                                            ?>
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusColor; ?>">
                                                <?php echo $statusLabel; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <?php
                                                $appointmentTime = strtotime($row['scheduled_at'] ?? '');
                                                $isFutureSession = $appointmentTime !== false && $appointmentTime >= strtotime('-2 hours');
                                                $canActivate = ($row['status'] === 'approved' && empty($row['chat_activated_at']) && $isFutureSession);
                                            ?>
                                            <?php if(in_array($row['status'], ['pending', 'rescheduled'], true)): ?>
                                                <div class="flex justify-end gap-2">
                                                    <form method="POST" class="inline">
                                                        <input type="hidden" name="appoint_id" value="<?php echo $row['id']; ?>">
                                                        <button type="submit" name="action" value="accept" class="px-3 py-1 bg-primary/15 dark:bg-primary/25 text-primary rounded-lg hover:bg-primary/25 dark:hover:bg-primary/35 transition text-sm font-medium" title="Apstiprināt">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" class="inline">
                                                        <input type="hidden" name="appoint_id" value="<?php echo $row['id']; ?>">
                                                        <button type="submit" name="action" value="reject" class="px-3 py-1 bg-[#ccecee] text-[#095d7e] dark:bg-[#095d7e]/20 dark:text-[#ccecee] rounded-lg hover:bg-[#b8dde0] dark:hover:bg-[#095d7e]/30 transition text-sm font-medium" title="Noraidīt">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php else: ?>
                                                <?php if ($row['status'] === 'approved'): ?>
                                                    <?php if (!empty($row['chat_activated_at'])): ?>
                                                    <div class="flex justify-end gap-2">
                                                        <a href="<?php echo htmlspecialchars($pathPrefix); ?>pages/chat.php?appointment_id=<?php echo (int)$row['id']; ?>" class="px-3 py-1 bg-primary/15 dark:bg-primary/25 text-primary rounded-lg hover:bg-primary/25 dark:hover:bg-primary/35 transition text-sm font-medium" title="Čats">
                                                            <i class="fas fa-comments"></i>
                                                        </a>
                                                        <?php if ($row['consultation_type'] === 'online'): ?>
                                                        <a href="<?php echo htmlspecialchars($pathPrefix); ?>pages/video_call.php?appointment_id=<?php echo (int)$row['id']; ?>" class="px-3 py-1 bg-green-500/10 text-green-600 dark:text-green-400 rounded-lg hover:bg-green-500/20 transition text-sm font-medium" title="Videozvans">
                                                            <i class="fas fa-video"></i>
                                                        </a>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php elseif ($canActivate): ?>
                                                    <form method="POST" class="inline">
                                                        <input type="hidden" name="appoint_id" value="<?php echo (int)$row['id']; ?>">
                                                        <button type="submit" name="action" value="activate" class="px-3 py-1.5 bg-amber-500/15 dark:bg-amber-500/25 text-amber-600 dark:text-amber-400 rounded-lg hover:bg-amber-500/25 dark:hover:bg-amber-500/35 transition text-sm font-medium" title="Aktivizēt čatu un video">
                                                            <i class="fas fa-bolt mr-1"></i> <?php echo t('activate'); ?>
                                                        </button>
                                                    </form>
                                                    <?php else: ?>
                                                    <span class="text-gray-500 text-sm">Sesiju nevar aktivizēt — laiks ir pagājis vai tā jau ir beigusies.</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                <span class="text-gray-400">-</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-calendar-times text-4xl text-gray-400 dark:text-gray-600 mb-4"></i>
                                            <p class="text-gray-500 dark:text-gray-400 text-lg"><?php echo t('no_appointments_yet'); ?></p>
                                            <p class="text-gray-400 dark:text-gray-500 text-sm mt-1"><?php echo t('new_appointments_here'); ?></p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require '../includes/footer.php'; ?>