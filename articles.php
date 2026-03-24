<?php
$pageTitle = "Mani raksti";
require 'db.php';
require 'header.php';

if (!isset($_SESSION['account_id'], $_SESSION['role']) || $_SESSION['role'] !== 'psychologist') {
    header("Location: login.php");
    exit();
}

$account_id = (int)$_SESSION['account_id'];
$message = "";
$error = "";

// Handle article creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_article'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? '');

    if(empty($title) || empty($content)) {
        $error = "Nosaukums un saturs ir obligāti.";
    } else {
        $stmt = $conn->prepare("INSERT INTO articles (psychologist_account_id, title, content, category, is_published) VALUES (?, ?, ?, ?, 1)");
        $stmt->bind_param("isss", $account_id, $title, $content, $category);
        if($stmt->execute()) {
            $message = "Raksts publicēts veiksmīgi!";
        } else {
            $error = "Kļūda publicējot rakstu.";
        }
        $stmt->close();
    }
}

// Handle article deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_article'])) {
    $article_id = (int)$_POST['article_id'];
    $stmt = $conn->prepare("DELETE FROM articles WHERE id = ? AND psychologist_account_id = ?");
    $stmt->bind_param("ii", $article_id, $account_id);
    $stmt->execute();
    $stmt->close();
    $message = "Raksts izdzēsts.";
}

// Get articles
$sql = "SELECT id, title, content, category, created_at FROM articles WHERE psychologist_account_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();
$articles = [];
while($row = $result->fetch_assoc()) {
    $articles[] = $row;
}
$stmt->close();
?>

<div class="flex-grow max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Mani raksti</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2">Pārvaldiet savus izglītojošos rakstus un resursus.</p>
    </div>

    <?php if(!empty($message)): ?>
        <div class="mb-6 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-600 dark:text-green-400 px-4 py-3 rounded-lg">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if(!empty($error)): ?>
        <div class="mb-6 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 px-4 py-3 rounded-lg">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Create form -->
        <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-100 dark:border-zinc-700 p-6 lg:row-span-2">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Publicēt jaunu rakstu</h2>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nosaukums</label>
                    <input type="text" name="title" required class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2.5 border transition">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategorija (neobligāta)</label>
                    <input type="text" name="category" placeholder="Piem. Stresa vadīšana" class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2.5 border transition">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Saturs</label>
                    <textarea name="content" rows="6" required class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2.5 border transition"></textarea>
                </div>

                <button type="submit" name="create_article" class="w-full bg-primary hover:bg-primaryHover text-white px-4 py-2 rounded-lg transition font-medium">
                    Publicēt
                </button>
            </form>
        </div>

        <!-- Articles list -->
        <div class="lg:col-span-2 space-y-4">
            <?php foreach($articles as $article): ?>
                <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-100 dark:border-zinc-700 p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($article['title']); ?></h3>
                            <?php if($article['category']): ?>
                                <p class="text-xs text-primary font-medium mt-1"><?php echo htmlspecialchars($article['category']); ?></p>
                            <?php endif; ?>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2 line-clamp-2"><?php echo htmlspecialchars(substr($article['content'], 0, 100)) . '...'; ?></p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">Publicēts: <?php echo date('d.m.Y', strtotime($article['created_at'])); ?></p>
                        </div>
                        <form method="POST" class="inline">
                            <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                            <button type="submit" name="delete_article" onclick="return confirm('Dzēst rakstu?')" class="text-red-600 hover:text-red-700 dark:text-red-400 text-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if(empty($articles)): ?>
                <div class="bg-gray-50 dark:bg-zinc-800 border border-dashed border-gray-300 dark:border-zinc-700 rounded-lg p-8 text-center">
                    <p class="text-gray-500 dark:text-gray-400">Jūs vēl neesat publicējis nekādus rakstus.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>
