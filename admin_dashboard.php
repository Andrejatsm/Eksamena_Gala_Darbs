<?php
session_start();
require 'db.php';

// Check admin access BEFORE including header.php to prevent headers already sent error
if (!isset($_SESSION['account_id'], $_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$pageTitle = "Admin panelis";
require 'header.php';

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
            $success_msg = "Psihologs apstiprināts sekmīgi!";
        }
    } elseif ($action === 'reject_psych') {
        $stmt = $conn->prepare("UPDATE accounts SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $account_id);
        if ($stmt->execute()) {
            $success_msg = "Psihologu profils noraidīts.";
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
            $success_msg = "Raksts apstiprināts un publicēts.";
        }
    } elseif ($action === 'approve_test' || $action === 'publish_test') {
        $test_id = intval($_POST['test_id'] ?? 0);
        $stmt = $conn->prepare("UPDATE tests SET status = 'published' WHERE id = ?");
        $stmt->bind_param("i", $test_id);
        if ($stmt->execute()) {
            $success_msg = "Tests publicēts sekmīgi.";
        }
    } elseif ($action === 'decline_test') {
        $test_id = intval($_POST['test_id'] ?? 0);
        $stmt = $conn->prepare("UPDATE tests SET status = 'archived' WHERE id = ?");
        $stmt->bind_param("i", $test_id);
        if ($stmt->execute()) {
            $success_msg = "Tests noraidīts.";
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

<div class="min-h-screen page-surface dark:bg-zinc-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <?php if ($success_msg): ?>
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-400">
            <i class="fas fa-check-circle mr-2"></i><?php echo $success_msg; ?>
        </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="mb-12">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white">Sveiki, <?php echo htmlspecialchars($_SESSION['display_name'] ?? 'Administrators'); ?>!</h1>
                    <p class="text-xl text-gray-600 dark:text-gray-400 mt-2">Pārvaldi platformu, speciālistus, rakstus un testus efektīvi</p>
                </div>
            </div>
        </div>

        <!-- Statistics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-12">
                <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 shadow-lg border border-gray-200 dark:border-zinc-700 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Lietotāji</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $stats['total_users']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-primary/15 dark:bg-primary/25 rounded-xl flex items-center justify-center">
                        <i class="fas fa-users text-primary text-xl"></i>
                    </div>
                </div>
                <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 shadow-lg border border-gray-200 dark:border-zinc-700 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Psihologi</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $stats['total_psychologists']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-primary/15 dark:bg-primary/25 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user-md text-primary text-xl"></i>
                    </div>
                </div>
                <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 shadow-lg border border-gray-200 dark:border-zinc-700 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Pieraksti</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $stats['total_appointments']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-primary/15 dark:bg-primary/25 rounded-xl flex items-center justify-center">
                        <i class="fas fa-calendar-check text-primary text-xl"></i>
                    </div>
                </div>
                <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 shadow-lg border border-gray-200 dark:border-zinc-700 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Gaidošie raksti</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $stats['pending_articles']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-primary/15 dark:bg-primary/25 rounded-xl flex items-center justify-center">
                        <i class="fas fa-newspaper text-primary text-xl"></i>
                    </div>
                </div>
                <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 shadow-lg border border-gray-200 dark:border-zinc-700 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Testi</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $stats['published_tests']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-primary/15 dark:bg-primary/25 rounded-xl flex items-center justify-center">
                        <i class="fas fa-clipboard-list text-primary text-xl"></i>
                    </div>
                </div>
                <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 shadow-lg border border-gray-200 dark:border-zinc-700 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Gaidošie psihologi</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $stats['pending_psychologists']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-primary/15 dark:bg-primary/25 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user-clock text-primary text-xl"></i>
                    </div>
                </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="mb-8">
            <nav class="flex space-x-1 bg-gray-100 dark:bg-zinc-800 p-1 rounded-xl">
                <button data-tab="psychologists" class="tab-btn flex-1 py-2.5 px-4 text-sm font-semibold rounded-lg transition-all bg-white dark:bg-zinc-700 text-gray-900 dark:text-white shadow-sm">
                    <i class="fas fa-users mr-2"></i>Psihologi
                </button>
                <button data-tab="articles" class="tab-btn flex-1 py-2.5 px-4 text-sm font-semibold rounded-lg transition-all text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    <i class="fas fa-newspaper mr-2"></i>Raksti
                </button>
                <button data-tab="tests" class="tab-btn flex-1 py-2.5 px-4 text-sm font-semibold rounded-lg transition-all text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    <i class="fas fa-clipboard-list mr-2"></i>Testi
                </button>
            </nav>
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
                            SELECT a.id, a.username, a.email, a.phone, p.full_name, p.specialization, p.experience_years, p.description, p.certificate_path
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
                            
                            <div class="flex gap-2 mt-4">
                                <button type="button" class="view-psych-btn px-5 py-2 bg-primary text-white rounded-lg hover:bg-primaryHover transition font-semibold"
                                        data-id="<?php echo $psy['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($psy['full_name']); ?>"
                                        data-spec="<?php echo htmlspecialchars($psy['specialization']); ?>"
                                        data-exp="<?php echo $psy['experience_years']; ?>"
                                        data-desc="<?php echo htmlspecialchars($psy['description'] ?? ''); ?>"
                                        data-email="<?php echo htmlspecialchars($psy['email']); ?>"
                                        data-phone="<?php echo htmlspecialchars($psy['phone']); ?>"
                                        data-cert="<?php echo htmlspecialchars($psy['certificate_path'] ?? ''); ?>">
                                    <i class="fas fa-search mr-2"></i>Skatīt profilu un lēmumu
                                </button>
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
                                <p class="font-bold text-primary">€50 / sesija</p>
                                <p class="text-sm text-green-600">✓ Apstiprināts</p>
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
                                <button type="button" class="view-article-btn px-5 py-2 bg-primary text-white rounded-lg hover:bg-primaryHover transition font-semibold"
                                        data-id="<?php echo $article['id']; ?>"
                                        data-acc="<?php echo $article['account_id']; ?>"
                                        data-title="<?php echo htmlspecialchars($article['title']); ?>"
                                        data-author="<?php echo htmlspecialchars($article['full_name']); ?>"
                                        data-content="<?php echo htmlspecialchars($article['content']); ?>">
                                    <i class="fas fa-book-open mr-2"></i>Lasīt pilnu rakstu
                                </button>
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
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Testu moderācija</h3>
                
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
                            'archived' => 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-400',
                            'draft' => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400',
                            default => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400'
                        };
                        $status_label = match($test['status']) {
                            'pending_review' => 'Gaida pārskatīšanu',
                            'published' => 'Publicēts',
                            'archived' => 'Noraidīts',
                            'draft' => 'Melnraksts',
                            default => ucfirst(str_replace('_', ' ', $test['status']))
                        };
                    ?>
                    <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 border border-gray-200 dark:border-zinc-700 flex justify-between items-center">
                        <div class="flex-1">
                            <p class="font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($test['title']); ?></p>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo substr(htmlspecialchars($test['description'] ?? 'Nav apraksta'), 0, 100); ?></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" class="view-test-btn px-3 py-1 bg-primary/15 dark:bg-primary/25 text-primary rounded-lg hover:bg-primary/25 dark:hover:bg-primary/35 transition text-sm font-medium" title="Priekšskatīt testu" data-test-url="test_view.php?test_id=<?php echo (int)$test['id']; ?>">
                                <i class="fas fa-eye"></i>
                            </button>
                            <span class="px-3 py-1 rounded-full text-sm font-semibold <?php echo $status_color; ?>">
                                <?php echo $status_label; ?>
                            </span>
                            <?php if (($test['status'] ?? '') === 'pending_review' || ($test['status'] ?? '') === 'draft'): ?>
                            <form method="POST" class="inline m-0">
                                <input type="hidden" name="action" value="approve_test">
                                <input type="hidden" name="test_id" value="<?php echo (int)$test['id']; ?>">
                                <button type="submit" class="px-3 py-1 bg-primary text-white rounded-lg hover:bg-primaryHover transition text-sm font-medium" title="Apstiprināt testu">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <form method="POST" class="inline m-0">
                                <input type="hidden" name="action" value="decline_test">
                                <input type="hidden" name="test_id" value="<?php echo (int)$test['id']; ?>">
                                <button type="submit" class="px-3 py-1 bg-gray-200 dark:bg-zinc-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-zinc-600 transition text-sm font-medium" title="Noraidīt testu" onclick="return confirm('Vai tiešām noraidīt šo testu?');">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Test Preview Modal -->
<div id="testPreviewModal" class="hidden fixed inset-0 z-[70] overflow-y-auto" aria-labelledby="test-preview-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div id="testPreviewBackdrop" class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <div class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl w-full">
            <div class="bg-white dark:bg-zinc-800 px-6 pt-5 pb-4 border-b border-gray-200 dark:border-zinc-700 flex items-center justify-between">
                <h3 id="test-preview-title" class="text-xl font-bold text-gray-900 dark:text-white">Testa priekšskatījums</h3>
                <button type="button" id="closeTestPreviewTop" class="px-3 py-2 bg-gray-200 dark:bg-zinc-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-zinc-600 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="bg-gray-50 dark:bg-zinc-900/40 p-4">
                <iframe id="testPreviewFrame" title="Testa priekšskatījums" class="w-full h-[70vh] rounded-lg border border-gray-200 dark:border-zinc-700 bg-white"></iframe>
            </div>
            <div class="bg-gray-50 dark:bg-zinc-700/50 px-6 py-3 flex justify-end">
                <button type="button" id="closeTestPreviewBottom" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primaryHover transition font-semibold">
                    Aizvērt
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Psychologist Approval Modal -->
<div id="psychModal" class="hidden fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('psychModal').classList.add('hidden')"></div>
        <div class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
            <div class="bg-white dark:bg-zinc-800 px-6 pt-5 pb-4">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4" id="psychModalName"></h3>
                <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                    <p><strong>Specializācija:</strong> <span id="psychModalSpec"></span></p>
                    <p><strong>Pieredze:</strong> <span id="psychModalExp"></span> gadi</p>
                    <p><strong>E-pasts:</strong> <span id="psychModalEmail"></span></p>
                    <p><strong>Telefons:</strong> <span id="psychModalPhone"></span></p>
                    <p><strong>Apraksts:</strong></p>
                    <p class="bg-gray-50 dark:bg-zinc-700 p-3 rounded-lg" id="psychModalDesc"></p>
                    <p class="mt-4"><strong>Sertifikāts:</strong></p>
                    <div id="psychModalCertContainer" class="mt-2"></div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-zinc-700/50 px-6 py-3 flex flex-row-reverse gap-2">
                <form method="POST" class="inline m-0">
                    <input type="hidden" name="action" value="approve_psych">
                    <input type="hidden" name="account_id" id="psychModalApproveId">
                    <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition font-semibold">
                        <i class="fas fa-check mr-2"></i>Apstiprināt profilu
                    </button>
                </form>
                <form method="POST" class="inline m-0">
                    <input type="hidden" name="action" value="reject_psych">
                    <input type="hidden" name="account_id" id="psychModalRejectId">
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition font-semibold" onclick="return confirm('Vai tiešām noraidi šo profilu?');">
                        <i class="fas fa-times mr-2"></i>Noraidīt profilu
                    </button>
                </form>
                <button type="button" class="mr-auto px-4 py-2 bg-gray-300 dark:bg-zinc-600 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-400 dark:hover:bg-zinc-500 transition font-semibold" onclick="document.getElementById('psychModal').classList.add('hidden')">
                    Atcelt
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Article Reading Modal -->
<div id="articleModal" class="hidden fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('articleModal').classList.add('hidden')"></div>
        <div class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl w-full">
            <div class="bg-white dark:bg-zinc-800 px-6 pt-5 pb-4">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2" id="articleModalTitle"></h3>
                <p class="text-sm text-primary font-semibold mb-6" id="articleModalAuthor"></p>
                <div class="text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-zinc-700 p-4 rounded-lg whitespace-pre-wrap leading-relaxed max-h-96 overflow-y-auto" id="articleModalContent"></div>
            </div>
            <div class="bg-gray-50 dark:bg-zinc-700/50 px-6 py-3 flex flex-row-reverse gap-2">
                <form method="POST" class="inline m-0">
                    <input type="hidden" name="action" value="approve_article">
                    <input type="hidden" name="article_id" id="articleModalApproveId">
                    <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition font-semibold">
                        <i class="fas fa-check mr-2"></i>Publicēt
                    </button>
                </form>
                <form method="POST" class="inline m-0">
                    <input type="hidden" name="action" value="delete_article">
                    <input type="hidden" name="article_id" id="articleModalRejectId">
                    <input type="hidden" name="account_id" id="articleModalAccId">
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition font-semibold" onclick="return confirm('Vai tiešām dzēst šo rakstu?');">
                        <i class="fas fa-trash mr-2"></i>Dzēst
                    </button>
                </form>
                <button type="button" class="mr-auto px-4 py-2 bg-gray-300 dark:bg-zinc-600 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-400 dark:hover:bg-zinc-500 transition font-semibold" onclick="document.getElementById('articleModal').classList.add('hidden')">Atcelt</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const testPreviewModal = document.getElementById('testPreviewModal');
    const testPreviewFrame = document.getElementById('testPreviewFrame');
    const closeTestPreviewTop = document.getElementById('closeTestPreviewTop');
    const closeTestPreviewBottom = document.getElementById('closeTestPreviewBottom');
    const testPreviewBackdrop = document.getElementById('testPreviewBackdrop');

    const closeTestPreviewModal = () => {
        testPreviewModal.classList.add('hidden');
        testPreviewFrame.src = '';
    };

    document.querySelectorAll('.view-test-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            testPreviewFrame.src = btn.dataset.testUrl;
            testPreviewModal.classList.remove('hidden');
        });
    });

    closeTestPreviewTop.addEventListener('click', closeTestPreviewModal);
    closeTestPreviewBottom.addEventListener('click', closeTestPreviewModal);
    testPreviewBackdrop.addEventListener('click', closeTestPreviewModal);

    // Tab navigation
    const tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => b.classList.remove('bg-white', 'dark:bg-zinc-700', 'text-gray-900', 'dark:text-white', 'shadow-sm'));
            tabBtns.forEach(b => b.classList.add('text-gray-600', 'dark:text-gray-400'));
            tabBtns.forEach(b => b.classList.remove('text-gray-900', 'dark:text-white'));
            btn.classList.add('bg-white', 'dark:bg-zinc-700', 'text-gray-900', 'dark:text-white', 'shadow-sm');
            btn.classList.remove('text-gray-600', 'dark:text-gray-400');
            document.querySelectorAll('.tab-content').forEach(t => t.classList.add('hidden'));
            document.getElementById(btn.dataset.tab).classList.remove('hidden');
        });
    });

    // Psychologists modal mapping
    document.querySelectorAll('.view-psych-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('psychModalName').textContent = btn.dataset.name;
            document.getElementById('psychModalSpec').textContent = btn.dataset.spec;
            document.getElementById('psychModalExp').textContent = btn.dataset.exp;
            document.getElementById('psychModalEmail').textContent = btn.dataset.email;
            document.getElementById('psychModalPhone').textContent = btn.dataset.phone;
            document.getElementById('psychModalDesc').textContent = btn.dataset.desc || 'Nav apraksta sniegts.';
            document.getElementById('psychModalApproveId').value = btn.dataset.id;
            document.getElementById('psychModalRejectId').value = btn.dataset.id;
            
            let certContainer = document.getElementById('psychModalCertContainer');
            if(btn.dataset.cert) {
                certContainer.innerHTML = `<a href="${btn.dataset.cert}" target="_blank" class="text-blue-500 hover:underline"><i class="fas fa-file-pdf mr-2"></i>Apskatīt failu</a>`;
            } else {
                certContainer.innerHTML = `<span class="text-red-500">Fails nav pievienots!</span>`;
            }
            document.getElementById('psychModal').classList.remove('hidden');
        });
    });

    // Articles modal mapping
    document.querySelectorAll('.view-article-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('articleModalTitle').textContent = btn.dataset.title;
            document.getElementById('articleModalAuthor').textContent = "Autors: " + btn.dataset.author;
            document.getElementById('articleModalContent').textContent = btn.dataset.content;
            document.getElementById('articleModalApproveId').value = btn.dataset.id;
            document.getElementById('articleModalRejectId').value = btn.dataset.id;
            document.getElementById('articleModalAccId').value = btn.dataset.acc;
            document.getElementById('articleModal').classList.remove('hidden');
        });
    });
});
</script>

<?php require 'footer.php'; ?>
