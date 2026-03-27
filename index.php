<?php
$pageTitle = "Saprasts - Sākums";
$pageStyles = ['home.css'];
require 'db.php';
require 'header.php';

$btn_link = (isset($_SESSION['account_id'], $_SESSION['role']) && $_SESSION['role'] === 'user') ? 'dashboard.php' : 'login.php';
$btn_text = (isset($_SESSION['account_id'], $_SESSION['role']) && $_SESSION['role'] === 'user') ? 'Doties uz sistēmu' : 'Atrodi savu psihologu';

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

<!-- Main Content -->
<main class="pt-0">
    
    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center px-6 overflow-hidden bg-white dark:bg-zinc-900">
        <div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-transparent dark:from-primary/10"></div>
        
        <div class="max-w-7xl mx-auto w-full grid lg:grid-cols-2 gap-12 items-center relative z-10">
            <!-- Left Content -->
            <div>
                <div class="inline-block px-4 py-2 rounded-full bg-primary/10 dark:bg-primary/20 text-primary text-xs font-bold tracking-widest uppercase mb-6 border border-primary/30">
                    Tava labsajūta ir prioritāte
                </div>
                
                <h1 class="text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white leading-tight mb-8">
                    Atrodi mieru un <span class="text-primary italic">profesionālu</span> atbalstu
                </h1>
                
                <p class="text-lg text-gray-600 dark:text-gray-400 max-w-lg mb-10 leading-relaxed">
                    Personalizēta pieeja jūsu garīgajai veselībai. Mūsu platforma savieno jūs ar sertificētiem psihologiem, lai palīdzētu pārvarēt dzīves izaicinājumus.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="<?php echo $btn_link; ?>" class="px-10 py-4 bg-gradient-to-r from-primary to-primaryHover text-white rounded-full font-bold text-lg flex items-center justify-center gap-2 hover:shadow-lg hover:shadow-primary/30 transition-all transform hover:scale-105">
                        <?php echo $btn_text; ?>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <a href="#how-it-works" class="px-10 py-4 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white rounded-full font-bold text-lg hover:bg-gray-200 dark:hover:bg-zinc-700 transition-colors border border-gray-300 dark:border-zinc-700">
                        Kā tas darbojas
                    </a>
                </div>
            </div>
            
            <!-- Right Image -->
            <div class="relative lg:h-[600px] hidden lg:block">
                <div class="absolute inset-0 bg-primary/10 rounded-2xl transform scale-105"></div>
                <img src="Images/psih8.png" alt="Profesionāla psihologa konsultācija" class="relative z-10 w-full h-full object-cover rounded-2xl shadow-2xl">
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-24 bg-gray-50 dark:bg-zinc-800/50 px-6">
        <div class="max-w-7xl mx-auto">
            <div class="mb-16">
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Kā tas darbojas</h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl">Trīs vienkārši soļi līdz labākai pašsajūtai un iekšējam mieram.</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="p-8 bg-white dark:bg-zinc-800 rounded-2xl hover:shadow-lg hover:-translate-y-1 transition-all border border-gray-100 dark:border-zinc-700">
                    <div class="w-14 h-14 rounded-xl bg-primary/20 dark:bg-primary/30 flex items-center justify-center mb-6">
                        <i class="fas fa-search text-2xl text-primary"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-2 block">1. Solis</span>
                    <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Izvēlies speciālistu</h3>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed">Pārlūko mūsu sertificēto speciālistu profilus un atrodi sev piemērotāko pēc specializācijas un pieredzes.</p>
                </div>
                
                <!-- Step 2 -->
                <div class="p-8 bg-white dark:bg-zinc-800 rounded-2xl hover:shadow-lg hover:-translate-y-1 transition-all border border-gray-100 dark:border-zinc-700">
                    <div class="w-14 h-14 rounded-xl bg-primary/20 dark:bg-primary/30 flex items-center justify-center mb-6">
                        <i class="fas fa-calendar text-2xl text-primary"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-2 block">2. Solis</span>
                    <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Piesaki vizīti</h3>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed">Izvēlies ērtāko laiku tiešsaistes vai klātienes konsultācijai un rezervē to dažu sekunžu laikā.</p>
                </div>
                
                <!-- Step 3 -->
                <div class="p-8 bg-white dark:bg-zinc-800 rounded-2xl hover:shadow-lg hover:-translate-y-1 transition-all border border-gray-100 dark:border-zinc-700">
                    <div class="w-14 h-14 rounded-xl bg-primary/20 dark:bg-primary/30 flex items-center justify-center mb-6">
                        <i class="fas fa-comments text-2xl text-primary"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-2 block">3. Solis</span>
                    <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Sāc sarunu</h3>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed">Saņem profesionālu atbalstu drošā un konfidenciālā vidē, lai kur tu atrastos.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Self Tests Section -->
    <section id="self-tests" class="home-section home-section-light">
        <div class="home-shell">
            <div class="home-section-head">
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Pildi pašnovērtējuma testus jau tagad</h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 max-w-3xl">Testus vari aizpildīt arī bez ielogošanās. Rezultātu varēsi atvērt pēc ielogošanās vai reģistrācijas.</p>
                <a href="tests.php" class="home-section-link home-section-link-button">Skatīt visus testus</a>
            </div>

            <div class="home-card-grid">
                <?php foreach ($tests as $test): ?>
                <div class="home-test-card">
                    <div class="home-test-icon">
                        <i class="fas fa-clipboard-check text-lg"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2"><?php echo htmlspecialchars($test['title']); ?></h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-5 flex-grow home-card-copy"><?php echo htmlspecialchars($test['description'] ?? 'Nav apraksta.'); ?></p>
                    <a href="test_view.php?test_id=<?php echo (int)$test['id']; ?>" class="button-primary home-card-action">
                        Pildīt testu
                    </a>
                </div>
                <?php endforeach; ?>

                <?php if (empty($tests)): ?>
                <div class="empty-card col-span-full">
                    <p class="text-gray-500 dark:text-gray-400">Pašlaik nav pieejamu testu.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Articles Section -->
    <section class="home-section home-section-articles">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-white rounded-full mix-blend-multiply filter blur-3xl"></div>
            <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-white rounded-full mix-blend-multiply filter blur-3xl"></div>
        </div>

        <div class="home-shell relative z-10">
            <div class="home-section-head home-section-head-dark">
                <h2 class="text-4xl font-bold text-white mb-3">Psihologu raksti un resursi</h2>
                <p class="text-lg text-white/90 max-w-3xl">Ieskaties mūsu speciālistu publicētajos rakstos par labsajūtu, attiecībām un ikdienas mentālo veselību.</p>
                <a href="published_articles.php" class="home-link-light">Skatīt visus rakstus</a>
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
                    <a href="published_articles.php?id=<?php echo (int)$article['id']; ?>" class="button-primary home-card-action mt-6">Lasīt rakstu</a>
                </article>
                <?php endforeach; ?>

                <?php if (empty($articles)): ?>
                <div class="empty-card col-span-full">
                    <p class="text-gray-700 dark:text-gray-300">Pašlaik nav publicētu rakstu.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

</main>

<?php require 'footer.php'; ?>
