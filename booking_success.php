<?php
session_start();
$pageTitle = "Apstiprinājums";
require 'db.php';

// Check if user is logged in
if (!isset($_SESSION['account_id'])) {
    header("Location: login.php");
    exit();
}

require 'header.php';

$appointment_id = intval($_GET['appointment_id'] ?? 0);

if (!$appointment_id) {
    header("Location: dashboard.php");
    exit();
}

// Get appointment details
$stmt = $conn->prepare("
    SELECT a.id, a.scheduled_at, a.consultation_type, a.status,
           p.full_name as psychologist_name, p.specialization,
           u.full_name as user_name
    FROM appointments a
    JOIN psychologist_profiles p ON a.psychologist_account_id = p.account_id
    JOIN user_profiles u ON a.user_account_id = u.account_id
    WHERE a.id = ? AND a.user_account_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $appointment_id, $_SESSION['account_id']);
$stmt->execute();
$result = $stmt->get_result();
$appointment = $result->fetch_assoc();

if (!$appointment) {
    header("Location: dashboard.php");
    exit();
}
?>

<div class="min-h-screen page-surface dark:bg-zinc-900">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <!-- Success Card -->
        <div class="text-center mb-12">
            <div class="w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-check text-green-600 dark:text-green-400 text-4xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Konsultācija apstiprinājums!</h1>
            <p class="text-xl text-gray-600 dark:text-gray-400">Jūsu pieraksts ir veiksmīgi apstiprināts</p>
        </div>

        <!-- Appointment Details -->
        <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-lg p-8 border border-gray-200 dark:border-zinc-700 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                <i class="fas fa-calendar-check text-primary mr-3"></i>
                Konsultācijas detaļas
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-50 dark:bg-zinc-700/50 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Speciālists</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($appointment['psychologist_name']); ?></p>
                    <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($appointment['specialization']); ?></p>
                </div>

                <div class="bg-gray-50 dark:bg-zinc-700/50 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Konsultācijas veids</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                        <?php echo $appointment['consultation_type'] === 'online' ? 'Tiešsaistes' : 'Klātienē'; ?>
                    </p>
                </div>

                <div class="bg-gray-50 dark:bg-zinc-700/50 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Datums un laiks</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white"><?php echo date('d.m.Y H:i', strtotime($appointment['scheduled_at'])); ?></p>
                </div>

                <div class="bg-gray-50 dark:bg-zinc-700/50 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Statuss</p>
                    <p class="text-lg font-bold">
                        <span class="px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300">
                            Gaida apstiprinājumu
                        </span>
                    </p>
                </div>

                <div class="bg-gray-50 dark:bg-zinc-700/50 rounded-lg p-4 md:col-span-2">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Pieraksta numurs</p>
                    <p class="text-lg font-bold text-primary font-mono">#<?php echo str_pad($appointment['id'], 6, '0', STR_PAD_LEFT); ?></p>
                </div>
            </div>
        </div>

        <!-- Next Steps -->
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-2xl shadow-lg p-8 border border-blue-200 dark:border-blue-800 mb-8">
            <h3 class="text-xl font-bold text-blue-900 dark:text-blue-300 mb-4 flex items-center">
                <i class="fas fa-info-circle mr-3"></i>
                Kas būs tālāk?
            </h3>
            <ul class="space-y-3 text-blue-900 dark:text-blue-300">
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-blue-600 dark:text-blue-400 mt-1 mr-3 text-lg flex-shrink-0"></i>
                    <span><strong>1. Solas apstiprinājums</strong> - Speciālists apstiprinās jūsu pierakstu 24 stundu laikā</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-blue-600 dark:text-blue-400 mt-1 mr-3 text-lg flex-shrink-0"></i>
                    <span><strong>2. Saņemsiet saiti</strong> - Jūs saņemsiet video saites vai instrukcijas</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-blue-600 dark:text-blue-400 mt-1 mr-3 text-lg flex-shrink-0"></i>
                    <span><strong>3. Konsultācija</strong> - Nokļūsiet pierakstā norādītajā laikā</span>
                </li>
            </ul>
        </div>

        <!-- Action Buttons -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="appointments.php" class="px-6 py-3 bg-primary text-white font-bold rounded-lg hover:bg-primaryHover transition text-center">
                <i class="fas fa-calendar mr-2"></i>Mani pieraksti
            </a>
            <a href="dashboard.php" class="px-6 py-3 bg-gray-200 dark:bg-zinc-700 text-gray-900 dark:text-white font-bold rounded-lg hover:bg-gray-300 dark:hover:bg-zinc-600 transition text-center">
                <i class="fas fa-grid-2 mr-2"></i>Panelis
            </a>
            <a href="index.php" class="px-6 py-3 bg-gray-200 dark:bg-zinc-700 text-gray-900 dark:text-white font-bold rounded-lg hover:bg-gray-300 dark:hover:bg-zinc-600 transition text-center">
                <i class="fas fa-home mr-2"></i>Sākums
            </a>
        </div>

        <!-- Email Confirmation -->
        <div class="mt-8 p-6 bg-gray-100 dark:bg-zinc-800 rounded-lg text-center">
            <p class="text-gray-700 dark:text-gray-300">
                Apstiprinājuma e-pasts nosūtīts uz <strong><?php echo htmlspecialchars($_SESSION['display_name'] ?? 'jūsu e-pastu'); ?></strong>
            </p>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>
