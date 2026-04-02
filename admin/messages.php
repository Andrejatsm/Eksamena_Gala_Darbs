<?php
session_start();
require __DIR__ . '/../database/db.php';

if (!isset($_SESSION['account_id'], $_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$flashSuccess = (string)($_SESSION['admin_messages_success'] ?? '');
$flashError = (string)($_SESSION['admin_messages_error'] ?? '');
unset($_SESSION['admin_messages_success'], $_SESSION['admin_messages_error']);

$search = trim((string)($_GET['search'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_message_id'])) {
    $deleteId = (int)$_POST['delete_message_id'];
    $stmt = $conn->prepare('DELETE FROM contact_messages WHERE id = ?');
    $stmt->bind_param('i', $deleteId);
    if ($stmt->execute()) {
        $_SESSION['admin_messages_success'] = 'Ziņojums dzēsts.';
    } else {
        $_SESSION['admin_messages_error'] = 'Neizdevās dzēst ziņojumu.';
    }
    $stmt->close();

    $query = [];
    if ($search !== '') {
        $query['search'] = $search;
    }
    if ($page > 1) {
        $query['page'] = (string)$page;
    }

    $redirect = 'messages.php';
    if (!empty($query)) {
        $redirect .= '?' . http_build_query($query);
    }

    header('Location: ' . $redirect);
    exit();
}

$where = '';
$types = '';
$params = [];
if ($search !== '') {
    $where = "WHERE name LIKE ? OR email LIKE ? OR COALESCE(subject, '') LIKE ? OR message LIKE ?";
    $types = 'ssss';
    $searchLike = '%' . $search . '%';
    $params = [$searchLike, $searchLike, $searchLike, $searchLike];
}

$countSql = "SELECT COUNT(*) AS count FROM contact_messages $where";
$countStmt = $conn->prepare($countSql);
if ($types !== '') {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalRows = (int)(($countStmt->get_result()->fetch_assoc()['count'] ?? 0));
$countStmt->close();

$totalPages = $totalRows > 0 ? (int)ceil($totalRows / $perPage) : 1;
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$listSql = "
    SELECT id, name, email, subject, message, created_at
    FROM contact_messages
    $where
    ORDER BY created_at DESC
    LIMIT ? OFFSET ?
";
$listStmt = $conn->prepare($listSql);
$listTypes = $types . 'ii';
$listParams = array_merge($params, [$perPage, $offset]);
$listStmt->bind_param($listTypes, ...$listParams);
$listStmt->execute();
$result = $listStmt->get_result();
$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
$listStmt->close();

$pageTitle = 'Admina ziņojumi';
require __DIR__ . '/../header.php';
?>

<div class="min-h-screen page-surface dark:bg-zinc-900">
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Saziņas ziņojumi</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Šeit redzami visi ziņojumi no footer pogas “Sazināties”.</p>
        </div>

        <?php if ($flashSuccess !== ''): ?>
        <div class="mb-4 p-4 rounded-lg border border-[#14967f]/30 bg-[#e2fcd6] dark:bg-[#14967f]/20 text-[#14967f] dark:text-[#e2fcd6]">
            <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($flashSuccess); ?>
        </div>
        <?php endif; ?>

        <?php if ($flashError !== ''): ?>
        <div class="mb-4 p-4 rounded-lg border border-[#ccecee] bg-[#f1f9ff] dark:bg-[#095d7e]/20 dark:border-[#095d7e]/40 text-[#095d7e] dark:text-[#ccecee]">
            <i class="fas fa-triangle-exclamation mr-2"></i><?php echo htmlspecialchars($flashError); ?>
        </div>
        <?php endif; ?>

        <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-200 dark:border-zinc-700 shadow-lg overflow-hidden">
            <div class="p-5 border-b border-gray-200 dark:border-zinc-700">
                <form method="GET" class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                    <div class="w-full sm:max-w-xl">
                        <label class="sr-only" for="msgSearch">Meklēt</label>
                        <input id="msgSearch" type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="input-control" placeholder="Meklēt pēc vārda, e-pasta, tēmas vai ziņas teksta...">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="button-primary">Meklēt</button>
                        <a href="messages.php" class="button-secondary">Notīrīt</a>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                    <thead class="bg-gray-50 dark:bg-zinc-900/60">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Sūtītājs</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Ziņa</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Saņemts</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Darbība</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                        <?php if (empty($messages)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-600 dark:text-gray-400">Nav atrasts neviens ziņojums.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                        <tr>
                            <td class="px-4 py-4 align-top">
                                <div class="font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars((string)$msg['name']); ?></div>
                                <div class="text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars((string)$msg['email']); ?></div>
                            </td>
                           <td class="px-4 py-4 align-top text-sm text-gray-700 dark:text-gray-300">
                                <div class="max-w-2xl whitespace-pre-wrap break-words"><?php echo htmlspecialchars((string)$msg['message']); ?></div>
                            </td>
                            <td class="px-4 py-4 align-top text-sm text-gray-600 dark:text-gray-400"><?php echo date('d.m.Y H:i', strtotime((string)$msg['created_at'])); ?></td>
                            <td class="px-4 py-4 align-top text-right">
                                <form method="POST" onsubmit="return confirm('Vai tiešām dzēst šo ziņojumu?');" class="inline">
                                    <input type="hidden" name="delete_message_id" value="<?php echo (int)$msg['id']; ?>">
                                    <button type="submit" class="px-3 py-2 bg-[#095d7e] text-white rounded-lg hover:bg-[#074e6b] dark:bg-[#095d7e] dark:hover:bg-[#074e6b] transition text-sm font-semibold">
                                        <i class="fas fa-trash mr-1"></i>Dzēst
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-4 border-t border-gray-200 dark:border-zinc-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-sm text-gray-600 dark:text-gray-400">Atrasti <?php echo (int)$totalRows; ?> ziņojumi</p>
                <div class="flex flex-wrap items-center gap-2">
                    <?php
                    $buildPageUrl = static function (int $targetPage, string $searchValue): string {
                        $query = [];
                        if ($searchValue !== '') {
                            $query['search'] = $searchValue;
                        }
                        if ($targetPage > 1) {
                            $query['page'] = (string)$targetPage;
                        }
                        return 'messages.php' . (empty($query) ? '' : '?' . http_build_query($query));
                    };
                    ?>
                    <?php if ($page <= 1): ?>
                        <span class="px-3 py-1.5 rounded-lg bg-[#ccecee]/40 text-[#095d7e]/40 font-semibold text-sm cursor-not-allowed"><i class="fas fa-chevron-left mr-1"></i>Iepriekšējā</span>
                    <?php else: ?>
                        <a href="<?php echo htmlspecialchars($buildPageUrl(max(1, $page - 1), $search)); ?>" class="px-3 py-1.5 rounded-lg bg-[#ccecee] text-[#095d7e] hover:bg-[#b8dde0] font-semibold text-sm transition"><i class="fas fa-chevron-left mr-1"></i>Iepriekšējā</a>
                    <?php endif; ?>
                    <span class="text-sm text-gray-600 dark:text-gray-400 px-2">Lapa <?php echo $page; ?> no <?php echo $totalPages; ?></span>
                    <?php if ($page >= $totalPages): ?>
                        <span class="px-3 py-1.5 rounded-lg bg-[#ccecee]/40 text-[#095d7e]/40 font-semibold text-sm cursor-not-allowed">Nākamā<i class="fas fa-chevron-right ml-1"></i></span>
                    <?php else: ?>
                        <a href="<?php echo htmlspecialchars($buildPageUrl(min($totalPages, $page + 1), $search)); ?>" class="px-3 py-1.5 rounded-lg bg-[#ccecee] text-[#095d7e] hover:bg-[#b8dde0] font-semibold text-sm transition">Nākamā<i class="fas fa-chevron-right ml-1"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../footer.php'; ?>
