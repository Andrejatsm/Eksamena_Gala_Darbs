<?php
session_start();
require '../database/db.php';

// Pārbaude vai ir psihologs
if (!isset($_SESSION['account_id'], $_SESSION['role']) || $_SESSION['role'] !== 'psychologist') {
    header("Location: ../login.php");
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
require '../header.php';
?>

<div class="min-h-screen page-surface dark:bg-zinc-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <!-- Header -->
        <div class="mb-12">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white">Sveiki, <?php echo htmlspecialchars($_SESSION['display_name'] ?? ''); ?>!</h1>
                    <p class="text-xl text-gray-600 dark:text-gray-400 mt-2">Pārvaldiet savu praksi un klientus efektīvi</p>
                </div>
                <div class="flex gap-3">
                    <a href="../articles.php" class="px-6 py-3 bg-primary text-white font-bold rounded-lg hover:bg-primaryHover transition shadow-lg">
                        <i class="fas fa-plus mr-2"></i>Rakstīt rakstu
                    </a>
                    <a href="../availability.php" class="px-6 py-3 bg-gray-200 dark:bg-zinc-700 text-gray-900 dark:text-white font-bold rounded-lg hover:bg-gray-300 dark:hover:bg-zinc-600 transition">
                        <i class="fas fa-calendar-plus mr-2"></i>Pievienot laiku
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

            <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 shadow-lg border border-gray-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase">Kopā pieraksti</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $stats['total_appointments']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-primary/15 dark:bg-primary/25 rounded-xl flex items-center justify-center">
                        <i class="fas fa-calendar-check text-primary text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 shadow-lg border border-gray-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase">Gaida apstiprinājumu</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $stats['pending_appointments']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-primary/15 dark:bg-primary/25 rounded-xl flex items-center justify-center">
                        <i class="fas fa-clock text-primary text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 shadow-lg border border-gray-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase">Apstiprināti</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $stats['approved_appointments']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-primary/15 dark:bg-primary/25 rounded-xl flex items-center justify-center">
                        <i class="fas fa-check-circle text-primary text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 shadow-lg border border-gray-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase">Raksti</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $stats['total_articles']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-primary/15 dark:bg-primary/25 rounded-xl flex items-center justify-center">
                        <i class="fas fa-file-alt text-primary text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div>
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
                                                        <button type="submit" name="action" value="accept" class="px-3 py-1 bg-primary/15 dark:bg-primary/25 text-primary rounded-lg hover:bg-primary/25 dark:hover:bg-primary/35 transition text-sm font-medium" title="Apstiprināt">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" class="inline">
                                                        <input type="hidden" name="appoint_id" value="<?php echo $row['id']; ?>">
                                                        <button type="submit" name="action" value="reject" class="px-3 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 rounded-lg hover:bg-amber-200 dark:hover:bg-amber-900/50 transition text-sm font-medium" title="Noraidīt">
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

    </div>
</div>

<?php require '../footer.php'; ?>