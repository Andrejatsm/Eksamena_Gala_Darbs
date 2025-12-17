<?php 
session_start();
require 'db.php';

// Pārbaudām, vai esam ielogojušies
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Iegūstam datus
$psihologs_id = isset($_SESSION['last_paid_psychologist_id']) ? $_SESSION['last_paid_psychologist_id'] : 1; 
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['vards']; 

// --- Iegūstam E-PASTU no datubāzes ---
$user_email = ""; // Inicializējam mainīgo
$stmt_email = $conn->prepare("SELECT epasts FROM users WHERE id = ?");
$stmt_email->bind_param("i", $user_id);
$stmt_email->execute();
$res_email = $stmt_email->get_result();

if ($row_email = $res_email->fetch_assoc()) {
    $user_email = $row_email['epasts'];
}
$stmt_email->close();
// -------------------------------------

// Pārbaudam vai šis ieraksts jau nav (lai atsvaidzinot lapu nedubultojas)
// Vienkāršota pārbaude: pēdējo 5 min laikā
$check_sql = "SELECT id FROM appointments WHERE user_id = $user_id AND created_at > NOW() - INTERVAL 5 MINUTE";
$check = $conn->query($check_sql);

if ($check && $check->num_rows == 0) {
    // Ievietojam ar atrasto e-pastu
    $stmt = $conn->prepare("INSERT INTO appointments (user_id, psychologist_id, user_name, user_email, statuss) VALUES (?, ?, ?, ?, 'gaida')");
    $stmt->bind_param("iiss", $user_id, $psihologs_id, $user_name, $user_email);
    $stmt->execute();
    $stmt->close();
}

$pageTitle = "Maksājums veiksmīgs";
require 'header.php'; 
?>

<div class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50 dark:bg-zinc-900">
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
        
        <p class="text-gray-600 dark:text-gray-300 mb-8">
            Jūsu pieteikums konsultācijai ir reģistrēts sistēmā. Apstiprinājums nosūtīts uz <strong><?php echo htmlspecialchars($user_email); ?></strong>.
        </p>
        
        <a href="dashboard.php" class="w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-primary hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition transform hover:scale-[1.02] shadow-lg shadow-green-500/30">
            Atgriezties sistēmā
        </a>
    </div>
</div>

<?php require 'footer.php'; ?>