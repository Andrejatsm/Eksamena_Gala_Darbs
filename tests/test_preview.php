<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['account_id'], $_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo 'Piekļuve liegta.';
    exit();
}

$test_id = isset($_GET['test_id']) ? (int)$_GET['test_id'] : 0;
if ($test_id <= 0) {
    echo 'Nav izvēlēts tests.';
    exit();
}

$testStmt = $conn->prepare("SELECT id, title, description, status FROM tests WHERE id = ? LIMIT 1");
$testStmt->bind_param('i', $test_id);
$testStmt->execute();
$test = $testStmt->get_result()->fetch_assoc();
$testStmt->close();

if (!$test) {
    echo 'Tests netika atrasts.';
    exit();
}

$qStmt = $conn->prepare("SELECT id, question_text, sort_order FROM test_questions WHERE test_id = ? ORDER BY sort_order ASC, id ASC");
$qStmt->bind_param('i', $test_id);
$qStmt->execute();
$qRes = $qStmt->get_result();
$questions = [];
while ($row = $qRes->fetch_assoc()) {
    $questions[] = $row;
}
$qStmt->close();
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testa priekšskatījums</title>
    <link rel="stylesheet" href="../assets/css/test_preview.css">
</head>
<body>
    <div class="preview-wrap">
        <div class="preview-head">
            <h2 class="preview-head-title"><?php echo htmlspecialchars($test['title']); ?></h2>
            <p class="preview-head-desc"><?php echo htmlspecialchars($test['description'] ?? 'Nav apraksta.'); ?></p>
            <span class="status"><?php echo htmlspecialchars($test['status'] ?? 'draft'); ?></span>
            <p class="hint">Priekšskatījums ir tikai lasāms režīms bez navigācijas un bez sesijas darbībām.</p>
        </div>

        <?php if (empty($questions)): ?>
            <div class="question">Šim testam vēl nav pievienotu jautājumu.</div>
        <?php else: ?>
            <?php foreach ($questions as $idx => $q): ?>
                <div class="question">
                    <p class="q-title"><?php echo ($idx + 1) . '. ' . htmlspecialchars($q['question_text']); ?></p>
                    <ol class="scale">
                        <li>Pilnīgi nepiekrītu</li>
                        <li>Daļēji nepiekrītu</li>
                        <li>Neitrāli</li>
                        <li>Daļēji piekrītu</li>
                        <li>Pilnīgi piekrītu</li>
                    </ol>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
