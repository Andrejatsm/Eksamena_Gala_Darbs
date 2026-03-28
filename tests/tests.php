<?php
session_start();
$pageTitle = "Pašnovērtējuma testi";
require '../database/db.php';

require '../header.php';

$account_id = isset($_SESSION['account_id'], $_SESSION['role']) && $_SESSION['role'] === 'user'
    ? (int)$_SESSION['account_id']
    : 0;
$show_attempts = $account_id > 0;
$is_admin_or_psych_logged_in = isset($_SESSION['account_id'], $_SESSION['role'])
    && in_array($_SESSION['role'], ['admin', 'psychologist'], true);
$logged_in_role_label = ($_SESSION['role'] ?? '') === 'admin' ? 'administrators' : 'psihologs';

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
        <h1 class="page-title">Pašnovērtējuma testi</h1>
        <p class="page-subtitle">Aizpildiet testus, lai labāk izprastu savu emocionālo stāvokli.</p>
    </div>

    <?php if ($is_admin_or_psych_logged_in): ?>
    <div class="alert-warning">
        Tu esi ielogojies kā <?php echo htmlspecialchars($logged_in_role_label); ?>. Testu vari pildīt, bet šī konta rezultāti netiks saglabāti.
    </div>
    <?php elseif (!$show_attempts): ?>
    <div class="alert-info">
        Testus vari pildīt bez ielogošanās. Lai saglabātu un apskatītu rezultātus, pēc testa beigām vajadzēs ielogoties vai reģistrēties.
    </div>
    <?php endif; ?>

    <div class="layout-grid-2">
        <?php foreach($tests as $test): ?>
            <div class="panel-card hover:shadow-lg transition flex flex-col">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2"><?php echo htmlspecialchars($test['title']); ?></h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 flex-grow"><?php echo htmlspecialchars($test['description'] ?? 'Nav apraksta.'); ?></p>
                
                <?php if(isset($attempts[$test['id']])): ?>
                    <div class="alert-info mb-4 p-3">
                        <p class="text-sm font-medium text-blue-900 dark:text-blue-300">
                            <i class="fas fa-check-circle"></i> Jūs jau esat pabeidzis šo testu
                        </p>
                        <p class="text-xs text-blue-700 dark:text-blue-400 mt-1">
                            Pēdējais rezultāts: <?php echo $attempts[$test['id']][0]['total_score']; ?> punkti
                        </p>
                    </div>
                <?php endif; ?>
                
                <a href="test_view.php?test_id=<?php echo $test['id']; ?>" class="button-primary text-sm">
                    <?php echo isset($attempts[$test['id']]) ? 'Atkārtot testu' : 'Sākt testu'; ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if(empty($tests)): ?>
        <div class="empty-card">
            <p class="text-gray-500 dark:text-gray-400">Pašlaik nav pieejamu testu.</p>
        </div>
    <?php endif; ?>
</div>

<?php require '../footer.php'; ?>
