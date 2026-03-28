<?php 
session_start();
require __DIR__ . '/database/db.php';

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
$consultation_type = isset($_SESSION['booking_consultation_type']) && in_array($_SESSION['booking_consultation_type'], ['online', 'in_person'], true)
    ? $_SESSION['booking_consultation_type']
    : 'online';

if ($psihologs_id > 0) {
    $checkSql = "SELECT id FROM appointments WHERE user_account_id = ? AND psychologist_account_id = ?";
    $checkTypes = "ii";
    $checkParams = [$user_id, $psihologs_id];

    if ($scheduled_at) {
        $checkSql .= " AND scheduled_at = ?";
        $checkTypes .= "s";
        $checkParams[] = $scheduled_at;
    }
    $checkSql .= " AND created_at > NOW() - INTERVAL 5 MINUTE";

    $stmt_check = $conn->prepare($checkSql);
    if ($checkTypes === "iis") {
        $stmt_check->bind_param($checkTypes, $checkParams[0], $checkParams[1], $checkParams[2]);
    } else {
        $stmt_check->bind_param($checkTypes, $checkParams[0], $checkParams[1]);
    }
    $stmt_check->execute();
    $check = $stmt_check->get_result();
    $stmt_check->close();
}

if ($check && $check->num_rows == 0) {
    if ($scheduled_at) {
        $stmt = $conn->prepare("INSERT INTO appointments (user_account_id, psychologist_account_id, scheduled_at, consultation_type, user_name_snapshot, user_email_snapshot, status) VALUES (?, ?, ?, ?, ?, ?, 'approved')");
        $stmt->bind_param("iissss", $user_id, $psihologs_id, $scheduled_at, $consultation_type, $user_name, $user_email);
    } else {
        $stmt = $conn->prepare("INSERT INTO appointments (user_account_id, psychologist_account_id, consultation_type, user_name_snapshot, user_email_snapshot, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("iisss", $user_id, $psihologs_id, $consultation_type, $user_name, $user_email);
    }
    $stmt->execute();
    $stmt->close();
}

$pageTitle = "Maksājums veiksmīgs";
require __DIR__ . '/header.php'; 
?>

<div class="auth-shell page-surface">
    <div class="result-card max-w-md w-full text-center border-2 border-green-100 dark:border-green-900/30">
        
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
                <strong>Veids:</strong> <?php echo $consultation_type === 'online' ? 'Tiešsaistē' : 'Klātienē'; ?><br>
                <strong>Ilgums:</strong> 1 stunda
            </p>
        </div>
        <?php endif; ?>
        
        <p class="text-gray-600 dark:text-gray-300 mb-8">
            Jūsu pieteikums konsultācijai ir reģistrēts sistēmā. Apstiprinājums nosūtīts uz <strong><?php echo htmlspecialchars($user_email); ?></strong>.
        </p>
        
        <a href="dashboard.php" class="button-primary w-full">
            Atgriezties sistēmā
        </a>
    </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>