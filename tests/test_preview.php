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
        <div class="preview-heading">
            <h1><?php echo htmlspecialchars($test['title']); ?></h1>
            <p><?php echo htmlspecialchars($test['description'] ?? ''); ?></p>
        </div>

        <div class="info-banner">
            &#9432; Priekšskatījums &mdash; rezultāts netiks saglabāts.
        </div>

        <?php if (empty($questions)): ?>
            <div class="form-card">Šim testam vēl nav pievienotu jautājumu.</div>
        <?php else: ?>
        <form class="form-card">
            <?php foreach ($questions as $index => $q): ?>
                <div class="question-block">
                    <label class="question-label">
                        <?php echo ($index + 1) . '. ' . htmlspecialchars($q['question_text']); ?>
                    </label>
                    <div class="options">
                        <?php
                        $labels = [1 => 'Pilnīgi nepiekrītu', 2 => 'Daļēji nepiekrītu', 3 => 'Neitrāli', 4 => 'Daļēji piekrītu', 5 => 'Pilnīgi piekrītu'];
                        foreach ($labels as $val => $lbl):
                        ?>
                        <label class="option-label">
                            <input type="radio" name="answer_<?php echo $q['id']; ?>" value="<?php echo $val; ?>">
                            <span><?php echo $val . ' — ' . $lbl; ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="form-actions">
                <button type="button" class="btn-primary" disabled>Iesniegt atbildes (priekšskatījums)</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
