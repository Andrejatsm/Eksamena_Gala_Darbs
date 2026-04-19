<?php
session_start();
require_once __DIR__ . '/../includes/lang.php';
require '../includes/db.php';

// Check admin access BEFORE including header.php to prevent headers already sent error
if (!isset($_SESSION['account_id'], $_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$pageTitle = t('admin_panel');

$success_msg = '';
$error_msg = '';

if (isset($_SESSION['admin_flash']) && is_array($_SESSION['admin_flash'])) {
    $flashType = (string)($_SESSION['admin_flash']['type'] ?? 'success');
    $flashMessage = (string)($_SESSION['admin_flash']['message'] ?? '');
    if ($flashMessage !== '') {
        if ($flashType === 'error') {
            $error_msg = $flashMessage;
        } else {
            $success_msg = $flashMessage;
        }
    }
    unset($_SESSION['admin_flash']);
}

$countQuery = static function (mysqli $conn, string $query): int {
    $result = $conn->query($query);
    if (!$result) {
        return 0;
    }
    $row = $result->fetch_assoc();
    return (int)($row['count'] ?? 0);
};

$fetchPaginatedData = static function (
    mysqli $conn,
    string $countSql,
    string $dataSql,
    string $types,
    array $params,
    int $page,
    int $perPage
): array {
    $countStmt = $conn->prepare($countSql);
    if ($types !== '' && !empty($params)) {
        $countStmt->bind_param($types, ...$params);
    }
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $total = (int)(($countResult->fetch_assoc()['count'] ?? 0));
    $countStmt->close();

    $totalPages = $total > 0 ? (int)ceil($total / $perPage) : 1;
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;

    $dataStmt = $conn->prepare($dataSql . " LIMIT ? OFFSET ?");
    $dataTypes = $types . 'ii';
    $dataParams = [...$params, $perPage, $offset];
    $dataStmt->bind_param($dataTypes, ...$dataParams);
    $dataStmt->execute();
    $dataResult = $dataStmt->get_result();

    $rows = [];
    while ($row = $dataResult->fetch_assoc()) {
        $rows[] = $row;
    }
    $dataStmt->close();

    return [
        'rows' => $rows,
        'page' => $page,
        'total' => $total,
        'totalPages' => $totalPages,
    ];
};

// Apstrādājam administratora darbības
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $account_id = intval($_POST['account_id'] ?? 0);
    $handledAction = true;
    
    if ($action === 'approve_psych') {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("UPDATE psychologist_profiles SET approved_at = NOW() WHERE account_id = ?");
            $stmt->bind_param("i", $account_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("UPDATE accounts SET status = 'active' WHERE id = ? AND role = 'psychologist'");
            $stmt->bind_param("i", $account_id);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            $success_msg = t('psych_approved');
        } catch (Throwable $e) {
            $conn->rollback();
            $error_msg = t('psych_approve_error');
        }
    } elseif ($action === 'reject_psych') {
        $stmt = $conn->prepare("UPDATE accounts SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $account_id);
        if ($stmt->execute()) {
            $success_msg = t('psych_rejected');
        }
        $stmt->close();
    } elseif ($action === 'delete_user') {
        $stmt = $conn->prepare("DELETE FROM accounts WHERE id = ? AND role = 'user'");
        $stmt->bind_param("i", $account_id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $success_msg = t('account_deleted');
        } else {
            $error_msg = t('account_delete_error');
        }
        $stmt->close();
    } elseif ($action === 'delete_article') {
        $article_id = intval($_POST['article_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM articles WHERE id = ? AND psychologist_account_id = ?");
        $stmt->bind_param("ii", $article_id, $account_id);
        if ($stmt->execute()) {
            $success_msg = t('article_deleted_admin');
        }
    } elseif ($action === 'approve_article') {
        $article_id = intval($_POST['article_id'] ?? 0);
        $stmt = $conn->prepare("UPDATE articles SET is_published = 1 WHERE id = ?");
        $stmt->bind_param("i", $article_id);
        if ($stmt->execute()) {
            $success_msg = t('article_approved');
        }
    } elseif ($action === 'approve_test' || $action === 'publish_test') {
        $test_id = intval($_POST['test_id'] ?? 0);
        $stmt = $conn->prepare("UPDATE tests SET status = 'published' WHERE id = ?");
        $stmt->bind_param("i", $test_id);
        if ($stmt->execute()) {
            $success_msg = t('test_published');
        }
    } elseif ($action === 'decline_test') {
        $test_id = intval($_POST['test_id'] ?? 0);
        $stmt = $conn->prepare("UPDATE tests SET status = 'archived' WHERE id = ?");
        $stmt->bind_param("i", $test_id);
        if ($stmt->execute()) {
            $success_msg = t('test_rejected');
        }
    } elseif ($action === 'add_article_category') {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $error_msg = t('category_empty');
        } else {
            $stmt = $conn->prepare("INSERT INTO article_categories (name, is_active, sort_order) VALUES (?, 1, 999)");
            $stmt->bind_param("s", $name);
            if ($stmt->execute()) {
                $success_msg = t('category_added');
            } else {
                $error_msg = ((int)$conn->errno === 1062)
                    ? t('category_exists')
                    : t('category_add_error');
            }
            $stmt->close();
        }
    } elseif ($action === 'add_specialization') {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $error_msg = t('spec_empty');
        } else {
            $stmt = $conn->prepare("INSERT INTO psychologist_specializations (name, is_active, sort_order) VALUES (?, 1, 999)");
            $stmt->bind_param("s", $name);
            if ($stmt->execute()) {
                $success_msg = t('spec_added');
            } else {
                $error_msg = ((int)$conn->errno === 1062)
                    ? t('spec_exists')
                    : t('spec_add_error');
            }
            $stmt->close();
        }
    } elseif ($action === 'toggle_lookup_status') {
        $lookup_type = $_POST['lookup_type'] ?? '';
        $lookup_id = (int)($_POST['lookup_id'] ?? 0);
        $new_status = (int)($_POST['new_status'] ?? 0);
        $new_status = $new_status === 1 ? 1 : 0;

        $table = null;
        if ($lookup_type === 'article_category') {
            $table = 'article_categories';
        } elseif ($lookup_type === 'specialization') {
            $table = 'psychologist_specializations';
        }

        if ($table === null || $lookup_id <= 0) {
            $error_msg = t('record_invalid');
        } else {
            $sql = "UPDATE {$table} SET is_active = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $new_status, $lookup_id);
            if ($stmt->execute()) {
                $success_msg = $new_status === 1 ? t('record_activated') : t('record_deactivated');
            } else {
                $error_msg = t('record_status_error');
            }
            $stmt->close();
        }
    }

    $_SESSION['admin_flash'] = [
        'type' => $error_msg !== '' ? 'error' : 'success',
        'message' => $error_msg !== '' ? $error_msg : $success_msg,
    ];

    header('Location: ' . ($_SERVER['PHP_SELF'] ?? 'admin_dashboard.php'));
    exit();
}

// Iegūstam statistiku
$stats = [
    'total_users' => 0,
    'total_psychologists' => 0,
    'pending_psychologists' => 0,
    'total_appointments' => 0,
    'pending_articles' => 0,
    'published_tests' => 0
];

$stats['total_users'] = $countQuery($conn, "SELECT COUNT(*) AS count FROM accounts WHERE role = 'user' AND status = 'active'");
$stats['total_psychologists'] = $countQuery($conn, "SELECT COUNT(*) AS count FROM accounts WHERE role = 'psychologist' AND status = 'active'");
$stats['pending_psychologists'] = $countQuery($conn, "SELECT COUNT(*) AS count FROM accounts WHERE role = 'psychologist' AND status = 'pending'");
$stats['total_appointments'] = $countQuery($conn, "SELECT COUNT(*) AS count FROM appointments WHERE status IN ('pending', 'approved')");
$stats['pending_articles'] = $countQuery($conn, "SELECT COUNT(*) AS count FROM articles WHERE is_published = 0");
$stats['published_tests'] = $countQuery($conn, "SELECT COUNT(*) AS count FROM tests WHERE status = 'published'");

$lookup_categories = [];
$lookup_specs = [];

$lookupCatRes = $conn->query("SELECT id, name, is_active, sort_order FROM article_categories ORDER BY sort_order ASC, name ASC");
if ($lookupCatRes) {
    while ($row = $lookupCatRes->fetch_assoc()) {
        $lookup_categories[] = $row;
    }
}

$lookupSpecRes = $conn->query("SELECT id, name, is_active, sort_order FROM psychologist_specializations ORDER BY sort_order ASC, name ASC");
if ($lookupSpecRes) {
    while ($row = $lookupSpecRes->fetch_assoc()) {
        $lookup_specs[] = $row;
    }
}

$chartStats = [
    'users' => $stats['total_users'],
    'psychologists' => $stats['total_psychologists'],
    'pendingPsychologists' => $stats['pending_psychologists'],
    'appointments' => $stats['total_appointments'],
    'articles' => $stats['pending_articles'],
    'tests' => $stats['published_tests'],
];

require '../includes/header.php';
?>

<div class="min-h-screen page-surface dark:bg-zinc-900">
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <?php if ($success_msg): ?>
        <div class="js-auto-dismiss-alert mb-6 p-4 bg-[#e2fcd6] dark:bg-[#14967f]/20 border border-[#14967f]/30 dark:border-[#14967f]/30 rounded-lg text-[#14967f] dark:text-[#e2fcd6]" data-timeout="4500">
            <i class="fas fa-check-circle mr-2"></i><?php echo $success_msg; ?>
        </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
        <div class="js-auto-dismiss-alert mb-6 p-4 bg-[#f1f9ff] dark:bg-[#095d7e]/20 border border-[#ccecee] dark:border-[#095d7e]/30 rounded-lg text-[#095d7e] dark:text-[#ccecee]" data-timeout="6000">
            <i class="fas fa-triangle-exclamation mr-2"></i><?php echo $error_msg; ?>
        </div>
        <?php endif; ?>

        <div id="adminActionFeedback" class="hidden mb-6 p-4 rounded-lg border"></div>

        <!-- Header -->
        <div class="mb-12">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white"><?php echo t('welcome', htmlspecialchars($_SESSION['display_name'] ?? 'Administrators')); ?></h1>
                    <p class="text-xl text-gray-600 dark:text-gray-400 mt-2"><?php echo t('manage_platform'); ?></p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-12">
            <div class="xl:col-span-2 bg-white dark:bg-zinc-800 rounded-2xl p-6 shadow-lg border border-gray-200 dark:border-zinc-700">
                <div class="flex items-start justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white"><?php echo t('platform_overview'); ?></h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1"><?php echo t('overview_subtitle'); ?></p>
                    </div>
                </div>
                <div class="h-80">
                    <canvas id="adminStatsChart"></canvas>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 shadow-lg border border-gray-200 dark:border-zinc-700">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4"><?php echo t('summary'); ?></h2>
                <div class="space-y-4 text-sm">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-zinc-700">
                        <span class="text-gray-600 dark:text-gray-400"><?php echo t('active_users'); ?></span>
                        <strong class="text-gray-900 dark:text-white" data-stat-key="users"><?php echo $stats['total_users']; ?></strong>
                    </div>
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-zinc-700">
                        <span class="text-gray-600 dark:text-gray-400"><?php echo t('active_psychologists'); ?></span>
                        <strong class="text-gray-900 dark:text-white" data-stat-key="psychologists"><?php echo $stats['total_psychologists']; ?></strong>
                    </div>
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-zinc-700">
                        <span class="text-gray-600 dark:text-gray-400"><?php echo t('pending_psychologists'); ?></span>
                        <strong class="text-gray-900 dark:text-white" data-stat-key="pendingPsychologists"><?php echo $stats['pending_psychologists']; ?></strong>
                    </div>
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-zinc-700">
                        <span class="text-gray-600 dark:text-gray-400"><?php echo t('active_appointments'); ?></span>
                        <strong class="text-gray-900 dark:text-white" data-stat-key="appointments"><?php echo $stats['total_appointments']; ?></strong>
                    </div>
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-zinc-700">
                        <span class="text-gray-600 dark:text-gray-400"><?php echo t('pending_articles'); ?></span>
                        <strong class="text-gray-900 dark:text-white" data-stat-key="articles"><?php echo $stats['pending_articles']; ?></strong>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 dark:text-gray-400"><?php echo t('published_tests'); ?></span>
                        <strong class="text-gray-900 dark:text-white" data-stat-key="tests"><?php echo $stats['published_tests']; ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="mb-8">
            <nav class="flex space-x-1 bg-gray-100 dark:bg-zinc-800 p-1 rounded-xl">
                <button data-tab="psychologists" class="tab-btn flex-1 py-2.5 px-4 text-sm font-semibold rounded-lg transition-all bg-white dark:bg-zinc-700 text-gray-900 dark:text-white shadow-sm">
                    <i class="fas fa-users mr-2"></i><?php echo t('tab_users'); ?>
                </button>
                <button data-tab="articles" class="tab-btn flex-1 py-2.5 px-4 text-sm font-semibold rounded-lg transition-all text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    <i class="fas fa-newspaper mr-2"></i><?php echo t('tab_articles'); ?>
                </button>
                <button data-tab="tests" class="tab-btn flex-1 py-2.5 px-4 text-sm font-semibold rounded-lg transition-all text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    <i class="fas fa-clipboard-list mr-2"></i><?php echo t('tab_tests'); ?>
                </button>
                <button data-tab="lookups" class="tab-btn flex-1 py-2.5 px-4 text-sm font-semibold rounded-lg transition-all text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    <i class="fas fa-sliders mr-2"></i><?php echo t('tab_settings'); ?>
                </button>
            </nav>
        </div>

        <!-- PSYCHOLOGISTS TAB -->
        <div id="psychologists" class="tab-content">
            <div class="space-y-6">
                <div class="bg-white dark:bg-zinc-800 rounded-2xl p-5 shadow-lg border border-gray-200 dark:border-zinc-700">
                    <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white"><?php echo t('account_management'); ?></h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1"><?php echo t('account_mgmt_subtitle'); ?></p>
                        </div>
                        <div class="flex flex-col lg:flex-row gap-3 w-full xl:w-auto">
                            <input type="text" id="adminAccountSearch" class="input-control min-w-[260px]" placeholder="<?php echo t('search'); ?>...">
                            <select id="adminRoleFilter" class="input-control min-w-[180px]">
                                <option value="all"><?php echo t('all_roles'); ?></option>
                                <option value="psychologist"><?php echo t('psychologists'); ?></option>
                                <option value="user"><?php echo t('users'); ?></option>
                            </select>
                            <select id="adminStatusFilter" class="input-control min-w-[180px]">
                                <option value="all"><?php echo t('all_statuses'); ?></option>
                                <option value="pending"><?php echo t('status_pending'); ?></option>
                                <option value="active"><?php echo t('status_active'); ?></option>
                                <option value="rejected"><?php echo t('status_rejected'); ?></option>
                                <option value="disabled"><?php echo t('status_disabled'); ?></option>
                            </select>
                            <button type="button" id="adminAccountsReset" class="px-4 py-2 bg-gray-200 dark:bg-zinc-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-zinc-600 transition font-semibold whitespace-nowrap">
                                <?php echo t('clear'); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <div id="adminAccountsContainer" class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-200 dark:border-zinc-700 overflow-hidden">
                    <div class="px-6 py-12 text-center text-gray-600 dark:text-gray-400">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary mb-3"></div>
                        <p><?php echo t('loading_accounts'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ARTICLES TAB -->
        <div id="articles" class="tab-content hidden">
            <div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                    Gaidošie raksti (<span class="text-orange-600"><?php echo $stats['pending_articles']; ?></span>)
                </h3>
                
                <div class="grid grid-cols-1 gap-6">
                    <?php
                    $result = $conn->query("
                        SELECT a.id, a.title, a.content, a.category, a.created_at, p.full_name, p.account_id
                        FROM articles a
                        JOIN psychologist_profiles p ON a.psychologist_account_id = p.account_id
                        WHERE a.is_published = 0
                        ORDER BY a.created_at DESC
                    ");
                    
                    if ($result->num_rows === 0) {
                        echo '<p class="text-gray-600 dark:text-gray-400">' . t('no_articles_yet') . '</p>';
                    } else {
                        while ($article = $result->fetch_assoc()):
                        ?>
                        <div class="bg-white dark:bg-zinc-800 rounded-lg p-6 border border-gray-200 dark:border-zinc-700">
                            <div class="mb-4">
                                <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2"><?php echo htmlspecialchars($article['title']); ?></h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                    <i class="fas fa-user mr-2"></i><?php echo htmlspecialchars($article['full_name']); ?>
                                    <?php if ($article['category']): ?>
                                    • <span class="bg-primary/10 text-primary px-2 py-1 rounded text-xs font-semibold"><?php echo htmlspecialchars($article['category']); ?></span>
                                    <?php endif; ?>
                                </p>
                                <p class="text-gray-700 dark:text-gray-300 mb-4 break-words overflow-hidden"><?php echo htmlspecialchars(mb_strimwidth(strip_tags($article['content']), 0, 200, '...')); ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-500"><?php echo date('d.m.Y H:i', strtotime($article['created_at'])); ?></p>
                            </div>
                            
                            <div class="flex gap-2">
                                <button type="button" class="view-article-btn px-5 py-2 bg-primary text-white rounded-lg hover:bg-primaryHover transition font-semibold"
                                        data-id="<?php echo $article['id']; ?>"
                                        data-acc="<?php echo $article['account_id']; ?>"
                                        data-title="<?php echo htmlspecialchars($article['title']); ?>"
                                        data-author="<?php echo htmlspecialchars($article['full_name']); ?>"
                                        data-content="<?php echo htmlspecialchars($article['content']); ?>">
                                    <i class="fas fa-book-open mr-2"></i><?php echo t('read_full_article'); ?>
                                </button>
                            </div>
                        </div>
                        <?php
                        endwhile;
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- TESTS TAB -->
        <div id="tests" class="tab-content hidden">
            <div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4"><?php echo t('test_moderation'); ?></h3>
                
                <div class="grid grid-cols-1 gap-4">
                    <?php
                    $result = $conn->query("
                        SELECT id, title, description, status, created_at
                        FROM tests
                        ORDER BY created_at DESC
                    ");
                    
                    while ($test = $result->fetch_assoc()):
                        $status_color = match($test['status']) {
                            'published' => 'bg-[#e2fcd6] text-[#14967f] dark:bg-[#14967f]/20 dark:text-[#e2fcd6]',
                            'pending_review' => 'bg-[#ccecee] text-[#095d7e] dark:bg-[#095d7e]/20 dark:text-[#ccecee]',
                            'archived' => 'bg-[#f1f9ff] text-[#095d7e] border border-[#ccecee] dark:bg-[#095d7e]/10 dark:text-[#ccecee]',
                            'draft' => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400',
                            default => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400'
                        };
                        $status_label = match($test['status']) {
                            'pending_review' => t('status_review'),
                            'published' => t('status_published'),
                            'archived' => t('status_rejected'),
                            'draft' => t('status_draft'),
                            default => ucfirst(str_replace('_', ' ', $test['status']))
                        };
                    ?>
                    <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 border border-gray-200 dark:border-zinc-700 flex justify-between items-center">
                        <div class="flex-1">
                            <p class="font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($test['title']); ?></p>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo substr(htmlspecialchars($test['description'] ?? 'Nav apraksta'), 0, 100); ?></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" class="view-test-btn px-3 py-1 bg-primary/15 dark:bg-primary/25 text-primary rounded-lg hover:bg-primary/25 dark:hover:bg-primary/35 transition text-sm font-medium" title="Priekšskatīt testu" data-test-url="../tests/test_preview.php?test_id=<?php echo (int)$test['id']; ?>">
                                <i class="fas fa-eye"></i>
                            </button>
                            <span class="px-3 py-1 rounded-full text-sm font-semibold <?php echo $status_color; ?>">
                                <?php echo $status_label; ?>
                            </span>
                            <?php if (($test['status'] ?? '') === 'pending_review' || ($test['status'] ?? '') === 'draft'): ?>
                            <form method="POST" class="inline m-0">
                                <input type="hidden" name="action" value="approve_test">
                                <input type="hidden" name="test_id" value="<?php echo (int)$test['id']; ?>">
                                <button type="submit" class="px-3 py-1 bg-primary text-white rounded-lg hover:bg-primaryHover transition text-sm font-medium" title="Apstiprināt testu">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <form method="POST" class="inline m-0" data-confirm-delete="Vai tiešām noraidīt šo testu?">
                                <input type="hidden" name="action" value="decline_test">
                                <input type="hidden" name="test_id" value="<?php echo (int)$test['id']; ?>">
                                <button type="submit" class="px-3 py-1 bg-[#ccecee] text-[#095d7e] dark:bg-[#095d7e]/20 dark:text-[#ccecee] rounded-lg hover:bg-[#b8dde0] dark:hover:bg-[#095d7e]/30 transition text-sm font-medium" title="Noraidīt testu">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <div id="lookups" class="tab-content hidden">
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-zinc-800 rounded-lg p-6 border border-gray-200 dark:border-zinc-700">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1"><?php echo t('article_categories'); ?></h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-5"><?php echo t('categories_visible'); ?></p>

                    <form method="POST" class="flex gap-2 mb-5">
                        <input type="hidden" name="action" value="add_article_category">
                        <input type="text" name="name" required maxlength="120" class="input-control" placeholder="<?php echo t('new_category'); ?>">
                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primaryHover transition font-semibold whitespace-nowrap">
                            <i class="fas fa-plus mr-1"></i>Pievienot
                        </button>
                    </form>

                    <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
                        <?php foreach ($lookup_categories as $cat): ?>
                            <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-zinc-700 rounded-lg">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($cat['name']); ?></p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-1 text-xs rounded-full <?php echo ((int)$cat['is_active'] === 1) ? 'bg-primary/15 text-primary' : 'bg-gray-200 dark:bg-zinc-700 text-gray-700 dark:text-gray-300'; ?>">
                                        <?php echo ((int)$cat['is_active'] === 1) ? t('status_active_label') : t('status_inactive_label'); ?>
                                    </span>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="toggle_lookup_status">
                                        <input type="hidden" name="lookup_type" value="article_category">
                                        <input type="hidden" name="lookup_id" value="<?php echo (int)$cat['id']; ?>">
                                        <input type="hidden" name="new_status" value="<?php echo ((int)$cat['is_active'] === 1) ? '0' : '1'; ?>">
                                        <button type="submit" class="px-3 py-1 text-xs rounded-lg <?php echo ((int)$cat['is_active'] === 1) ? 'bg-[#f1f9ff] text-[#095d7e] border border-[#ccecee] hover:bg-[#ccecee] dark:bg-[#095d7e]/10 dark:text-[#ccecee] dark:hover:bg-[#095d7e]/20' : 'bg-[#e2fcd6] text-[#14967f] hover:bg-[#ccecee] dark:bg-[#14967f]/20 dark:text-[#e2fcd6] dark:hover:bg-[#14967f]/30'; ?> transition">
                                            <?php echo ((int)$cat['is_active'] === 1) ? t('deactivate') : t('activate'); ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if (empty($lookup_categories)): ?>
                            <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo t('categories_empty'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-lg p-6 border border-gray-200 dark:border-zinc-700">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1"><?php echo t('psych_specializations'); ?></h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-5"><?php echo t('specs_visible'); ?></p>

                    <form method="POST" class="flex gap-2 mb-5">
                        <input type="hidden" name="action" value="add_specialization">
                        <input type="text" name="name" required maxlength="120" class="input-control" placeholder="<?php echo t('new_specialization'); ?>">
                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primaryHover transition font-semibold whitespace-nowrap">
                            <i class="fas fa-plus mr-1"></i>Pievienot
                        </button>
                    </form>

                    <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
                        <?php foreach ($lookup_specs as $spec): ?>
                            <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-zinc-700 rounded-lg">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($spec['name']); ?></p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-1 text-xs rounded-full <?php echo ((int)$spec['is_active'] === 1) ? 'bg-primary/15 text-primary' : 'bg-gray-200 dark:bg-zinc-700 text-gray-700 dark:text-gray-300'; ?>">
                                        <?php echo ((int)$spec['is_active'] === 1) ? t('status_active_label') : t('status_inactive_label'); ?>
                                    </span>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="toggle_lookup_status">
                                        <input type="hidden" name="lookup_type" value="specialization">
                                        <input type="hidden" name="lookup_id" value="<?php echo (int)$spec['id']; ?>">
                                        <input type="hidden" name="new_status" value="<?php echo ((int)$spec['is_active'] === 1) ? '0' : '1'; ?>">
                                        <button type="submit" class="px-3 py-1 text-xs rounded-lg <?php echo ((int)$spec['is_active'] === 1) ? 'bg-[#f1f9ff] text-[#095d7e] border border-[#ccecee] hover:bg-[#ccecee] dark:bg-[#095d7e]/10 dark:text-[#ccecee] dark:hover:bg-[#095d7e]/20' : 'bg-[#e2fcd6] text-[#14967f] hover:bg-[#ccecee] dark:bg-[#14967f]/20 dark:text-[#e2fcd6] dark:hover:bg-[#14967f]/30'; ?> transition">
                                            <?php echo ((int)$spec['is_active'] === 1) ? t('deactivate') : t('activate'); ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if (empty($lookup_specs)): ?>
                            <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo t('specs_empty'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Test Preview Modal -->
<div id="testPreviewModal" class="hidden fixed inset-0 z-[70] overflow-y-auto" aria-labelledby="test-preview-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div id="testPreviewBackdrop" class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
        <div class="relative bg-surface dark:bg-zinc-800 rounded-2xl border border-[#ccecee] dark:border-zinc-700 shadow-2xl w-full sm:max-w-5xl">
            <div class="px-6 pt-6 pb-4 border-b border-[#ccecee] dark:border-zinc-700 flex items-center justify-between">
                <h3 id="test-preview-title" class="text-xl font-bold text-gray-900 dark:text-white">Testa priešskatiķjums</h3>
                <button type="button" id="closeTestPreviewTop" class="text-gray-400 hover:text-[#095d7e] dark:hover:text-[#ccecee] transition p-1">
                    <i class="fas fa-times fa-lg"></i>
                </button>
            </div>
            <div class="p-4 bg-[#f1f9ff] dark:bg-zinc-900/40">
                <iframe id="testPreviewFrame" title="Testa priešskatiķjums" sandbox="allow-same-origin" class="w-full h-[70vh] rounded-xl border border-[#ccecee] dark:border-zinc-700 bg-surface"></iframe>
            </div>
            <div class="bg-[#f1f9ff] dark:bg-zinc-700/30 border-t border-[#ccecee] dark:border-zinc-700 px-6 py-4 flex justify-end rounded-b-2xl">
                <button type="button" id="closeTestPreviewBottom" class="button-primary px-6 py-2">
                    Aizvērt
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Psychologist Approval Modal -->
<div id="psychModal" class="hidden fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="psych-modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div id="psychModalBackdrop" class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
        <div class="relative bg-surface dark:bg-zinc-800 rounded-2xl border border-[#ccecee] dark:border-zinc-700 shadow-2xl w-full sm:max-w-2xl">
            <div class="px-6 pt-6 pb-4">
                <div class="flex justify-between items-start mb-5">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white" id="psychModalName"></h3>
                    <button type="button" id="closePsychModalTopBtn" class="text-gray-400 hover:text-[#095d7e] dark:hover:text-[#ccecee] transition p-1 ml-4 flex-shrink-0">
                        <i class="fas fa-times fa-lg"></i>
                    </button>
                </div>
                <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                    <p><strong>Specializācija:</strong> <span id="psychModalSpec"></span></p>
                    <p><strong>Pieredze:</strong> <span id="psychModalExp"></span> gadi</p>
                    <p><strong>E-pasts:</strong> <span id="psychModalEmail"></span></p>
                    <p><strong>Telefons:</strong> <span id="psychModalPhone"></span></p>
                    <p><strong>Apraksts:</strong></p>
                    <p class="bg-[#f1f9ff] dark:bg-zinc-700 border border-[#ccecee] dark:border-zinc-600 p-3 rounded-xl" id="psychModalDesc"></p>
                    <p class="mt-4"><strong>Sertifikāts:</strong></p>
                    <div id="psychModalCertContainer" class="mt-2"></div>
                </div>
            </div>
            <div class="bg-[#f1f9ff] dark:bg-zinc-700/30 border-t border-[#ccecee] dark:border-zinc-700 px-6 py-4 flex flex-wrap items-center gap-2 rounded-b-2xl">
                <button id="closePsychModalBtn" type="button" class="px-4 py-2 bg-surface dark:bg-zinc-700 border border-[#ccecee] dark:border-zinc-600 text-[#095d7e] dark:text-[#ccecee] rounded-lg hover:bg-[#ccecee] dark:hover:bg-zinc-600 transition font-semibold whitespace-nowrap">
                    Atcelt
                </button>
                <div class="flex flex-wrap gap-2 ml-auto">
                    <button type="button" id="psychModalDeleteBtn" class="px-4 py-2 bg-[#095d7e] text-white rounded-lg hover:bg-[#074e6b] transition font-semibold whitespace-nowrap">
                        <i class="fas fa-trash mr-1"></i>Dzēst
                    </button>
                    <button type="button" id="psychModalRejectBtn" class="px-4 py-2 bg-[#ccecee] text-[#095d7e] dark:bg-[#095d7e]/20 dark:text-[#ccecee] rounded-lg hover:bg-[#b8dde0] dark:hover:bg-[#095d7e]/30 transition font-semibold whitespace-nowrap">
                        <i class="fas fa-times mr-1"></i>Noraidīt
                    </button>
                    <button type="button" id="psychModalApproveBtn" class="button-primary px-5 py-2 whitespace-nowrap">
                        <i class="fas fa-check mr-1"></i>Apstiprināt
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Article Reading Modal -->
<div id="articleModal" class="hidden fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="article-modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div id="articleModalBackdrop" class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
        <div class="relative bg-surface dark:bg-zinc-800 rounded-2xl border border-[#ccecee] dark:border-zinc-700 shadow-2xl w-full sm:max-w-3xl">
            <div class="px-6 pt-6 pb-4">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white" id="articleModalTitle"></h3>
                    <button type="button" id="closeArticleModalTopBtn" class="text-gray-400 hover:text-[#095d7e] dark:hover:text-[#ccecee] transition p-1 ml-4 flex-shrink-0">
                        <i class="fas fa-times fa-lg"></i>
                    </button>
                </div>
                <p class="text-sm text-primary font-semibold mb-5" id="articleModalAuthor"></p>
                <div class="text-sm text-gray-700 dark:text-gray-300 bg-[#f1f9ff] dark:bg-zinc-700 border border-[#ccecee] dark:border-zinc-600 p-4 rounded-xl leading-relaxed max-h-[60vh] overflow-y-auto overflow-x-hidden break-words article-body" id="articleModalContent"></div>
            </div>
            <div class="bg-[#f1f9ff] dark:bg-zinc-700/30 border-t border-[#ccecee] dark:border-zinc-700 px-6 py-4 flex flex-row-reverse gap-2 rounded-b-2xl">
                <form method="POST" class="inline m-0">
                    <input type="hidden" name="action" value="approve_article">
                    <input type="hidden" name="article_id" id="articleModalApproveId">
                    <button type="submit" class="button-primary px-5 py-2">
                        <i class="fas fa-check mr-2"></i>Publicēt
                    </button>
                </form>
                <form method="POST" class="inline m-0">
                    <input type="hidden" name="action" value="delete_article">
                    <input type="hidden" name="article_id" id="articleModalRejectId">
                    <input type="hidden" name="account_id" id="articleModalAccId">
                    <button type="submit" class="confirm-delete-article px-4 py-2 bg-[#095d7e] text-white rounded-lg hover:bg-[#074e6b] transition font-semibold">
                        <i class="fas fa-trash mr-2"></i>Dzēst
                    </button>
                </form>
                <button id="closeArticleModalBtn" type="button" class="mr-auto px-4 py-2 bg-surface dark:bg-zinc-700 border border-[#ccecee] dark:border-zinc-600 text-[#095d7e] dark:text-[#ccecee] rounded-lg hover:bg-[#ccecee] dark:hover:bg-zinc-600 transition font-semibold">Atcelt</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="application/json" id="admin-dashboard-data"><?php echo json_encode(['chartStats' => $chartStats, 'accountsConfig' => ['listUrl' => 'accounts_table.php', 'actionUrl' => 'accounts_action.php']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
<script src="../assets/js/modals.js"></script>
<script src="admin_dashboard.js"></script>

<?php require '../includes/footer.php'; ?>
