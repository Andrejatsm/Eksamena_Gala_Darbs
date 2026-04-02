<?php
session_start();
$pageTitle = 'Publicētie raksti';
$pageStyles = ['content.css'];
require '../includes/db.php';
require '../includes/header.php';

$article_id = (int)($_GET['id'] ?? 0);

if ($article_id > 0) {
    $stmt = $conn->prepare(
        "SELECT a.id, a.title, a.content, a.category, a.created_at, p.full_name
         FROM articles a
         JOIN psychologist_profiles p ON a.psychologist_account_id = p.account_id
         WHERE a.id = ? AND a.is_published = 1"
    );
    $stmt->bind_param('i', $article_id);
    $stmt->execute();
    $article = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$article) {
        header('Location: published_articles.php');
        exit();
    }
}

$articles = [];
if ($article_id === 0) {
    $per_page = 9;
    $page = max(1, (int)($_GET['page'] ?? 1));
    $offset = ($page - 1) * $per_page;

    $count_result = $conn->query("SELECT COUNT(*) FROM articles a JOIN psychologist_profiles p ON a.psychologist_account_id = p.account_id WHERE a.is_published = 1");
    $total_articles = (int)$count_result->fetch_row()[0];
    $total_pages = (int)ceil($total_articles / $per_page);
    $page = min($page, max(1, $total_pages));

    $result = $conn->query(
        "SELECT a.id, a.title, a.content, a.category, a.created_at, p.full_name
         FROM articles a
         JOIN psychologist_profiles p ON a.psychologist_account_id = p.account_id
         WHERE a.is_published = 1
         ORDER BY a.created_at DESC
         LIMIT {$per_page} OFFSET {$offset}"
    );
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $articles[] = $row;
        }
    }
}
?>

<div class="page-shell page-surface articles-page">
    <?php if ($article_id > 0): ?>
    <div class="page-heading">
        <a href="published_articles.php" class="button-link-icon mb-4">
            <i class="fas fa-arrow-left"></i>Atpakaļ uz rakstiem
        </a>
        <h1 class="page-title"><?php echo htmlspecialchars($article['title']); ?></h1>
        <p class="page-subtitle">
            <?php echo htmlspecialchars($article['full_name']); ?>
            <span class="mx-2">•</span>
            <?php echo date('d.m.Y', strtotime($article['created_at'])); ?>
        </p>
    </div>

    <article class="form-card article-detail-card">
        <?php if (!empty($article['category'])): ?>
        <p class="text-sm text-primary font-semibold mb-4"><?php echo htmlspecialchars($article['category']); ?></p>
        <?php endif; ?>
        <div class="text-gray-700 dark:text-gray-300 leading-relaxed article-body">
            <?php echo $article['content']; ?>
        </div>
    </article>
    <?php else: ?>
    <div class="page-heading">
        <h1 class="page-title">Publicētie raksti</h1>
        <p class="page-subtitle">Lasiet psihologu sagatavotos rakstus par mentālo veselību, attiecībām un ikdienas labsajūtu.</p>
    </div>

    <div class="layout-grid-2 articles-grid">
        <?php foreach ($articles as $article): ?>
        <article class="panel-card flex flex-col article-tile">
            <?php if (!empty($article['category'])): ?>
            <p class="text-sm text-primary font-semibold mb-3"><?php echo htmlspecialchars($article['category']); ?></p>
            <?php endif; ?>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3"><?php echo htmlspecialchars($article['title']); ?></h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                <?php echo htmlspecialchars($article['full_name']); ?>
                <span class="mx-2">•</span>
                <?php echo date('d.m.Y', strtotime($article['created_at'])); ?>
            </p>
            <p class="text-gray-600 dark:text-gray-400 mb-5 flex-grow">
                <?php echo htmlspecialchars(strip_tags(mb_substr($article['content'], 0, 220))); ?>...
            </p>
            <a href="published_articles.php?id=<?php echo (int)$article['id']; ?>" class="button-primary">Lasīt pilno rakstu</a>
        </article>
        <?php endforeach; ?>

        <?php if (empty($articles)): ?>
        <div class="empty-card col-span-full">
            <p class="text-gray-500 dark:text-gray-400">Pašlaik nav publicētu rakstu.</p>
        </div>
        <?php endif; ?>
    </div>
    <?php if (!empty($total_pages) && $total_pages > 1): ?>
    <div class="flex justify-center items-center gap-2 mt-8">
        <?php
        $buildUrl = fn($p) => '?' . http_build_query(array_filter(['page' => $p > 1 ? $p : null]));
        ?>
        <?php if ($page > 1): ?>
            <a href="<?php echo htmlspecialchars($buildUrl($page - 1)); ?>" class="px-3 py-1.5 rounded-lg bg-[#ccecee] text-[#095d7e] hover:bg-[#b8dde0] font-semibold text-sm transition"><i class="fas fa-chevron-left mr-1"></i>Iepriekšējā</a>
        <?php else: ?>
            <span class="px-3 py-1.5 rounded-lg bg-[#ccecee]/40 text-[#095d7e]/40 font-semibold text-sm cursor-not-allowed"><i class="fas fa-chevron-left mr-1"></i>Iepriekšējā</span>
        <?php endif; ?>
        <span class="text-sm text-gray-600 dark:text-gray-400 px-2">Lapa <?php echo $page; ?> no <?php echo $total_pages; ?></span>
        <?php if ($page < $total_pages): ?>
            <a href="<?php echo htmlspecialchars($buildUrl($page + 1)); ?>" class="px-3 py-1.5 rounded-lg bg-[#ccecee] text-[#095d7e] hover:bg-[#b8dde0] font-semibold text-sm transition">Nākamā<i class="fas fa-chevron-right ml-1"></i></a>
        <?php else: ?>
            <span class="px-3 py-1.5 rounded-lg bg-[#ccecee]/40 text-[#095d7e]/40 font-semibold text-sm cursor-not-allowed">Nākamā<i class="fas fa-chevron-right ml-1"></i></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>    <?php endif; ?>
</div>

<?php require '../includes/footer.php'; ?>