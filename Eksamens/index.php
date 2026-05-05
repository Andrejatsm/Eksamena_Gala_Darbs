<?php
require_once __DIR__ . '/includes/lang.php';
$pageTitle = t('site_title');
$pageStyles = ['home.css'];
require 'includes/db.php';
require 'includes/header.php';

$btn_link = (isset($_SESSION['account_id'], $_SESSION['role']) && $_SESSION['role'] === 'user') ? $pathPrefix . 'pages/dashboard.php' : $pathPrefix . 'auth/login.php';
$btn_text = (isset($_SESSION['account_id'], $_SESSION['role']) && $_SESSION['role'] === 'user') ? t('go_to_system') : t('find_psychologist');

$tests = [];
$tests_result = $conn->query("SELECT id, title, description FROM tests WHERE status = 'published' ORDER BY created_at DESC LIMIT 3");
if ($tests_result) {
    while ($row = $tests_result->fetch_assoc()) {
        $tests[] = $row;
    }
}

$articles = [];
$articles_result = $conn->query(
    "SELECT a.id, a.title, a.content, a.category, a.created_at, p.full_name
     FROM articles a
     JOIN psychologist_profiles p ON a.psychologist_account_id = p.account_id
     WHERE a.is_published = 1
     ORDER BY a.created_at DESC
     LIMIT 3"
);
if ($articles_result) {
    while ($row = $articles_result->fetch_assoc()) {
        $articles[] = $row;
    }
}
?>

<!-- Galvenais saturs -->
<main class="pt-0">
    
    <!-- Ievada sadaļa -->
    <section class="relative min-h-screen flex items-center px-6 overflow-hidden bg-surface dark:bg-zinc-900">
        <div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-transparent dark:from-primary/10"></div>
        
        <div class="max-w-7xl mx-auto w-full grid lg:grid-cols-2 gap-12 items-center relative z-10">
            <!-- Kreisās puses saturs -->
            <div>
                <div class="inline-block px-4 py-2 rounded-full bg-primary/10 dark:bg-primary/20 text-primary text-xs font-bold tracking-widest uppercase mb-6 border border-primary/30">
                    <?php echo t('hero_badge'); ?>
                </div>
                
                <h1 class="text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white leading-tight mb-8">
                    <?php echo t('hero_heading'); ?>
                </h1>
                
                <p class="text-lg text-gray-600 dark:text-gray-400 max-w-lg mb-10 leading-relaxed">
                    <?php echo t('hero_text'); ?>
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="<?php echo $btn_link; ?>" class="px-10 py-4 bg-gradient-to-r from-primary to-primaryHover text-white rounded-full font-bold text-lg flex items-center justify-center gap-2 hover:shadow-lg hover:shadow-primary/30 transition-all transform hover:scale-105">
                        <?php echo $btn_text; ?>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <a href="#how-it-works" class="px-10 py-4 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white rounded-full font-bold text-lg hover:bg-gray-200 dark:hover:bg-zinc-700 transition-colors border border-gray-300 dark:border-zinc-700">
                        <?php echo t('how_it_works'); ?>
                    </a>
                </div>
            </div>
            
            <!-- Labās puses attēls -->
            <div class="relative lg:h-[600px] hidden lg:block">
                <div class="absolute inset-0 bg-primary/10 rounded-2xl transform scale-105"></div>
                <img src="assets/Images/psih8.png" alt="Profesionāla psihologa konsultācija" class="relative z-10 w-full h-full object-cover rounded-2xl shadow-2xl">
            </div>
        </div>
    </section>

<!-- Kā tas darbojas sadaļa -->
    <section id="how-it-works" class="home-section home-section-light">
        <div class="home-shell">
            <div class="home-section-head">
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4"><?php echo t('how_it_works'); ?></h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 max-w-3xl"><?php echo t('three_steps_subtitle'); ?></p>
            </div>
            
            <div class="home-card-grid">
                <!-- 1. solis -->
                <div class="home-test-carda">
                    <div class="home-test-icona">
                        <i class="fas fa-search text-lg"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-2 block">1. <?php echo t('step'); ?></span>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2"><?php echo t('step1_title'); ?></h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-5 flex-grow home-card-copy"><?php echo t('step1_text'); ?></p>
                </div>
                
                <!-- 2. solis -->
                <div class="home-test-carda">
                    <div class="home-test-icona">
                        <i class="fas fa-calendar text-lg"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-2 block">2. <?php echo t('step'); ?></span>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2"><?php echo t('step2_title'); ?></h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-5 flex-grow home-card-copy"><?php echo t('step2_text'); ?></p>
                </div>
                
                <!-- 3. solis -->
                <div class="home-test-carda">
                    <div class="home-test-icona">
                        <i class="fas fa-comments text-lg"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-2 block">3. <?php echo t('step'); ?></span>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2"><?php echo t('step3_title'); ?></h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-5 flex-grow home-card-copy"><?php echo t('step3_text'); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pašnovērtējuma testu sadaļa -->
    <section id="self-tests" class="home-section home-section-light">
        <div class="home-shell">
            <div class="home-section-head">
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4"><?php echo t('tests_section_title'); ?></h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 max-w-3xl"><?php echo t('tests_section_subtitle'); ?></p>
                <a href="tests/tests.php" class="home-section-link home-section-link-button"><?php echo t('view_all_tests'); ?></a>
            </div>

            <div class="home-card-grid">
                <?php foreach ($tests as $test): ?>
                <div class="home-test-card">
                    <div class="home-test-icon">
                        <i class="fas fa-clipboard-check text-lg"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2"><?php echo htmlspecialchars($test['title']); ?></h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-5 flex-grow home-card-copy"><?php echo htmlspecialchars($test['description'] ?? 'Nav apraksta.'); ?></p>
                    <a href="tests/test_view.php?test_id=<?php echo (int)$test['id']; ?>" class="button-primary home-card-action">
                        <?php echo t('take_test'); ?>
                    </a>
                </div>
                <?php endforeach; ?>

                <?php if (empty($tests)): ?>
                <div class="empty-card col-span-full">
                    <p class="text-gray-500 dark:text-gray-400"><?php echo t('no_tests_available'); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Rakstu sadaļa -->
    <section class="home-section home-section-articles">

        <div class="home-shell relative z-10">
            <div class="home-section-head home-section-head-dark">
                <h2 class="text-4xl font-bold text-white mb-3"><?php echo t('articles_section_title'); ?></h2>
                <p class="text-lg text-white/90 max-w-3xl"><?php echo t('articles_section_subtitle'); ?></p>
                <a href="pages/published_articles.php" class="home-link-light"><?php echo t('view_all_articles'); ?></a>
            </div>

            <div class="home-card-grid">
                <?php foreach ($articles as $article): ?>
                <article class="home-article-card">
                    <div class="mb-3">
                        <?php if (!empty($article['category'])): ?>
                        <span class="home-article-tag">
                            <?php echo htmlspecialchars($article['category']); ?>
                        </span>
                        <?php endif; ?>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white leading-snug"><?php echo htmlspecialchars($article['title']); ?></h3>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                        <i class="fas fa-user mr-1"></i><?php echo htmlspecialchars($article['full_name']); ?>
                        <span class="mx-2">•</span>
                        <?php echo date('d.m.Y', strtotime($article['created_at'])); ?>
                    </p>
                    <p class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed">
                        <?php echo htmlspecialchars(mb_substr($article['content'], 0, 170)); ?>...
                    </p>
                    <a href="pages/published_articles.php?id=<?php echo (int)$article['id']; ?>" class="button-primary home-card-action mt-6"><?php echo t('read_article'); ?></a>
                </article>
                <?php endforeach; ?>

                <?php if (empty($articles)): ?>
                <div class="empty-card col-span-full">
                    <p class="text-gray-700 dark:text-gray-300"><?php echo t('no_tests_available'); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

</main>

<?php require 'includes/footer.php'; ?>
