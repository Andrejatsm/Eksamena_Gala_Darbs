<?php 
session_start();
require __DIR__ . '/db.php';

// Pārbaudām, vai esam ielogojušies
if (!isset($_SESSION['account_id'], $_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Iegūstam datus
$psihologs_id = isset($_SESSION['last_paid_psychologist_account_id']) ? (int)$_SESSION['last_paid_psychologist_account_id'] : 0; 
$user_id = (int)$_SESSION['account_id'];
$user_name = $_SESSION['display_name'] ?? ''; 

// --- Iegūstam E-PASTU no datubāzes ---
$user_email = ""; // Inicializējam mainīgo
$stmt_email = $conn->prepare("SELECT email FROM accounts WHERE id = ?");
$stmt_email->bind_param("i", $user_id);
$stmt_email->execute();
$res_email = $stmt_email->get_result();

if ($row_email = $res_email->fetch_assoc()) {
    $user_email = $row_email['email'];
}
$stmt_email->close();
// -------------------------------------

// Pārbaudam vai šis ieraksts jau nav (lai atsvaidzinot lapu nedubultojas)
// Vienkāršota pārbaude: pēdējo 5 min laikā
$check = null;
$scheduled_at = isset($_SESSION['booking_scheduled_at']) ? $_SESSION['booking_scheduled_at'] : null;

if ($psihologs_id > 0) {
    if ($scheduled_at) {
        $stmt_check = $conn->prepare("SELECT id FROM appointments WHERE user_account_id = ? AND psychologist_account_id = ? AND scheduled_at = ? AND created_at > NOW() - INTERVAL 5 MINUTE");
        $stmt_check->bind_param("iis", $user_id, $psihologs_id, $scheduled_at);
    } else {
        $stmt_check = $conn->prepare("SELECT id FROM appointments WHERE user_account_id = ? AND psychologist_account_id = ? AND created_at > NOW() - INTERVAL 5 MINUTE");
        $stmt_check->bind_param("ii", $user_id, $psihologs_id);
    }
    $stmt_check->execute();
    $check = $stmt_check->get_result();
    $stmt_check->close();
}

if ($check && $check->num_rows == 0) {
    if ($scheduled_at) {
        $stmt = $conn->prepare("INSERT INTO appointments (user_account_id, psychologist_account_id, scheduled_at, user_name_snapshot, user_email_snapshot, status) VALUES (?, ?, ?, ?, ?, 'approved')");
        $stmt->bind_param("iisss", $user_id, $psihologs_id, $scheduled_at, $user_name, $user_email);
    } else {
        $stmt = $conn->prepare("INSERT INTO appointments (user_account_id, psychologist_account_id, user_name_snapshot, user_email_snapshot, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param("iiss", $user_id, $psihologs_id, $user_name, $user_email);
    }
    $stmt->execute();
    $stmt->close();
}

$pageTitle = "Maksājums veiksmīgs";
require __DIR__ . '/header.php'; 
?>

<div class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-surface dark:bg-zinc-900">
    <div class="max-w-md w-full bg-white dark:bg-zinc-800 p-8 rounded-2xl shadow-xl border-2 border-green-100 dark:border-green-900/30 text-center">
        
        <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 dark:bg-green-900/50 mb-6">
            <i class="fas fa-check text-4xl text-green-600 dark:text-green-400"></i>
        </div>

        <h1 class="text-3xl font-extrabold text-green-600 dark:text-green-400 mb-2">
            Paldies!
        </h1>
        
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            Maksājums saņemts veiksmīgi.
        </h3>
        
        <?php if ($scheduled_at): ?>
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
            <h4 class="font-bold text-blue-900 dark:text-blue-100 mb-2">Jūsu konsultācija ir apstiprināta:</h4>
            <p class="text-blue-800 dark:text-blue-200">
                <strong>Laiks:</strong> <?php echo date('d.m.Y H:i', strtotime($scheduled_at)); ?><br>
                <strong>Ilgums:</strong> 1 stunda
            </p>
        </div>
        <?php endif; ?>
        
        <p class="text-gray-600 dark:text-gray-300 mb-8">
            Jūsu pieteikums konsultācijai ir reģistrēts sistēmā. Apstiprinājums nosūtīts uz <strong><?php echo htmlspecialchars($user_email); ?></strong>.
        </p>
        
        <a href="dashboard.php" class="w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-lg text-white bg-primary hover:bg-primaryHover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition transform hover:scale-[1.02] shadow-lg shadow-primary/20">
            Atgriezties sistēmā
        </a>
    </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>