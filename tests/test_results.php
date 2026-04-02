<?php
session_start();
$pageTitle = "Testa rezultāti";
require '../includes/db.php';

// Guest flow: result is temporarily stored in session until user logs in
if (isset($_GET['guest']) && isset($_SESSION['guest_test_result'])) {
    $guest_result = $_SESSION['guest_test_result'];

    if (!isset($_SESSION['account_id'])) {
        require '../includes/header.php';
        ?>
        <div class="page-shell-narrow page-surface">
            <div class="result-card text-center">
                <div class="mb-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-[#ccecee] dark:bg-[#095d7e]/20 rounded-full mb-4">
                        <i class="fas fa-lock text-3xl text-[#095d7e] dark:text-[#ccecee]"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Tests pabeigts!</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">Lai redzētu rezultātu testam <strong><?php echo htmlspecialchars($guest_result['test_title']); ?></strong>, ielogojies vai izveido profilu.</p>
                </div>

                <div class="panel-card mb-6 text-left">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        <i class="fas fa-shield-alt text-primary mr-2"></i>
                        Tavs testa mēģinājums ir īslaicīgi saglabāts šajā pārlūka sesijā.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="../auth/login.php?next=tests/test_results.php%3Fguest%3D1" class="flex-1 bg-primary hover:bg-primaryHover text-white px-6 py-3 rounded-lg transition font-medium">
                        Ielogoties un redzēt rezultātu
                    </a>
                    <a href="../auth/register.php?next=tests/test_results.php%3Fguest%3D1" class="flex-1 border border-gray-300 dark:border-zinc-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700 px-6 py-3 rounded-lg transition font-medium">
                        Reģistrēties
                    </a>
                </div>
            </div>
        </div>
        <?php
        require '../includes/footer.php';
        exit();
    }

    if (($_SESSION['role'] ?? '') === 'user') {
        $account_id = (int)$_SESSION['account_id'];
        $test_id = (int)$guest_result['test_id'];
        $score = (int)$guest_result['score'];
        $result_text = (string)$guest_result['result_text'];

        $stmt = $conn->prepare("INSERT INTO test_attempts (test_id, user_account_id, total_score, result_text) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $test_id, $account_id, $score, $result_text);
        $stmt->execute();
        $attempt_id = (int)$conn->insert_id;
        $stmt->close();

        unset($_SESSION['guest_test_result']);
        header("Location: test_results.php?attempt_id=" . $attempt_id);
        exit();
    }

    unset($_SESSION['guest_test_result']);
    header("Location: " . (($_SESSION['role'] ?? '') === 'admin' ? '../admin/admin_dashboard.php' : '../specialist/specialist_dashboard.php'));
    exit();
}

if (!isset($_SESSION['account_id']) || empty($_GET['attempt_id'])) {
    header("Location: tests.php");
    exit();
}

require '../includes/header.php';

$attempt_id = (int)$_GET['attempt_id'];
$account_id = (int)$_SESSION['account_id'];

// Get attempt
$stmt = $conn->prepare("SELECT t.id, t.title, ta.total_score, ta.result_text, ta.created_at FROM test_attempts ta JOIN tests t ON ta.test_id = t.id WHERE ta.id = ? AND ta.user_account_id = ?");
$stmt->bind_param("ii", $attempt_id, $account_id);
$stmt->execute();
$result = $stmt->get_result();
$attempt = $result->fetch_assoc();
$stmt->close();

if (!$attempt) {
    header("Location: tests.php");
    exit();
}
?>

<div class="page-shell-narrow page-surface">
    <div class="result-card text-center">
        <div class="mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-[#e2fcd6] dark:bg-[#14967f]/20 rounded-full mb-4">
                <i class="fas fa-check text-3xl text-[#14967f] dark:text-[#e2fcd6]"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Tests pabeigts!</h1>
        </div>

        <div class="bg-gray-50 dark:bg-zinc-700 rounded-lg p-6 mb-6">
            <p class="text-gray-600 dark:text-gray-300 mb-2">Jūsu rezultāts:</p>
            <p class="text-5xl font-bold text-primary mb-2"><?php echo $attempt['total_score']; ?></p>
            <p class="text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($attempt['result_text']); ?></p>
            <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">Pabeigtā: <?php echo date("d.m.Y H:i", strtotime($attempt['created_at'])); ?></p>
        </div>

        <div class="text-sm text-gray-600 dark:text-gray-400 mb-6">
            <p>Pamatojoties uz Jūsu atbildēm, ieteicam sazināties ar speciālistu, kas specializējas jūsu problēmas risināšanā.</p>
        </div>

        <div class="flex gap-4">
            <a href="../pages/dashboard.php" class="button-primary flex-1">
                Atrast speciālistu
            </a>
            <a href="tests.php" class="button-secondary flex-1">
                Citi testi
            </a>
        </div>
    </div>
</div>

<?php require '../includes/footer.php'; ?>
