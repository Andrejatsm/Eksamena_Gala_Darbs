<?php
$pageTitle = "Tests";
require 'db.php';

if (!isset($_SESSION['account_id']) || empty($_GET['test_id'])) {
    header("Location: tests.php");
    exit();
}

$test_id = (int)$_GET['test_id'];
$account_id = (int)$_SESSION['account_id'];

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
    $conn->begin_transaction();
    try {
        // Create test attempt
        $score = 0;
        $answer_count = 0;
        foreach($questions as $q) {
            if(isset($_POST['answer_' . $q['id']])) {
                $answer_count++;
                $score += (int)$_POST['answer_' . $q['id']];
            }
        }
        
        $result_text = "Jūs ieguvāt " . $score . " punktus no " . (count($questions) * 5) . " iespējamiem.";
        
        $stmt = $conn->prepare("INSERT INTO test_attempts (test_id, user_account_id, total_score, result_text) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $test_id, $account_id, $score, $result_text);
        $stmt->execute();
        $attempt_id = $conn->insert_id;
        $stmt->close();
        
        // Save answers
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
}

require 'header.php';

$test_id = (int)$_GET['test_id'];
$account_id = (int)$_SESSION['account_id'];

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
    $conn->begin_transaction();
    try {
        // Create test attempt
        $score = 0;
        $answer_count = 0;
        foreach($questions as $q) {
            if(isset($_POST['answer_' . $q['id']])) {
                $answer_count++;
                $score += (int)$_POST['answer_' . $q['id']];
            }
        }
        
        $result_text = "Jūs ieguvāt " . $score . " punktus no " . (count($questions) * 5) . " iespējamiem.";
        
        $stmt = $conn->prepare("INSERT INTO test_attempts (test_id, user_account_id, total_score, result_text) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $test_id, $account_id, $score, $result_text);
        $stmt->execute();
        $attempt_id = $conn->insert_id;
        $stmt->close();
        
        // Save answers
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
}
?>

<div class="flex-grow max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full">
    <div class="mb-8">
        <a href="tests.php" class="text-primary hover:text-primaryHover text-sm font-medium flex items-center gap-1 mb-4">
            <i class="fas fa-arrow-left"></i> Atpakaļ uz testiem
        </a>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($test['title']); ?></h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2"><?php echo htmlspecialchars($test['description']); ?></p>
    </div>

    <form method="POST" class="space-y-6 bg-white dark:bg-zinc-800 rounded-2xl border border-gray-100 dark:border-zinc-700 p-6">
        <?php foreach($questions as $index => $q): ?>
            <div class="border-b border-gray-100 dark:border-zinc-700 pb-6 last:border-b-0">
                <label class="block text-lg font-medium text-gray-900 dark:text-white mb-4">
                    <?php echo ($index + 1) . '. ' . htmlspecialchars($q['question_text']); ?>
                </label>
                <div class="space-y-3">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <label class="flex items-center">
                            <input type="radio" name="answer_<?php echo $q['id']; ?>" value="<?php echo $i; ?>" required class="text-primary focus:ring-primary">
                            <span class="ml-3 text-sm text-gray-700 dark:text-gray-300"><?php echo $i . ' - ' . ($i == 1 ? 'Pilnīgi nepiekrītu' : ($i == 5 ? 'Pilnīgi piekrītu' : 'Neitrāli')); ?></span>
                        </label>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="pt-6 flex gap-3">
            <button type="submit" name="submit_test" class="flex-1 bg-primary hover:bg-primaryHover text-white px-6 py-3 rounded-lg transition font-medium">
                Iesniedz atbildes
            </button>
            <a href="tests.php" class="flex-1 text-center border border-gray-300 dark:border-zinc-600 text-gray-700 dark:text-gray-300 px-6 py-3 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-700 transition font-medium">
                Atcelt
            </a>
        </div>
    </form>
</div>

<?php require 'footer.php'; ?>
