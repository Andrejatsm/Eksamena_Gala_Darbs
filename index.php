<?php
$pageTitle = "Saprasts - Sākums";
require 'header.php';

$btn_link = (isset($_SESSION['account_id'], $_SESSION['role']) && $_SESSION['role'] === 'user') ? 'dashboard.php' : 'login.php';
$btn_text = (isset($_SESSION['account_id'], $_SESSION['role']) && $_SESSION['role'] === 'user') ? 'Doties uz sistēmu' : 'Atrodi savu psihologu';
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

    <!-- Featured Specialists Section -->
    <section class="py-24 px-6 bg-white dark:bg-zinc-900">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6">
                <div>
                    <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Mūsu speciālisti</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400">Labākie savas jomas eksperti, gatavi jums palīdzēt.</p>
                </div>
                <a href="dashboard.php" class="text-primary font-bold flex items-center gap-2 hover:gap-4 transition-all group">
                    Skatīt visus speciālistus
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php
                require 'db.php';
                
                // Fetch featured psychologists
                $stmt = $conn->prepare("
                    SELECT p.account_id, p.full_name, p.specialization, p.hourly_rate, p.experience_years,
                           COUNT(DISTINCT a.id) as total_appointments
                    FROM psychologist_profiles p
                    LEFT JOIN appointments a ON p.account_id = a.psychologist_account_id AND a.status = 'approved'
                    WHERE p.approved_at IS NOT NULL
                    GROUP BY p.account_id
                    ORDER BY total_appointments DESC, p.full_name ASC
                    LIMIT 3
                ");
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    while ($specialist = $result->fetch_assoc()) {
                        $initials = strtoupper(substr($specialist['full_name'], 0, 1));
                        $total_appointments = (int)$specialist['total_appointments'];
                        ?>
                        <div class="bg-gray-50 dark:bg-zinc-800 rounded-2xl overflow-hidden border border-gray-200 dark:border-zinc-700 hover:shadow-lg transition-shadow">
                            <!-- Image Placeholder -->
                            <div class="h-80 relative overflow-hidden bg-gradient-to-br from-primary/20 to-primary/5 flex items-center justify-center">
                                <div class="w-24 h-24 rounded-full bg-primary/20 flex items-center justify-center">
                                    <span class="text-4xl font-bold text-primary"><?php echo $initials; ?></span>
                                </div>
                            </div>
                            
                            <!-- Info -->
                            <div class="p-8">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($specialist['full_name']); ?></h3>
                                        <p class="text-primary text-sm font-semibold"><?php echo htmlspecialchars($specialist['specialization']); ?></p>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-yellow-400">★</span>
                                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300">4.9</span>
                                    </div>
                                </div>
                                
                                <p class="text-gray-600 dark:text-gray-400 text-sm mb-6">
                                    <?php echo $specialist['experience_years']; ?> gadu pieredze • <?php echo $total_appointments; ?> pacienti
                                </p>
                                
                                <a href="psychologist_profile.php?id=<?php echo (int)$specialist['account_id']; ?>" class="w-full py-3 bg-white dark:bg-zinc-700 text-primary dark:text-primary font-bold rounded-full hover:bg-primary hover:text-white dark:hover:text-white transition-colors border border-primary">
                                    Profila apskate
                                </a>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="py-20 bg-gradient-to-r from-primary to-primaryHover relative overflow-hidden px-6">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-white rounded-full mix-blend-multiply filter blur-3xl"></div>
            <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-white rounded-full mix-blend-multiply filter blur-3xl"></div>
        </div>
        
        <div class="max-w-4xl mx-auto relative z-10 text-center">
            <h2 class="text-4xl lg:text-5xl font-bold text-white mb-4">
                Vai tu gatavs sākt? 🌟
            </h2>
            <p class="text-xl text-white/90 mb-10 max-w-2xl mx-auto">
                Tūkstošiem cilvēku jau ir atraduši mieru un atbalstu. Jūsu kārta ir šodien.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="register.php" class="px-8 py-4 bg-white text-primary font-bold rounded-full hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg">
                    Reģistrēties bezmaksas
                </a>
                <a href="login.php" class="px-8 py-4 bg-white/20 text-white font-bold rounded-full border border-white hover:bg-white/30 transition-colors">
                    Pierakstīties
                </a>
            </div>
            
            <p class="text-sm text-white/80 mt-8">
                💳 Pašreiz ir mīļš periods - slēptā 50% atlaide uz pirmo konsultāciju!
            </p>
        </div>
    </section>

</main>

<?php require 'footer.php'; ?>
