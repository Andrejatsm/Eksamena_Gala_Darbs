<?php
session_start();
$pageTitle = "Mani pieraksti";
require 'database/db.php';

if (!isset($_SESSION['account_id'], $_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

require 'header.php';

$account_id = (int)$_SESSION['account_id'];
$message = "";
$status_classes = [
    'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    'approved' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    'rescheduled' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
];
$status_labels = [
    'pending' => 'Gaida apstiprinājumu',
    'approved' => 'Apstiprināts',
    'cancelled' => 'Atcelts',
    'rescheduled' => 'Pārcelts',
];

// Apstrādājam pieraksta atcelšanu vai pārcelšanu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'], $_POST['action'])) {
    $appointment_id = (int)$_POST['appointment_id'];
    
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

            // Atbrīvojam slotu, lai citi lietotāji var pierakstīties
            if ($apptRow && !empty($apptRow['scheduled_at'])) {
                $freeStmt = $conn->prepare("UPDATE availability_slots SET is_booked = 0 WHERE psychologist_account_id = ? AND starts_at = ?");
                $freeStmt->bind_param("is", $apptRow['psychologist_account_id'], $apptRow['scheduled_at']);
                $freeStmt->execute();
                $freeStmt->close();
            }
            $message = "Pieraksts atcelts.";
    } elseif ($_POST['action'] === 'reschedule' && !empty($_POST['new_time'])) {
            $new_time = $_POST['new_time'];
            $status = 'rescheduled';
            $stmt = $conn->prepare("UPDATE appointments SET scheduled_at = ?, status = ? WHERE id = ? AND user_account_id = ?");
            $stmt->bind_param("ssii", $new_time, $status, $appointment_id, $account_id);
            $stmt->execute();
            $stmt->close();
            $message = "Pieraksts pārcelts uz " . date('d.m.Y H:i', strtotime($new_time)) . ".";
    }
}

// Iegūstam pierakstus
$sql = "SELECT a.id, a.scheduled_at, a.consultation_type, a.status, p.full_name FROM appointments a 
        JOIN psychologist_profiles p ON a.psychologist_account_id = p.account_id 
        WHERE a.user_account_id = ? 
        ORDER BY a.scheduled_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();
$appointments = [];
while($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}
$stmt->close();
?>

<div class="page-shell page-surface">
    <div class="page-heading">
        <h1 class="page-title">Mani pieraksti</h1>
        <p class="page-subtitle">Skatiet un pārvaldiet savus pierakstus.</p>
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
                            <i class="fas fa-video"></i> <?php echo $appt['consultation_type'] === 'online' ? 'Tiešsaistē' : 'Klātienē'; ?>
                        </p>
                        <span class="inline-block mt-2 px-2 py-1 text-xs rounded-full <?php echo $status_classes[$appt['status']] ?? 'bg-gray-100 text-gray-800'; ?>">
                            <?php echo $status_labels[$appt['status']] ?? ucfirst((string)$appt['status']); ?>
                        </span>
                    </div>
                    
                    <?php if($appt['status'] === 'pending' || $appt['status'] === 'approved'): ?>
                        <div class="flex gap-2">
                            <button type="button" class="open-reschedule-btn text-blue-600 hover:text-blue-700 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 px-3 py-2 rounded-lg transition text-sm" data-appointment-id="<?php echo (int)$appt['id']; ?>">
                                <i class="fas fa-calendar-alt"></i> Pārcelt
                            </button>
                            <form method="POST" class="inline">
                                <input type="hidden" name="appointment_id" value="<?php echo $appt['id']; ?>">
                                <button type="submit" name="action" value="cancel" onclick="return confirm('Vai tiešām vēlaties atcelt pierakstu?')" class="text-red-600 hover:text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 px-3 py-2 rounded-lg transition text-sm">
                                    <i class="fas fa-trash"></i> Atcelt
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
            <p class="text-gray-500 dark:text-gray-400 mb-4">Jums nav nekādu pierakstu.</p>
            <a href="dashboard.php" class="button-primary">
                Atrast speciālistu
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Pieraksta pārcelšanas modālis -->
<div id="rescheduleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-6 border w-full max-w-md shadow-lg rounded-2xl bg-white dark:bg-zinc-800">
        <div>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg leading-6 font-bold text-gray-900 dark:text-white">Pārcelt pierakstu</h3>
                <button id="closeRescheduleModalBtn" type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                    <i class="fas fa-times fa-lg"></i>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="appointment_id" id="modal_appointment_id">
                <input type="hidden" name="action" value="reschedule">
                <div class="mb-4">
                    <label for="new_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 text-left mb-1">Jaunais laiks</label>
                    <input type="datetime-local" name="new_time" id="new_time" required class="mt-1 block w-full px-3 py-2 bg-white dark:bg-zinc-700 border border-gray-300 dark:border-zinc-600 rounded-lg shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                </div>
                <div class="mt-6">
                    <button type="submit" class="w-full bg-primary hover:bg-primaryHover text-white px-6 py-2.5 rounded-lg transition font-medium">
                        Apstiprināt jauno laiku
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/js/appointments.js"></script>

<?php require 'footer.php'; ?>
