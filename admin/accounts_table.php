<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['account_id'], $_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo '<div class="px-6 py-8 text-center text-[#095d7e] dark:text-[#ccecee]">Piekļave liegta.</div>';
    exit();
}

$search = trim((string)($_GET['search'] ?? ''));
$role = (string)($_GET['role'] ?? 'all');
$status = (string)($_GET['status'] ?? 'all');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;

$allowedRoles = ['all', 'user', 'psychologist'];
$allowedStatuses = ['all', 'active', 'pending', 'rejected', 'disabled'];
if (!in_array($role, $allowedRoles, true)) {
    $role = 'all';
}
if (!in_array($status, $allowedStatuses, true)) {
    $status = 'all';
}

$where = ["a.role IN ('user', 'psychologist')"];
$types = '';
$params = [];

if ($role !== 'all') {
    $where[] = 'a.role = ?';
    $types .= 's';
    $params[] = $role;
}

if ($status !== 'all') {
    $where[] = 'a.status = ?';
    $types .= 's';
    $params[] = $status;
}

if ($search !== '') {
    $where[] = "(
        a.username LIKE ?
        OR a.email LIKE ?
        OR COALESCE(pp.full_name, CONCAT_WS(' ', up.first_name, up.last_name), '') LIKE ?
        OR COALESCE(pp.specialization, '') LIKE ?
    )";
    $types .= 'ssss';
    $searchValue = '%' . $search . '%';
    array_push($params, $searchValue, $searchValue, $searchValue, $searchValue);
}

$whereSql = implode(' AND ', $where);

$countStmt = $conn->prepare(
    "SELECT COUNT(*) AS count
     FROM accounts a
     LEFT JOIN user_profiles up ON up.account_id = a.id
     LEFT JOIN psychologist_profiles pp ON pp.account_id = a.id
     WHERE {$whereSql}"
);
if ($types !== '') {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRows = (int)(($countResult->fetch_assoc()['count'] ?? 0));
$countStmt->close();

$totalPages = $totalRows > 0 ? (int)ceil($totalRows / $limit) : 1;
$page = max(1, min($page, $totalPages));
$offset = ($page - 1) * $limit;

$dataStmt = $conn->prepare(
    "SELECT
        a.id,
        a.username,
        a.email,
        a.phone,
        a.role,
        a.status,
        a.created_at,
        COALESCE(NULLIF(pp.full_name, ''), NULLIF(TRIM(CONCAT_WS(' ', up.first_name, up.last_name)), ''), a.username) AS display_name,
        pp.specialization,
        pp.experience_years,
        pp.description,
        pp.certificate_path
     FROM accounts a
     LEFT JOIN user_profiles up ON up.account_id = a.id
     LEFT JOIN psychologist_profiles pp ON pp.account_id = a.id
     WHERE {$whereSql}
     ORDER BY a.created_at DESC
     LIMIT ? OFFSET ?"
);

$dataTypes = $types . 'ii';
$dataParams = [...$params, $limit, $offset];
$dataStmt->bind_param($dataTypes, ...$dataParams);
$dataStmt->execute();
$result = $dataStmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
$dataStmt->close();

$makePageButton = static function (int $targetPage, string $label, bool $isActive = false, bool $disabled = false) use (&$page, &$totalPages): string {
    if ($label === 'Iepriekšējā') {
        if ($disabled) return '<span class="px-3 py-1.5 rounded-lg bg-[#ccecee]/40 text-[#095d7e]/40 font-semibold text-sm cursor-not-allowed"><i class="fas fa-chevron-left mr-1"></i>Iepriekšējā</span>';
        return '<button type="button" data-admin-page="' . $targetPage . '" class="px-3 py-1.5 rounded-lg bg-[#ccecee] text-[#095d7e] hover:bg-[#b8dde0] font-semibold text-sm transition"><i class="fas fa-chevron-left mr-1"></i>Iepriekšējā</button>';
    }
    if ($label === 'Nākamā') {
        if ($disabled) return '<span class="px-3 py-1.5 rounded-lg bg-[#ccecee]/40 text-[#095d7e]/40 font-semibold text-sm cursor-not-allowed">Nākamā<i class="fas fa-chevron-right ml-1"></i></span>';
        return '<button type="button" data-admin-page="' . $targetPage . '" class="px-3 py-1.5 rounded-lg bg-[#ccecee] text-[#095d7e] hover:bg-[#b8dde0] font-semibold text-sm transition">Nākamā<i class="fas fa-chevron-right ml-1"></i></button>';
    }
    // page number — skip, replaced by "Lapa X no Y" text
    return '';
};
?>
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
        <thead class="bg-gray-50 dark:bg-zinc-900/60">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Konts</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Loma</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Papildinfo</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Kontakti</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Statuss</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Izveidots</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Darbības</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
            <?php if (empty($rows)): ?>
            <tr>
                <td colspan="7" class="px-6 py-10 text-center text-gray-600 dark:text-gray-400">Nav atrasts neviens konts pēc izvēlētajiem filtriem.</td>
            </tr>
            <?php else: ?>
            <?php foreach ($rows as $row): ?>
            <?php
                $roleLabel = ($row['role'] ?? '') === 'psychologist' ? 'Psihologs' : 'Lietotājs';
                $roleBadge = ($row['role'] ?? '') === 'psychologist'
                    ? 'bg-[#ccecee] text-[#095d7e] dark:bg-[#095d7e]/20 dark:text-[#ccecee]'
                    : 'bg-[#e2fcd6] text-[#14967f] dark:bg-[#14967f]/20 dark:text-[#e2fcd6]';
                $statusLabel = match ($row['status'] ?? '') {
                    'active' => 'Aktīvs',
                    'pending' => 'Gaida apstiprinājumu',
                    'rejected' => 'Noraidīts',
                    'disabled' => 'Atspējots',
                    default => (string)($row['status'] ?? 'Nezināms'),
                };
                $statusBadge = match ($row['status'] ?? '') {
                    'active' => 'bg-[#e2fcd6] text-[#14967f] dark:bg-[#14967f]/20 dark:text-[#e2fcd6]',
                    'pending' => 'bg-[#ccecee] text-[#095d7e] dark:bg-[#095d7e]/20 dark:text-[#ccecee]',
                    'rejected' => 'bg-[#f1f9ff] text-[#095d7e] border border-[#ccecee] dark:bg-[#095d7e]/10 dark:text-[#ccecee]',
                    'disabled' => 'bg-gray-200 dark:bg-zinc-700 text-gray-700 dark:text-gray-300',
                    default => 'bg-gray-200 dark:bg-zinc-700 text-gray-700 dark:text-gray-300',
                };
            ?>
            <tr>
                <td class="px-4 py-4 align-top">
                    <p class="font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars((string)$row['display_name']); ?></p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">@<?php echo htmlspecialchars((string)$row['username']); ?></p>
                </td>
                <td class="px-4 py-4 align-top">
                    <span class="px-2 py-1 text-xs rounded-full <?php echo $roleBadge; ?>"><?php echo $roleLabel; ?></span>
                </td>
                <td class="px-4 py-4 align-top text-sm text-gray-600 dark:text-gray-400">
                    <?php if (($row['role'] ?? '') === 'psychologist'): ?>
                    <p><?php echo htmlspecialchars((string)($row['specialization'] ?: 'Nav specializācijas')); ?></p>
                    <p><?php echo (int)($row['experience_years'] ?? 0); ?> g. pieredze</p>
                    <p class="font-semibold text-primary">50 € / sesija</p>
                    <?php else: ?>
                    <p>Parastais lietotāja konts</p>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-4 align-top text-sm text-gray-600 dark:text-gray-400">
                    <p><?php echo htmlspecialchars((string)$row['email']); ?></p>
                    <p><?php echo htmlspecialchars((string)($row['phone'] ?: 'Nav norādīts')); ?></p>
                </td>
                <td class="px-4 py-4 align-top">
                    <span class="px-2 py-1 text-xs rounded-full <?php echo $statusBadge; ?>"><?php echo $statusLabel; ?></span>
                </td>
                <td class="px-4 py-4 align-top text-sm text-gray-600 dark:text-gray-400"><?php echo date('d.m.Y H:i', strtotime((string)$row['created_at'])); ?></td>
                <td class="px-4 py-4 align-top">
                    <div class="flex flex-wrap justify-end gap-2">
                        <?php if (($row['role'] ?? '') === 'psychologist'): ?>
                        <button
                            type="button"
                            class="view-psych-btn px-3 py-2 bg-primary text-white rounded-lg hover:bg-primaryHover transition text-sm font-semibold"
                            data-id="<?php echo (int)$row['id']; ?>"
                            data-status="<?php echo htmlspecialchars((string)($row['status'] ?? '')); ?>"
                            data-name="<?php echo htmlspecialchars((string)$row['display_name']); ?>"
                            data-spec="<?php echo htmlspecialchars((string)($row['specialization'] ?? '')); ?>"
                            data-exp="<?php echo (int)($row['experience_years'] ?? 0); ?>"
                            data-desc="<?php echo htmlspecialchars((string)($row['description'] ?? '')); ?>"
                            data-email="<?php echo htmlspecialchars((string)$row['email']); ?>"
                            data-phone="<?php echo htmlspecialchars((string)($row['phone'] ?? '')); ?>"
                            data-cert="<?php echo htmlspecialchars((string)($row['certificate_path'] ?? '')); ?>">
                            <i class="fas fa-eye mr-2"></i>Skatīt
                        </button>
                        <button type="button" data-account-action="delete_psych" data-account-id="<?php echo (int)$row['id']; ?>" data-confirm="Vai tiešām dzēst šo psihologa kontu?" class="px-3 py-2 bg-[#095d7e] text-white rounded-lg hover:bg-[#074e6b] dark:bg-[#095d7e] dark:hover:bg-[#074e6b] transition text-sm font-semibold">
                            <i class="fas fa-trash mr-2"></i>Dzēst
                        </button>
                        <?php else: ?>
                        <button type="button" data-account-action="delete_user" data-account-id="<?php echo (int)$row['id']; ?>" data-confirm="Vai tiešām dzēst šo lietotāja kontu?" class="px-3 py-2 bg-[#095d7e] text-white rounded-lg hover:bg-[#074e6b] dark:bg-[#095d7e] dark:hover:bg-[#074e6b] transition text-sm font-semibold">
                            <i class="fas fa-trash mr-2"></i>Dzēst
                        </button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="px-4 py-4 border-t border-gray-200 dark:border-zinc-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <p class="text-sm text-gray-600 dark:text-gray-400">Atrasti <?php echo $totalRows; ?> konti</p>
    <div class="flex flex-wrap justify-center items-center gap-2">
        <?php echo $makePageButton(max(1, $page - 1), 'Iepriekšējā', false, $page <= 1); ?>
        <span class="text-sm text-gray-600 dark:text-gray-400 px-2">Lapa <?php echo $page; ?> no <?php echo $totalPages; ?></span>
        <?php echo $makePageButton(min($totalPages, $page + 1), 'Nākamā', false, $page >= $totalPages); ?>
    </div>
</div>