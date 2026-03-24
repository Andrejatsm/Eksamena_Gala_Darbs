<?php
session_start();
require 'db.php';

// Pārbaude vai ir psihologs
if (!isset($_SESSION['account_id'], $_SESSION['role']) || $_SESSION['role'] !== 'psychologist') {
    header("Location: login.php");
    exit();
}

$psihologs_id = (int)$_SESSION['account_id'];

// Statusa maiņas loģika
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'], $_POST['appoint_id'])) {
    $status = ($_POST['action'] === 'accept') ? 'approved' : 'rejected';
    $appoint_id = (int)$_POST['appoint_id'];

    $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ? AND psychologist_account_id = ?");
    $stmt->bind_param("sii", $status, $appoint_id, $psihologs_id);
    $stmt->execute();
    $stmt->close();
}

// Iegūstam pieteikumus
$sql = "SELECT * FROM appointments WHERE psychologist_account_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $psihologs_id);
$stmt->execute();
$result = $stmt->get_result();

$pageTitle = "Speciālista Panelis";
require 'header.php';
?>

<div class="min-h-screen bg-gray-50 dark:bg-zinc-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <!-- Header -->
        <div class="mb-12">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white">Sveiki, <?php echo htmlspecialchars($_SESSION['display_name'] ?? ''); ?>!</h1>
                    <p class="text-xl text-gray-600 dark:text-gray-400 mt-2">Pārvaldiet savu praksi un klientus efektīvi</p>
                </div>
                <div class="flex gap-3">
                    <a href="articles.php" class="px-6 py-3 bg-primary text-white font-bold rounded-lg hover:bg-primaryHover transition shadow-lg">
                        <i class="fas fa-plus mr-2"></i>Rakstīt rakstu
                    </a>
                    <a href="availability.php" class="px-6 py-3 bg-gray-200 dark:bg-zinc-700 text-gray-900 dark:text-white font-bold rounded-lg hover:bg-gray-300 dark:hover:bg-zinc-600 transition">
                        <i class="fas fa-calendar-plus mr-2"></i>Pievienot laiku
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
            <?php
            // Get statistics
            $stats = [
                'total_appointments' => 0,
                'pending_appointments' => 0,
                'approved_appointments' => 0,
                'total_articles' => 0
            ];

            // Total appointments
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE psychologist_account_id = ?");
            $stmt->bind_param("i", $psihologs_id);
            $stmt->execute();
            $stats['total_appointments'] = $stmt->get_result()->fetch_assoc()['count'];

            // Pending appointments
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE psychologist_account_id = ? AND status = 'pending'");
            $stmt->bind_param("i", $psihologs_id);
            $stmt->execute();
            $stats['pending_appointments'] = $stmt->get_result()->fetch_assoc()['count'];

            // Approved appointments
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE psychologist_account_id = ? AND status = 'approved'");
            $stmt->bind_param("i", $psihologs_id);
            $stmt->execute();
            $stats['approved_appointments'] = $stmt->get_result()->fetch_assoc()['count'];

            // Total articles
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM articles WHERE psychologist_account_id = ?");
            $stmt->bind_param("i", $psihologs_id);
            $stmt->execute();
            $stats['total_articles'] = $stmt->get_result()->fetch_assoc()['count'];
            ?>

            <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 shadow-lg border border-gray-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase">Kopā pieraksti</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $stats['total_appointments']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-check text-blue-600 dark:text-blue-400 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 shadow-lg border border-gray-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase">Gaida apstiprinājumu</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $stats['pending_appointments']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600 dark:text-yellow-400 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 shadow-lg border border-gray-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase">Apstiprināti</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $stats['approved_appointments']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 shadow-lg border border-gray-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase">Raksti</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $stats['total_articles']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-alt text-purple-600 dark:text-purple-400 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="mb-8">
            <nav class="flex space-x-1 bg-gray-100 dark:bg-zinc-800 p-1 rounded-xl">
                <button class="tab-btn active px-6 py-3 text-sm font-semibold rounded-lg transition bg-white dark:bg-zinc-700 text-gray-900 dark:text-white shadow-sm" data-tab="appointments">Pieraksti</button>
                <button class="tab-btn px-6 py-3 text-sm font-semibold rounded-lg transition text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white dark:hover:bg-zinc-700" data-tab="profile">Profils</button>
                <button class="tab-btn px-6 py-3 text-sm font-semibold rounded-lg transition text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white dark:hover:bg-zinc-700" data-tab="availability">Pieejamība</button>
                <button class="tab-btn px-6 py-3 text-sm font-semibold rounded-lg transition text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white dark:hover:bg-zinc-700" data-tab="articles">Raksti</button>
            </nav>
        </div>

        <!-- Appointments Tab -->
        <div id="appointments-tab" class="tab-content">
            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-lg border border-gray-200 dark:border-zinc-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-zinc-700">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Pierakstu pārvaldība</h3>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Pārskatiet un apstipriniet klientu pierakstus</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                        <thead class="bg-gray-50 dark:bg-zinc-700/50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Klients</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Datums</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Veids</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Statuss</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Darbības</th>
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
                                            <?php echo $row['consultation_type'] === 'online' ? 'Tiešsaiste' : 'Klātienē'; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $statusColor = match($row['status']) {
                                                'approved' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                                'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                                'cancelled' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
                                                default => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                            };
                                            $statusLabel = match($row['status']) {
                                                'pending' => 'Gaida',
                                                'approved' => 'Apstiprināts',
                                                'rejected' => 'Noraidīts',
                                                'cancelled' => 'Atcelts',
                                                default => ucfirst((string)$row['status']),
                                            };
                                            ?>
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusColor; ?>">
                                                <?php echo $statusLabel; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <?php if(($row['status'] ?? '') === 'pending'): ?>
                                                <div class="flex justify-end gap-2">
                                                    <form method="POST" class="inline">
                                                        <input type="hidden" name="appoint_id" value="<?php echo $row['id']; ?>">
                                                        <button type="submit" name="action" value="accept" class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 rounded-lg hover:bg-green-200 dark:hover:bg-green-900/50 transition text-sm font-medium" title="Apstiprināt">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" class="inline">
                                                        <input type="hidden" name="appoint_id" value="<?php echo $row['id']; ?>">
                                                        <button type="submit" name="action" value="reject" class="px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition text-sm font-medium" title="Noraidīt">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-gray-400">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-calendar-times text-4xl text-gray-400 dark:text-gray-600 mb-4"></i>
                                            <p class="text-gray-500 dark:text-gray-400 text-lg">Jums pagaidām nav pierakstu</p>
                                            <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Jauni pieraksti parādīsies šeit</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Profile Tab -->
        <div id="profile-tab" class="tab-content hidden">
            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-lg border border-gray-200 dark:border-zinc-700 p-8">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-primary/20 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-user text-primary text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Profils</h3>
                        <p class="text-gray-600 dark:text-gray-400">Pārvaldiet savu profilu un konta iestatījumus</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <a href="user_profile.php" class="group bg-gray-50 dark:bg-zinc-700/50 rounded-xl p-6 hover:bg-gray-100 dark:hover:bg-zinc-700 transition border border-gray-200 dark:border-zinc-600">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-primary/20 rounded-lg flex items-center justify-center mr-4">
                                    <i class="fas fa-user-edit text-primary"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 dark:text-white group-hover:text-primary transition">Rediģēt profilu</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">E-pasts, telefons, parole</p>
                                </div>
                            </div>
                            <i class="fas fa-arrow-right text-gray-400 group-hover:text-primary transition"></i>
                        </div>
                    </a>

                    <div class="bg-gray-50 dark:bg-zinc-700/50 rounded-xl p-6 border border-gray-200 dark:border-zinc-600">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-shield-alt text-green-600 dark:text-green-400"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white">Drošība</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Jūsu dati ir aizsargāti</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Articles Tab -->
        <div id="articles-tab" class="tab-content hidden">
            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-lg border border-gray-200 dark:border-zinc-700 p-8">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-file-alt text-purple-600 dark:text-purple-400 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Raksti un resursi</h3>
                            <p class="text-gray-600 dark:text-gray-400">Dalieties ar zināšanām un palīdziet klientiem</p>
                        </div>
                    </div>
                    <a href="articles.php" class="px-6 py-3 bg-primary text-white font-bold rounded-lg hover:bg-primaryHover transition shadow-lg">
                        <i class="fas fa-plus mr-2"></i>Rakstīt jaunu
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <a href="articles.php" class="group bg-gray-50 dark:bg-zinc-700/50 rounded-xl p-6 hover:bg-gray-100 dark:hover:bg-zinc-700 transition border border-gray-200 dark:border-zinc-600">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mr-4">
                                    <i class="fas fa-edit text-purple-600 dark:text-purple-400"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 dark:text-white group-hover:text-primary transition">Pārvaldīt rakstus</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Rediģēt un publicēt rakstus</p>
                                </div>
                            </div>
                            <i class="fas fa-arrow-right text-gray-400 group-hover:text-primary transition"></i>
                        </div>
                    </a>

                    <div class="bg-gray-50 dark:bg-zinc-700/50 rounded-xl p-6 border border-gray-200 dark:border-zinc-600">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-chart-line text-blue-600 dark:text-blue-400"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white">Rakstu statistika</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Sekojiet līdzi lasījumiem</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Availability Tab -->
        <div id="availability-tab" class="tab-content hidden">
            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-lg border border-gray-200 dark:border-zinc-700 p-8">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-calendar-plus text-green-600 dark:text-green-400 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Pieejamības pārvaldība</h3>
                            <p class="text-gray-600 dark:text-gray-400">Iestatiet laikus konsultācijām</p>
                        </div>
                    </div>
                    <a href="availability.php" class="px-6 py-3 bg-primary text-white font-bold rounded-lg hover:bg-primaryHover transition shadow-lg">
                        <i class="fas fa-plus mr-2"></i>Pievienot laiku
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <a href="availability.php" class="group bg-gray-50 dark:bg-zinc-700/50 rounded-xl p-6 hover:bg-gray-100 dark:hover:bg-zinc-700 transition border border-gray-200 dark:border-zinc-600">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mr-4">
                                    <i class="fas fa-calendar-alt text-green-600 dark:text-green-400"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 dark:text-white group-hover:text-primary transition">Pārvaldīt laikus</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Rediģēt pieejamos slotus</p>
                                </div>
                            </div>
                            <i class="fas fa-arrow-right text-gray-400 group-hover:text-primary transition"></i>
                        </div>
                    </a>

                    <div class="bg-gray-50 dark:bg-zinc-700/50 rounded-xl p-6 border border-gray-200 dark:border-zinc-600">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-clock text-orange-600 dark:text-orange-400"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white">Grafiks</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Jūsu darba laiki</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Sveiki, <?php echo htmlspecialchars($_SESSION['display_name'] ?? ''); ?>!</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Pārvaldiet savu praksi un klientus.</p>
        </div>
        <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition text-sm font-medium">Iziet</a>
    </div>

    <!-- Tabs -->
    <div class="mb-6">
        <nav class="flex space-x-1 bg-gray-100 dark:bg-zinc-800 p-1 rounded-lg">
            <button class="tab-btn active px-4 py-2 text-sm font-medium rounded-md transition" data-tab="appointments">Pieteikumi</button>
            <button class="tab-btn px-4 py-2 text-sm font-medium rounded-md transition" data-tab="profile">Profils</button>
            <button class="tab-btn px-4 py-2 text-sm font-medium rounded-md transition" data-tab="availability">Pieejamība</button>
            <button class="tab-btn px-4 py-2 text-sm font-medium rounded-md transition" data-tab="articles">Raksti</button>
        </nav>
    </div>

    <!-- Appointments Tab -->
    <div id="appointments-tab" class="tab-content">
        <div class="ui-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                    <thead class="bg-gray-50 dark:bg-zinc-700/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Klients</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">E-pasts</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Datums</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Statuss</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Darbības</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-zinc-700/30 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($row['user_name_snapshot'] ?? ''); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <a href="mailto:<?php echo htmlspecialchars($row['user_email_snapshot'] ?? ''); ?>" class="text-indigo-500 hover:underline">
                                            <?php echo htmlspecialchars($row['user_email_snapshot'] ?? ''); ?>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo date("d.m.Y H:i", strtotime($row['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php 
                                        $statusColor = match($row['status']) {
                                            'approved' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                            'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                            default => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                        };
                                        $statusLabel = match($row['status']) {
                                            'pending' => 'Gaida',
                                            'approved' => 'Apstiprināts',
                                            'rejected' => 'Noraidīts',
                                            'cancelled' => 'Atcelts',
                                            'rescheduled' => 'Pārcelts',
                                            default => ucfirst((string)$row['status']),
                                        };
                                        ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusColor; ?>">
                                            <?php echo $statusLabel; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <?php if(($row['status'] ?? '') === 'pending'): ?>
                                            <form method="POST" class="inline-flex gap-2">
                                                <input type="hidden" name="appoint_id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" name="action" value="accept" class="text-primary hover:text-primaryHover hover:bg-[rgba(20,167,199,0.10)] p-1.5 rounded-lg transition" title="Apstiprināt">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="submit" name="action" value="reject" class="text-red-600 hover:text-red-700 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 p-1.5 rounded-lg transition" title="Noraidīt">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                    Jums pagaidām nav pieteikumu.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Profile Tab -->
    <div id="profile-tab" class="tab-content hidden">
        <div class="ui-card p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Profils</h2>
            <div class="space-y-4">
                <a href="user_profile.php" class="block bg-white dark:bg-zinc-700 rounded-lg p-4 hover:shadow-md transition border border-gray-100 dark:border-zinc-600">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Rediģēt profila informāciju</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">E-pasts, telefons, parole</p>
                        </div>
                        <i class="fas fa-arrow-right text-primary"></i>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Articles Tab -->
    <div id="articles-tab" class="tab-content hidden">
        <div class="ui-card p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Raksti un resursi</h2>
            <a href="articles.php" class="block bg-white dark:bg-zinc-700 rounded-lg p-4 hover:shadow-md transition border border-gray-100 dark:border-zinc-600">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Pārvaldīt rakstus</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Publicēt izglītojošus rakstus</p>
                    </div>
                    <i class="fas fa-arrow-right text-primary"></i>
                </div>
            </a>
        </div>
    </div>

    <!-- Availability Tab -->
    <div id="availability-tab" class="tab-content hidden">
        <div class="ui-card p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Pieejamība</h2>
            <a href="availability.php" class="block bg-white dark:bg-zinc-700 rounded-lg p-4 hover:shadow-md transition border border-gray-100 dark:border-zinc-600">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Pārvaldīt pieejamību</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Pievienot laika slotus konsultācijām</p>
                    </div>
                    <i class="fas fa-arrow-right text-primary"></i>
                </div>
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active from all
            tabBtns.forEach(b => b.classList.remove('active', 'bg-white', 'dark:bg-zinc-700', 'text-gray-900', 'dark:text-white'));
            tabBtns.forEach(b => b.classList.add('text-gray-500', 'dark:text-gray-400'));
            
            // Add active to clicked
            btn.classList.add('active', 'bg-white', 'dark:bg-zinc-700', 'text-gray-900', 'dark:text-white');
            btn.classList.remove('text-gray-500', 'dark:text-gray-400');
            
            // Hide all contents
            tabContents.forEach(content => content.classList.add('hidden'));
            
            // Show selected content
            const tabId = btn.dataset.tab + '-tab';
            document.getElementById(tabId).classList.remove('hidden');
        });
    });
});
</script>

<?php require 'footer.php'; ?>