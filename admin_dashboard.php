<?php
$pageTitle = "Admin panelis";
require 'db.php';
require 'header.php';

// Check admin access
if (!isset($_SESSION['account_id'], $_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$success_msg = '';
$error_msg = '';

// Handle psychologist approval
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $account_id = intval($_POST['account_id'] ?? 0);
    
    if ($action === 'approve_psych') {
        $stmt = $conn->prepare("UPDATE psychologist_profiles SET approved_at = NOW() WHERE account_id = ?");
        $stmt->bind_param("i", $account_id);
        if ($stmt->execute()) {
            $success_msg = "Psihologs apstiprinātā sekmīgi!";
        }
    } elseif ($action === 'reject_psych') {
        $stmt = $conn->prepare("UPDATE accounts SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $account_id);
        if ($stmt->execute()) {
            $success_msg = "Psiholog profils noraidīts.";
        }
    } elseif ($action === 'delete_article') {
        $article_id = intval($_POST['article_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM articles WHERE id = ? AND psychologist_account_id = ?");
        $stmt->bind_param("ii", $article_id, $account_id);
        if ($stmt->execute()) {
            $success_msg = "Raksts izdzēsts sekmīgi.";
        }
    } elseif ($action === 'approve_article') {
        $article_id = intval($_POST['article_id'] ?? 0);
        $stmt = $conn->prepare("UPDATE articles SET is_published = 1 WHERE id = ?");
        $stmt->bind_param("i", $article_id);
        if ($stmt->execute()) {
            $success_msg = "Raksts apstiprinātā un publicēts.";
        }
    } elseif ($action === 'publish_test') {
        $test_id = intval($_POST['test_id'] ?? 0);
        $stmt = $conn->prepare("UPDATE tests SET status = 'published' WHERE id = ?");
        $stmt->bind_param("i", $test_id);
        if ($stmt->execute()) {
            $success_msg = "Tests publicēts sekmīgi.";
        }
    }
}

// Fetch statistics
$stats = [
    'total_users' => 0,
    'total_psychologists' => 0,
    'pending_psychologists' => 0,
    'total_appointments' => 0,
    'pending_articles' => 0,
    'published_tests' => 0
];

$result = $conn->query("SELECT COUNT(*) as count FROM accounts WHERE role = 'user' AND status = 'active'");
$stats['total_users'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM psychologist_profiles WHERE approved_at IS NOT NULL");
$stats['total_psychologists'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM psychologist_profiles WHERE approved_at IS NULL");
$stats['pending_psychologists'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE status IN ('pending', 'approved')");
$stats['total_appointments'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM articles WHERE is_published = 0");
$stats['pending_articles'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM tests WHERE status = 'published'");
$stats['published_tests'] = $result->fetch_assoc()['count'];
?>

<div class="min-h-screen bg-gray-50 dark:bg-zinc-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <?php if ($success_msg): ?>
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-400">
            <i class="fas fa-check-circle mr-2"></i><?php echo $success_msg; ?>
        </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white">Admin panelis</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Pārvaldi platformu, speciālistus, rakstus un testus</p>
        </div>

        <!-- Statistics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-12">
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 border-l-4 border-primary">
                <p class="text-gray-600 dark:text-gray-400 text-sm">Lietotāji</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $stats['total_users']; ?></p>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 border-l-4 border-green-500">
                <p class="text-gray-600 dark:text-gray-400 text-sm">Psihologi</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $stats['total_psychologists']; ?></p>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 border-l-4 border-yellow-500">
                <p class="text-gray-600 dark:text-gray-400 text-sm">Pieraksti</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $stats['total_appointments']; ?></p>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 border-l-4 border-orange-500">
                <p class="text-gray-600 dark:text-gray-400 text-sm">Gaidošie raksti</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $stats['pending_articles']; ?></p>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 border-l-4 border-purple-500">
                <p class="text-gray-600 dark:text-gray-400 text-sm">Testi</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $stats['published_tests']; ?></p>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 border-l-4 border-red-500">
                <p class="text-gray-600 dark:text-gray-400 text-sm">Gaidošie psihologi</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $stats['pending_psychologists']; ?></p>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="flex flex-wrap gap-2 mb-8 border-b border-gray-200 dark:border-zinc-700">
            <button onclick="switchTab('psychologists')" class="tab-btn active px-6 py-3 font-semibold text-sm border-b-2 border-primary text-primary">
                <i class="fas fa-users mr-2"></i>Psihologi
            </button>
            <button onclick="switchTab('articles')" class="tab-btn px-6 py-3 font-semibold text-sm border-b-2 border-transparent text-gray-600 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600">
                <i class="fas fa-newspaper mr-2"></i>Raksti
            </button>
            <button onclick="switchTab('tests')" class="tab-btn px-6 py-3 font-semibold text-sm border-b-2 border-transparent text-gray-600 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600">
                <i class="fas fa-clipboard-list mr-2"></i>Testi
            </button>
        </div>

        <!-- PSYCHOLOGISTS TAB -->
        <div id="psychologists" class="tab-content">
            <div class="space-y-6">
                <!-- Pending Psychologists -->
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400 font-bold text-sm mr-3">
                            <?php echo $stats['pending_psychologists']; ?>
                        </span>
                        Gaidošie psihologi
                    </h3>
                    
                    <div class="grid grid-cols-1 gap-6">
                        <?php
                        $result = $conn->query("
                            SELECT a.id, a.username, a.email, a.phone, p.full_name, p.specialization, p.experience_years, p.description
                            FROM psychologist_profiles p
                            JOIN accounts a ON p.account_id = a.id
                            WHERE p.approved_at IS NULL
                            ORDER BY a.created_at DESC
                        ");
                        
                        if ($result->num_rows === 0) {
                            echo '<p class="text-gray-600 dark:text-gray-400">Nav psihologu, kas gaidītu apstiprinājuma.</p>';
                        } else {
                            while ($psy = $result->fetch_assoc()):
                        ?>
                        <div class="bg-white dark:bg-zinc-800 rounded-lg p-6 border border-gray-200 dark:border-zinc-700">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Vārds</p>
                                    <p class="font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($psy['full_name']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Specializācija</p>
                                    <p class="font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($psy['specialization']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Pieredze</p>
                                    <p class="font-bold text-gray-900 dark:text-white"><?php echo $psy['experience_years']; ?> gadi</p>
                                </div>
                            </div>
                            
                            <p class="text-gray-600 dark:text-gray-400 mb-4"><?php echo htmlspecialchars($psy['description'] ?? 'Nav apraksta'); ?></p>
                            
                            <div class="flex gap-2 flex-wrap">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="approve_psych">
                                    <input type="hidden" name="account_id" value="<?php echo $psy['id']; ?>">
                                    <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition font-semibold">
                                        <i class="fas fa-check mr-2"></i>Apstiprinājums
                                    </button>
                                </form>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="reject_psych">
                                    <input type="hidden" name="account_id" value="<?php echo $psy['id']; ?>">
                                    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition font-semibold" onclick="return confirm('Vai tiešām noraidi šo profilu?');">
                                        <i class="fas fa-times mr-2"></i>Noraidīt
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php
                            endwhile;
                        }
                        ?>
                    </div>
                </div>

                <!-- Approved Psychologists -->
                <div class="mt-12">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400 font-bold text-sm mr-3">
                            <?php echo $stats['total_psychologists']; ?>
                        </span>
                        Apstiprinātie psihologi
                    </h3>
                    
                    <div class="grid grid-cols-1 gap-4">
                        <?php
                        $result = $conn->query("
                            SELECT a.id, a.username, a.email, p.full_name, p.specialization, p.experience_years, p.hourly_rate
                            FROM psychologist_profiles p
                            JOIN accounts a ON p.account_id = a.id
                            WHERE p.approved_at IS NOT NULL
                            ORDER BY a.created_at DESC
                        ");
                        
                        while ($psy = $result->fetch_assoc()):
                        ?>
                        <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 border border-gray-200 dark:border-zinc-700 flex justify-between items-center">
                            <div class="flex-1">
                                <p class="font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($psy['full_name']); ?></p>
                                <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($psy['specialization']); ?> • <?php echo $psy['experience_years']; ?> g. pieredze</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-primary">€<?php echo number_format($psy['hourly_rate'], 2); ?>/h</p>
                                <p class="text-sm text-green-600">✓ Apstiprinātā</p>
                            </div>
                        </div>
                        <?php endwhile; ?>
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
                        echo '<p class="text-gray-600 dark:text-gray-400">Nav rakstu, kas gaidītu apstiprinājuma.</p>';
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
                                <p class="text-gray-700 dark:text-gray-300 mb-4"><?php echo substr(htmlspecialchars($article['content']), 0, 200); ?>...</p>
                                <p class="text-xs text-gray-500 dark:text-gray-500"><?php echo date('d.m.Y H:i', strtotime($article['created_at'])); ?></p>
                            </div>
                            
                            <div class="flex gap-2">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="approve_article">
                                    <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                                    <input type="hidden" name="account_id" value="<?php echo $article['account_id']; ?>">
                                    <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition font-semibold">
                                        <i class="fas fa-check mr-2"></i>Publicēt
                                    </button>
                                </form>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="delete_article">
                                    <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                                    <input type="hidden" name="account_id" value="<?php echo $article['account_id']; ?>">
                                    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition font-semibold" onclick="return confirm('Vai tiešām dzēst šo rakstu?');">
                                        <i class="fas fa-trash mr-2"></i>Dzēst
                                    </button>
                                </form>
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
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Publicētie testi</h3>
                
                <div class="grid grid-cols-1 gap-4">
                    <?php
                    $result = $conn->query("
                        SELECT id, title, description, status, created_at
                        FROM tests
                        ORDER BY created_at DESC
                    ");
                    
                    while ($test = $result->fetch_assoc()):
                        $status_color = match($test['status']) {
                            'published' => 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400',
                            'pending_review' => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-400',
                            'draft' => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400',
                            default => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400'
                        };
                    ?>
                    <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 border border-gray-200 dark:border-zinc-700 flex justify-between items-center">
                        <div class="flex-1">
                            <p class="font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($test['title']); ?></p>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo substr(htmlspecialchars($test['description'] ?? 'Nav apraksta'), 0, 100); ?></p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-sm font-semibold <?php echo $status_color; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $test['status'])); ?>
                        </span>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
    // Show selected tab
    document.getElementById(tabName).classList.remove('hidden');
    
    // Update button styles
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('border-primary', 'text-primary');
        btn.classList.add('border-transparent', 'text-gray-600', 'dark:text-gray-400');
    });
    event.target.closest('.tab-btn').classList.add('border-primary', 'text-primary');
    event.target.closest('.tab-btn').classList.remove('border-transparent', 'text-gray-600', 'dark:text-gray-400');
}
</script>

<?php require 'footer.php'; ?>

