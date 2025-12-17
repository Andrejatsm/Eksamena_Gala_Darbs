<?php
session_start();
require 'db.php';

// Pārbaude vai ir psihologs
if (!isset($_SESSION['psihologs_id'])) {
    header("Location: login_specialist.php");
    exit();
}

$psihologs_id = $_SESSION['psihologs_id'];

// Statusa maiņas loģika
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'], $_POST['appoint_id'])) {
    $status = ($_POST['action'] === 'accept') ? 'apstiprinats' : 'noraidits';
    $appoint_id = (int)$_POST['appoint_id'];
    
    // Ja noraida, varam arī izdzēst, bet labāk atstāt vēsturei kā 'noraidits'
    $stmt = $conn->prepare("UPDATE appointments SET statuss = ? WHERE id = ? AND psychologist_id = ?");
    $stmt->bind_param("sii", $status, $appoint_id, $psihologs_id);
    $stmt->execute();
    $stmt->close();
}

// Iegūstam pieteikumus
$sql = "SELECT * FROM appointments WHERE psychologist_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $psihologs_id);
$stmt->execute();
$result = $stmt->get_result();

$pageTitle = "Speciālista Panelis";
require 'header.php'; 
?>

<div class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Sveiki, <?php echo htmlspecialchars($_SESSION['psihologs_vards']); ?>!</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Šeit ir jūsu konsultāciju pieteikumi.</p>
        </div>
        <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition text-sm font-medium">Iziet</a>
    </div>

    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 overflow-hidden">
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
                                    <?php echo htmlspecialchars($row['user_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <a href="mailto:<?php echo htmlspecialchars($row['user_email']); ?>" class="text-indigo-500 hover:underline">
                                        <?php echo htmlspecialchars($row['user_email']); ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <?php echo date("d.m.Y H:i", strtotime($row['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php 
                                    $statusColor = match($row['statuss']) {
                                        'apstiprinats' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                        'noraidits' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                        default => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                    };
                                    ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusColor; ?>">
                                        <?php echo ucfirst($row['statuss']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <?php if($row['statuss'] == 'gaida'): ?>
                                        <form method="POST" class="inline-flex gap-2">
                                            <input type="hidden" name="appoint_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="action" value="accept" class="text-green-600 hover:text-green-900 dark:hover:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 p-1 rounded transition" title="Apstiprināt">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="submit" name="action" value="reject" class="text-red-600 hover:text-red-900 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 p-1 rounded transition" title="Noraidīt">
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

<?php require 'footer.php'; ?>