<?php
session_start();
$pageTitle = "Mani raksti";
$pageHead = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">';
require 'database/db.php';

if (!isset($_SESSION['account_id'], $_SESSION['role']) || $_SESSION['role'] !== 'psychologist') {
    header("Location: login.php");
    exit();
}

require 'header.php';

$account_id = (int)$_SESSION['account_id'];
$message = "";
$error = "";
$article_title = '';
$article_content = '';
$selected_category = '';

$article_categories = [];
$catResult = $conn->query("SELECT name FROM article_categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
if ($catResult) {
    while ($catRow = $catResult->fetch_assoc()) {
        $article_categories[] = $catRow['name'];
    }
}

// Apstrādājam raksta izveidi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_article'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $article_title = $title;
    $article_content = $content;
    $selected_category = $category;

    if ($category !== '') {
        $catCheck = $conn->prepare("SELECT id FROM article_categories WHERE name = ? AND is_active = 1 LIMIT 1");
        $catCheck->bind_param("s", $category);
        $catCheck->execute();
        $catExists = $catCheck->get_result()->num_rows > 0;
        $catCheck->close();

        if (!$catExists) {
            $error = "Lūdzu izvēlieties kategoriju no saraksta.";
        }
    }

    if (empty($title) || empty($content)) {
        $error = "Nosaukums un saturs ir obligāti.";
    } elseif (empty($error)) {
        $stmt = $conn->prepare("INSERT INTO articles (psychologist_account_id, title, content, category, is_published) VALUES (?, ?, ?, ?, 0)");
        $stmt->bind_param("isss", $account_id, $title, $content, $category);
        if ($stmt->execute()) {
            $message = "Raksts iesniegts apstiprīšanai! Administrators to pārskatīs.";
            $article_title = '';
            $article_content = '';
            $selected_category = '';
        } else {
            $error = "Kļūda publicējot rakstu.";
        }
        $stmt->close();
    }
}

// Apstrādājam raksta dzēšanu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_article'])) {
    $article_id = (int)$_POST['article_id'];
    $stmt = $conn->prepare("DELETE FROM articles WHERE id = ? AND psychologist_account_id = ?");
    $stmt->bind_param("ii", $article_id, $account_id);
    $stmt->execute();
    $stmt->close();
    $message = "Raksts izdzēsts.";
}

// Iegūstam rakstus
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

<div class="page-shell page-surface">
    <div class="page-heading">
        <h1 class="page-title">Mani raksti</h1>
        <p class="page-subtitle">Pārvaldiet savus izglītojošos rakstus un resursus.</p>
    </div>

    <?php if(!empty($message)): ?>
        <div class="alert-success">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if(!empty($error)): ?>
        <div class="alert-error">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="layout-sidebar-3">
        <!-- Raksta izveides forma -->
        <div class="form-card lg:row-span-2">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Publicēt jaunu rakstu</h2>
            <form method="POST" class="stack-md">
                <div>
                    <label class="field-label">Nosaukums</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($article_title); ?>" required class="input-control">
                </div>

                <div>
                    <label class="field-label">Kategorija (neobligāta)</label>
                    <select name="category" class="select-control">
                        <option value="">Izvēlieties kategoriju</option>
                        <?php foreach ($article_categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $selected_category === $cat ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="field-label">Saturs</label>
                    <textarea name="content" rows="6" required class="textarea-control"><?php echo htmlspecialchars($article_content); ?></textarea>
                </div>

                <button type="submit" name="create_article" class="button-primary w-full">
                    Iesniegt pārskatīšanai
                </button>
            </form>
        </div>

        <!-- Rakstu saraksts -->
        <div class="lg:col-span-2 space-y-4">
            <?php foreach($articles as $article): ?>
                <div class="list-card p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($article['title']); ?></h3>
                            <?php if($article['category']): ?>
                                <p class="text-xs text-primary font-medium mt-1"><?php echo htmlspecialchars($article['category']); ?></p>
                            <?php endif; ?>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2 line-clamp-2"><?php echo htmlspecialchars(mb_strimwidth($article['content'], 0, 100, '...')); ?></p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">Publicēts: <?php echo date('d.m.Y', strtotime($article['created_at'])); ?></p>
                        </div>
                        <form method="POST" class="inline">
                            <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                            <button type="submit" name="delete_article" onclick="return confirm('Dzēst rakstu?')" class="button-danger-icon text-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if(empty($articles)): ?>
                <div class="empty-card">
                    <p class="text-gray-500 dark:text-gray-400">Jūs vēl neesat publicējis nekādus rakstus.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>
