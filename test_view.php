<?php
session_start();
$pageTitle = "Tests";
require 'db.php';

if (empty($_GET['test_id'])) {
    header("Location: tests.php");
    exit();
}

$test_id = (int)$_GET['test_id'];
$is_user_logged_in = isset($_SESSION['account_id'], $_SESSION['role']) && $_SESSION['role'] === 'user';
$account_id = $is_user_logged_in ? (int)$_SESSION['account_id'] : 0;

// Get test
$stmt = $conn->prepare("SELECT id, title, description FROM tests WHERE id = ? AND status = 'published'");
$stmt->bind_param("i", $test_id);
$stmt->execute();
$result = $stmt->get_result();
$test = $result->fetch_assoc();
$stmt->close();

if (!$test) {
    header("Location: tests.php");
    exit();
}

// Get questions
$stmt = $conn->prepare("SELECT id, question_text, sort_order FROM test_questions WHERE test_id = ? ORDER BY sort_order");
$stmt->bind_param("i", $test_id);
$stmt->execute();
$result = $stmt->get_result();
$questions = [];
while($row = $result->fetch_assoc()) {
    $questions[] = $row;
}
$stmt->close();

// Handle test submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_test'])) {
    $score = 0;
    foreach($questions as $q) {
        if(isset($_POST['answer_' . $q['id']])) {
            $score += (int)$_POST['answer_' . $q['id']];
        }
    }

    $max_score = count($questions) * 5;
    $result_text = "Jūs ieguvāt " . $score . " punktus no " . $max_score . " iespējamiem.";

    if ($is_user_logged_in) {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO test_attempts (test_id, user_account_id, total_score, result_text) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $test_id, $account_id, $score, $result_text);
            $stmt->execute();
            $attempt_id = $conn->insert_id;
            $stmt->close();

            foreach($questions as $q) {
                if(isset($_POST['answer_' . $q['id']])) {
                    $answer_value = (int)$_POST['answer_' . $q['id']];
                    $stmt = $conn->prepare("INSERT INTO test_answers (attempt_id, question_id, answer_value) VALUES (?, ?, ?)");
                    $stmt->bind_param("iii", $attempt_id, $q['id'], $answer_value);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            $conn->commit();
            header("Location: test_results.php?attempt_id=" . $attempt_id);
            exit();
        } catch(Exception $e) {
            $conn->rollback();
            echo "Kļūda: " . $e->getMessage();
        }
    } else {
        $_SESSION['guest_test_result'] = [
            'test_id' => $test_id,
            'test_title' => $test['title'],
            'score' => $score,
            'max_score' => $max_score,
            'result_text' => $result_text,
            'created_at' => date('Y-m-d H:i:s')
        ];
        header("Location: test_results.php?guest=1");
        exit();
    }
}

require 'header.php';
?>

<div class="page-shell-narrow page-surface">
    <div class="page-heading">
        <a href="<?php echo $is_user_logged_in ? 'tests.php' : 'index.php#self-tests'; ?>" class="text-primary hover:text-primaryHover text-sm font-medium flex items-center gap-1 mb-4">
            <i class="fas fa-arrow-left"></i> Atpakaļ uz testiem
        </a>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($test['title']); ?></h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2"><?php echo htmlspecialchars($test['description']); ?></p>
    </div>

    <?php if (!$is_user_logged_in): ?>
    <div class="alert-warning mb-6">
        <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-300">
            <i class="fas fa-info-circle mr-2"></i>Viesu režīms: testu vari pildīt bez ielogošanās, bet rezultāts tiks atvērts pēc ielogošanās/reģistrācijas.
        </p>
    </div>
    <?php endif; ?>

    <form method="POST" class="form-card stack-md">
        <?php foreach($questions as $index => $q): ?>
            <div class="border-b border-gray-100 dark:border-zinc-700 pb-6 last:border-b-0">
                <label class="block text-lg font-medium text-gray-900 dark:text-white mb-4">
                    <?php echo ($index + 1) . '. ' . htmlspecialchars($q['question_text']); ?>
                </label>
                <div class="space-y-3">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <label class="flex items-center">
                            <input type="radio" name="answer_<?php echo $q['id']; ?>" value="<?php echo $i; ?>" required class="text-primary focus:ring-primary">
                            <span class="ml-3 text-sm text-gray-700 dark:text-gray-300"><?php echo $i . ' - ' . ($i == 1 ? 'Pilnīgi nepiekrītu' : ($i == 2 ? 'Daļēji nepiekrītu' : ($i == 3 ? 'Neitrāli' : ($i == 4 ? 'Daļēji piekrītu' : 'Pilnīgi piekrītu')))); ?></span>
                        </label>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="pt-6 flex gap-3">
            <button type="submit" name="submit_test" class="button-primary flex-1">
                Iesniedz atbildes
            </button>
            <a href="<?php echo $is_user_logged_in ? 'tests.php' : 'index.php#self-tests'; ?>" class="button-secondary flex-1">
                Atcelt
            </a>
        </div>
    </form>
</div>

<?php require 'footer.php'; ?>
