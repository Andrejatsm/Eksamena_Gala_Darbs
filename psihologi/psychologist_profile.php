<?php
session_start();
$pageTitle = "Psihologa profils";
require '../database/db.php';

function normalize_psychologist_image_path(string $path): string {
    $normalized = trim($path);
    if ($normalized === '') {
        return '';
    }
    if (str_starts_with($normalized, '../') || str_starts_with($normalized, 'http://') || str_starts_with($normalized, 'https://') || str_starts_with($normalized, 'uploads/')) {
        return $normalized;
    }
    if (str_starts_with($normalized, 'assets/')) {
        return '../' . $normalized;
    }
    if (str_starts_with($normalized, 'Images/')) {
        return '../assets/' . $normalized;
    }
    return $normalized;
}

// Check if user is logged in
if (!isset($_SESSION['account_id'])) {
    header("Location: ../login.php");
    exit();
}

require '../header.php';

// Get psychologist ID from URL
$psychologist_id = intval($_GET['id'] ?? 0);

if ($psychologist_id === 0) {
    header("Location: ../dashboard.php");
    exit();
}

// Fetch psychologist profile and articles
$stmt = $conn->prepare("
    SELECT a.id, a.email, a.phone, p.full_name, p.specialization, p.experience_years, p.description, p.image_path, p.approved_at,
           (SELECT COUNT(*) FROM appointments WHERE psychologist_account_id = a.id AND status = 'approved') as total_appointments
    FROM accounts a
    JOIN psychologist_profiles p ON a.id = p.account_id
    WHERE a.id = ? AND p.approved_at IS NOT NULL
");
$stmt->bind_param("i", $psychologist_id);
$stmt->execute();
$result = $stmt->get_result();
$psychologist = $result->fetch_assoc();
$psychologist['image_path'] = normalize_psychologist_image_path((string)($psychologist['image_path'] ?? ''));

if (!$psychologist) {
    header("Location: ../dashboard.php");
    exit();
}

// Fetch psychologist's articles
$stmt = $conn->prepare("
    SELECT id, title, content, category, created_at
    FROM articles
    WHERE psychologist_account_id = ? AND is_published = 1
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $psychologist_id);
$stmt->execute();
$articles_result = $stmt->get_result();
$articles = $articles_result->fetch_all(MYSQLI_ASSOC);

// Fetch availability slots
$stmt = $conn->prepare("
    SELECT id, starts_at, ends_at, consultation_type, note
    FROM availability_slots
    WHERE psychologist_account_id = ? AND starts_at > NOW()
    ORDER BY starts_at ASC
    LIMIT 10
");
$stmt->bind_param("i", $psychologist_id);
$stmt->execute();
$slots_result = $stmt->get_result();
$available_slots = $slots_result->fetch_all(MYSQLI_ASSOC);
?>

<div class="min-h-screen page-surface dark:bg-zinc-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <!-- Back button -->
        <a href="../dashboard.php" class="inline-flex items-center text-primary hover:text-primaryHover mb-8 font-semibold">
            <i class="fas fa-arrow-left mr-2"></i> Atpakaļ uz speciālistiem
        </a>

        <!-- Psychologist Header -->
        <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-lg p-8 border border-gray-200 dark:border-zinc-700 mb-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Profile Info -->
                <div>
                            <?php if (!empty($psychologist['image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($psychologist['image_path']); ?>"
                             alt="<?php echo htmlspecialchars($psychologist['full_name']); ?>"
                             class="w-32 h-32 rounded-full object-cover mx-auto mb-4 shadow-lg border-4 border-primary/30">
                        <?php else: ?>
                        <div class="w-32 h-32 rounded-full bg-primary/20 flex items-center justify-center mx-auto mb-4 shadow-lg border-4 border-primary/30">
                            <div class="text-5xl font-bold text-primary">
                                <?php echo strtoupper(substr($psychologist['full_name'], 0, 1)); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-2" data-psychologist-name>
                        <?php echo htmlspecialchars($psychologist['full_name']); ?>
                    </h1>
                    <p class="text-center text-primary font-semibold mb-4"><?php echo htmlspecialchars($psychologist['specialization']); ?></p>
                </div>

                <!-- Key Info -->
                <div class="space-y-6">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Pieredze</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo $psychologist['experience_years']; ?> gadi</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Konsultācijas cena</p>
                        <p class="text-2xl font-bold text-primary">€50 / sesija</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Pieņemti pacienti</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo $psychologist['total_appointments'] ?? 0; ?></p>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white mb-2">Par mani</h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        <?php echo htmlspecialchars($psychologist['description'] ?? 'Nav apraksta'); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Articles Section -->
        <?php if (count($articles) > 0): ?>
        <div class="mb-12">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Raksti un resursi</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($articles as $article): ?>
                <div class="bg-white dark:bg-zinc-800 rounded-lg p-6 border border-gray-200 dark:border-zinc-700 hover:shadow-lg transition">
                    <div class="mb-3">
                        <?php if ($article['category']): ?>
                        <span class="inline-block bg-primary/10 text-primary px-3 py-1 rounded-full text-xs font-semibold mb-2">
                            <?php echo htmlspecialchars($article['category']); ?>
                        </span>
                        <?php endif; ?>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($article['title']); ?></h3>
                    </div>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        <?php echo substr(htmlspecialchars($article['content']), 0, 150); ?>...
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-500">
                        <?php echo date('d.m.Y', strtotime($article['created_at'])); ?>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Available Slots & Booking -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Available Slots -->
            <div class="lg:col-span-2">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Pieejamie laiki</h2>
                <?php if (count($available_slots) > 0): ?>
                <div class="space-y-3">
                    <?php foreach ($available_slots as $slot): ?>
                    <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 border border-gray-200 dark:border-zinc-700 flex items-center justify-between hover:shadow-md transition">
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                <?php echo date('d. F Y', strtotime($slot['starts_at'])); ?>
                                <span class="text-gray-600 dark:text-gray-400">
                                    <?php echo date('H:i', strtotime($slot['starts_at'])); ?> - <?php echo date('H:i', strtotime($slot['ends_at'])); ?>
                                </span>
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                <i class="fas fa-video mr-1"></i><?php echo ($slot['consultation_type'] ?? 'online') === 'online' ? 'Tiešsaistē' : 'Klātienē'; ?>
                            </p>
                            <?php if ($slot['note']): ?>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1"><?php echo htmlspecialchars($slot['note']); ?></p>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="slot-select-btn px-4 py-2 bg-primary text-white rounded-lg hover:bg-primaryHover transition font-semibold whitespace-nowrap ml-4"
                            data-slot-id="<?php echo (int)$slot['id']; ?>"
                            data-slot-time="<?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($slot['starts_at']))); ?>">
                            Izvēlēties
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6 text-center">
                    <p class="text-yellow-800 dark:text-yellow-300 font-semibold">
                        <i class="fas fa-calendar-times mr-2"></i>Pašlaik nav pieejamu laiku
                    </p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Booking Summary -->
            <div class="bg-gradient-to-br from-primary to-primaryHover rounded-2xl p-8 text-white shadow-xl sticky top-20 h-fit">
                <h3 class="text-2xl font-bold mb-6">Pierakstīties konsultācijai</h3>
                
                <div class="space-y-4 mb-8 pb-8 border-b border-white/20">
                    <div>
                        <p class="opacity-80 text-sm mb-1">Speciālists</p>
                        <p class="font-bold text-lg"><?php echo htmlspecialchars($psychologist['full_name']); ?></p>
                    </div>
                    <div>
                        <p class="opacity-80 text-sm mb-1">Specializācija</p>
                        <p class="font-bold"><?php echo htmlspecialchars($psychologist['specialization']); ?></p>
                    </div>
                    <div>
                        <p class="opacity-80 text-sm mb-1">Cena par konsultāciju</p>
                        <p class="text-3xl font-bold">€50</p>
                    </div>
                </div>

                <div id="bookingSummary" class="hidden mb-8">
                    <div>
                        <p class="opacity-80 text-sm mb-1">Izvēlētais laiks</p>
                        <p class="font-bold" id="selectedTime">-</p>
                    </div>
                </div>

                <button id="paymentBtn" type="button" data-psychologist-id="<?php echo (int)$psychologist_id; ?>"
                        class="w-full px-6 py-3 bg-white text-primary font-bold rounded-lg hover:bg-gray-100 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Turpināt uz maksājumu
                </button>
                
                <p class="text-xs opacity-75 mt-4 text-center">
                    ✓ Draudzīga maksājuma sistēma • 🔒 Droši
                </p>
            </div>
        </div>

    </div>
</div>

<script src="psychologist_profile.js"></script>

<?php require '../footer.php'; ?>
