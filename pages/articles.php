<?php
session_start();
require_once __DIR__ . '/../includes/lang.php';
$pageTitle = t('my_articles');
$pageHead = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">';
require '../includes/db.php';

if (!isset($_SESSION['account_id'], $_SESSION['role']) || $_SESSION['role'] !== 'psychologist') {
    header("Location: ../auth/login.php");
    exit();
}

require '../includes/header.php';

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
            $error = t('choose_category_error');
        }
    }

    if (empty($title) || empty($content)) {
        $error = t('title_content_required');
    } elseif (empty($error)) {
        $stmt = $conn->prepare("INSERT INTO articles (psychologist_account_id, title, content, category, is_published) VALUES (?, ?, ?, ?, 0)");
        $stmt->bind_param("isss", $account_id, $title, $content, $category);
        if ($stmt->execute()) {
            $message = t('article_submitted');
            $article_title = '';
            $article_content = '';
            $selected_category = '';
        } else {
            $error = t('article_publish_error');
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
    $message = t('article_deleted');
}

// Iegūstam rakstus
$per_page = 6;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;

$count_stmt = $conn->prepare("SELECT COUNT(*) FROM articles WHERE psychologist_account_id = ?");
$count_stmt->bind_param("i", $account_id);
$count_stmt->execute();
$total_articles = (int)$count_stmt->get_result()->fetch_row()[0];
$count_stmt->close();
$total_pages = (int)ceil($total_articles / $per_page);
$page = min($page, max(1, $total_pages));

$sql = "SELECT id, title, content, category, created_at FROM articles WHERE psychologist_account_id = ? ORDER BY created_at DESC LIMIT {$per_page} OFFSET {$offset}";
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
        <h1 class="page-title"><?php echo t('my_articles'); ?></h1>
        <p class="page-subtitle"><?php echo t('manage_articles'); ?></p>
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

    <!-- Raksta izveides forma -->
    <div class="form-card mb-8">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4"><?php echo t('publish_new_article'); ?></h2>
        <form method="POST" id="article-form" class="stack-md">
            <div class="layout-grid-2">
                <div>
                    <label class="field-label"><?php echo t('title'); ?></label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($article_title); ?>" required class="input-control">
                </div>
                <div>
                    <label class="field-label"><?php echo t('category'); ?></label>
                    <select name="category" class="select-control">
                        <option value=""><?php echo t('choose_category'); ?></option>
                        <?php foreach ($article_categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $selected_category === $cat ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div>
                <label class="field-label"><?php echo t('content'); ?></label>
                <div id="article-editor"></div>
                <input type="hidden" name="content" id="article-content-input" value="<?php echo htmlspecialchars($article_content); ?>">
            </div>
            <div>
                <button type="submit" name="create_article" class="button-primary">
                    <?php echo t('submit_for_review'); ?>
                </button>
            </div>
        </form>
    </div>

    <!-- Rakstu saraksts -->
    <div class="layout-grid-2">
        <?php foreach($articles as $article): ?>
            <div class="list-card p-4">
                <div class="flex justify-between items-start gap-3">
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($article['title']); ?></h3>
                        <?php if($article['category']): ?>
                            <p class="text-xs text-primary font-medium mt-1"><?php echo htmlspecialchars($article['category']); ?></p>
                        <?php endif; ?>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2 line-clamp-2"><?php echo htmlspecialchars(strip_tags(mb_strimwidth($article['content'], 0, 120, '...'))); ?></p>
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-2"><?php echo t('published'); ?><?php echo date('d.m.Y', strtotime($article['created_at'])); ?></p>
                    </div>
                    <form method="POST" class="flex-shrink-0" data-confirm-delete="<?php echo t('article_deleted'); ?>">
                        <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                        <button type="submit" name="delete_article" class="button-danger-icon text-sm">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if(empty($articles)): ?>
            <div class="empty-card col-span-full">
                <p class="text-gray-500 dark:text-gray-400"><?php echo t('no_articles_yet'); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
    <div class="flex justify-center items-center gap-2 mt-6">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>" class="pagination-btn"><i class="fas fa-chevron-left mr-1"></i><?php echo t('previous'); ?></a>
        <?php else: ?>
            <span class="pagination-btn-disabled"><i class="fas fa-chevron-left mr-1"></i><?php echo t('previous'); ?></span>
        <?php endif; ?>
        <span class="text-sm text-gray-600 dark:text-gray-400 px-2"><?php echo t('page_of', $page, $total_pages); ?></span>
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>" class="pagination-btn"><?php echo t('next'); ?><i class="fas fa-chevron-right ml-1"></i></a>
        <?php else: ?>
            <span class="pagination-btn-disabled"><?php echo t('next'); ?><i class="fas fa-chevron-right ml-1"></i></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script src="../assets/js/articles.js"></script>
<?php require '../includes/footer.php'; ?>
