<?php
$pageTitle = "Testa rezultāti";
require 'db.php';
require 'header.php';

if (!isset($_SESSION['account_id']) || empty($_GET['attempt_id'])) {
    header("Location: tests.php");
    exit();
}

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

<div class="flex-grow max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full">
    <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-100 dark:border-zinc-700 p-8 text-center">
        <div class="mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full mb-4">
                <i class="fas fa-check text-3xl text-green-600 dark:text-green-400"></i>
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
            <a href="dashboard.php" class="flex-1 bg-primary hover:bg-primaryHover text-white px-6 py-3 rounded-lg transition font-medium">
                Atrast speciālistu
            </a>
            <a href="tests.php" class="flex-1 border border-gray-300 dark:border-zinc-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700 px-6 py-3 rounded-lg transition font-medium">
                Citi testi
            </a>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>
