<?php
session_start();
require_once __DIR__ . '/../includes/lang.php';
$pageTitle = t('tests_title');
require '../includes/db.php';

require '../includes/header.php';

$account_id = isset($_SESSION['account_id'], $_SESSION['role']) && $_SESSION['role'] === 'user'
    ? (int)$_SESSION['account_id']
    : 0;
$show_attempts = $account_id > 0;
$is_admin_or_psych_logged_in = isset($_SESSION['account_id'], $_SESSION['role'])
    && in_array($_SESSION['role'], ['admin', 'psychologist'], true);
$logged_in_role_label = ($_SESSION['role'] ?? '') === 'admin' ? t('role_admin') : t('role_psychologist');

// Get all published tests
$sql = "SELECT id, title, description FROM tests WHERE status = 'published' ORDER BY created_at DESC";
$result = $conn->query($sql);
$tests = [];
if ($result) {
    while($row = $result->fetch_assoc()) {
        $tests[] = $row;
    }
}

// Get user's test attempts
$attempts_sql = "SELECT test_id, total_score, result_text, created_at FROM test_attempts WHERE user_account_id = ? ORDER BY created_at DESC";
$attempts = [];
if ($show_attempts) {
    $stmt = $conn->prepare($attempts_sql);
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $attempts_result = $stmt->get_result();
    while($row = $attempts_result->fetch_assoc()) {
        $attempts[$row['test_id']][] = $row;
    }
    $stmt->close();
}
?>

<div class="page-shell-wide page-surface">
    <div class="page-heading">
        <h1 class="page-title"><?php echo t('tests_title'); ?></h1>
        <p class="page-subtitle"><?php echo t('tests_subtitle'); ?></p>
    </div>

    <?php if ($is_admin_or_psych_logged_in): ?>
    <div class="alert-warning">
        <?php echo t('test_warning_admin', $logged_in_role_label); ?>
    </div>
    <?php elseif (!$show_attempts): ?>
    <div class="alert-info">
        <?php echo t('test_info_anonymous'); ?>
    </div>
    <?php endif; ?>

    <div class="layout-grid-2">
        <?php foreach($tests as $test): ?>
            <div class="panel-card flex flex-col">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2"><?php echo htmlspecialchars($test['title']); ?></h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 flex-grow"><?php echo htmlspecialchars($test['description'] ?? t('no_description')); ?></p>
                
                <?php if(isset($attempts[$test['id']])): ?>
                    <div class="alert-info mb-4 p-3">
                        <p class="text-sm font-medium text-[#095d7e] dark:text-[#ccecee]">
                            <i class="fas fa-check-circle"></i> <?php echo t('test_completed'); ?>
                        </p>
                        <p class="text-xs text-[#14967f] dark:text-[#e2fcd6] mt-1">
                            <?php echo t('last_result', $attempts[$test['id']][0]['total_score']); ?>
                        </p>
                    </div>
                <?php endif; ?>
                
                <a href="test_view.php?test_id=<?php echo $test['id']; ?>" class="button-primary text-sm">
                    <?php echo isset($attempts[$test['id']]) ? t('retake_test') : t('start_test'); ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if(empty($tests)): ?>
        <div class="empty-card">
            <p class="text-gray-500 dark:text-gray-400"><?php echo t('no_tests_available'); ?></p>
        </div>
    <?php endif; ?>
</div>

<?php require '../includes/footer.php'; ?>
