<?php
$pageTitle = "Pašnovērtējuma testi";
require 'db.php';
require 'header.php';

if (!isset($_SESSION['account_id'])) {
    header("Location: login.php");
    exit();
}

// Only regular users can access tests
if ($_SESSION['role'] !== 'user') {
    header("Location: " . ($dashboard_link ?? 'login.php'));
    exit();
}

$account_id = (int)$_SESSION['account_id'];

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
$stmt = $conn->prepare($attempts_sql);
$stmt->bind_param("i", $account_id);
$stmt->execute();
$attempts_result = $stmt->get_result();
$attempts = [];
while($row = $attempts_result->fetch_assoc()) {
    $attempts[$row['test_id']][] = $row;
}
$stmt->close();
?>

<div class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Pašnovērtējuma testi</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2">Aizpildiet testus, lai labāk izprastu savu emocionālo stāvokli.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php foreach($tests as $test): ?>
            <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-100 dark:border-zinc-700 p-6 hover:shadow-lg transition flex flex-col">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2"><?php echo htmlspecialchars($test['title']); ?></h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 flex-grow"><?php echo htmlspecialchars($test['description'] ?? 'Nav apraksta.'); ?></p>
                
                <?php if(isset($attempts[$test['id']])): ?>
                    <div class="mb-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                        <p class="text-sm font-medium text-blue-900 dark:text-blue-300">
                            <i class="fas fa-check-circle"></i> Jūs jau esat pabeidzis šo testu
                        </p>
                        <p class="text-xs text-blue-700 dark:text-blue-400 mt-1">
                            Pēdējais rezultāts: <?php echo $attempts[$test['id']][0]['total_score']; ?> punkti
                        </p>
                    </div>
                <?php endif; ?>
                
                <a href="test_view.php?test_id=<?php echo $test['id']; ?>" class="text-center bg-primary hover:bg-primaryHover text-white px-4 py-2 rounded-lg transition font-medium text-sm">
                    <?php echo isset($attempts[$test['id']]) ? 'Atkārtot testu' : 'Sākt testu'; ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if(empty($tests)): ?>
        <div class="bg-gray-50 dark:bg-zinc-800 border border-dashed border-gray-300 dark:border-zinc-700 rounded-2xl p-12 text-center">
            <p class="text-gray-500 dark:text-gray-400">Pašlaik nav pieejamu testu.</p>
        </div>
    <?php endif; ?>
</div>

<?php require 'footer.php'; ?>
