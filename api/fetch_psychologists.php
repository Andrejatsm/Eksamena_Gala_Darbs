<?php
require '../includes/db.php';

$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 9;
$offset = ($page - 1) * $limit;
$basePath = rtrim($_GET['base'] ?? '', '/');  // path prefix for links (e.g. '..')
if ($basePath !== '') $basePath .= '/';

// Vienādojam attēlu ceļus no dažādiem datu avotiem, lai frontend vienmēr saņemtu izmantojamu src vērtību.
function normalize_psychologist_image_path(string $path): string {
    $normalized = trim($path);
    if ($normalized === '') {
        return '';
    }
    if (str_starts_with($normalized, 'assets/') || str_starts_with($normalized, 'http://') || str_starts_with($normalized, 'https://') || str_starts_with($normalized, 'uploads/')) {
        return $normalized;
    }
    if (str_starts_with($normalized, 'Images/')) {
        return 'assets/' . $normalized;
    }
    return $normalized;
}

// Filtru veidojam pa daļām, lai vienu un to pašu WHERE nosacījumu var izmantot gan skaitīšanai, gan pašam sarakstam.
$whereSQL = "WHERE a.role = 'psychologist' AND a.status = 'active' AND p.approved_at IS NOT NULL";
$searchLike = '%' . $search . '%';
$types = '';
$params = [];
if ($search !== '') {
    $whereSQL .= " AND (p.full_name LIKE ? OR p.specialization LIKE ?)";
    $types = 'ss';
    $params = [$searchLike, $searchLike];
}

$total_sql = "SELECT COUNT(*) AS count
              FROM psychologist_profiles p
              INNER JOIN accounts a ON a.id = p.account_id
              $whereSQL";
// Vispirms nosakām kopējo rezultātu skaitu, lai frontend var uzzīmēt pareizu lapošanu.
$total_stmt = $conn->prepare($total_sql);
if ($types !== '') {
    $total_stmt->bind_param($types, ...$params);
}
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_rows = (int)($total_result->fetch_assoc()['count'] ?? 0);
$total_stmt->close();
$total_pages = $total_rows > 0 ? (int)ceil($total_rows / $limit) : 0;

if ($total_pages > 0 && $page > $total_pages) {
    $page = $total_pages;
    $offset = ($page - 1) * $limit;
}

$sql = "SELECT
            p.account_id,
            p.full_name,
            p.specialization,
            p.experience_years,
            p.description,
            p.image_path,
            p.hourly_rate
        FROM psychologist_profiles p
        INNER JOIN accounts a ON a.id = p.account_id
        $whereSQL
        ORDER BY p.full_name ASC
        LIMIT ? OFFSET ?";
// Tajā pašā filtrā ielādējam tikai vienas lapas rezultātus, nevis visu sarakstu uzreiz.
$stmt = $conn->prepare($sql);
$pageTypes = $types . 'ii';
$pageParams = [...$params, $limit, $offset];
$stmt->bind_param($pageTypes, ...$pageParams);
$stmt->execute();
$result = $stmt->get_result();

$psihologi = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $row['image_path'] = normalize_psychologist_image_path((string)($row['image_path'] ?? ''));
        $psihologi[] = $row;
    }
}
$stmt->close();

if (empty($psihologi)) {
    echo '<div class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400 bg-white dark:bg-zinc-800 rounded-xl border border-dashed border-gray-300 dark:border-zinc-700">Netika atrasts neviens psihologs.</div>';
} else {
    foreach ($psihologi as $psi) {
        $fullName = trim((string)$psi['full_name']);
        $nameParts = preg_split('/\s+/', $fullName) ?: [];
        $initials = '';
        // Ja profilam nav attēla, veidojam vienkāršu fallback ar iniciāļiem, lai kartīte nepaliek tukša.
        foreach (array_slice($nameParts, 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }
        if ($initials === '') {
            $initials = 'P';
        }
        ?>
        <a href="<?php echo htmlspecialchars($basePath); ?>specialist/psychologist_profile.php?id=<?php echo (int)$psi['account_id']; ?>" class="group bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-700 overflow-hidden hover:shadow-xl transition duration-300 flex flex-col h-full transform hover:-translate-y-1 block">
            <div class="relative h-64 overflow-hidden">
                <?php if ($psi['image_path'] !== ''): ?>
                <img src="<?php echo htmlspecialchars($basePath . $psi['image_path']); ?>" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500" alt="Psihologs">
                <?php else: ?>
                <div class="w-full h-full bg-gradient-to-br from-primary/20 to-primary/5 flex items-center justify-center">
                    <div class="w-24 h-24 rounded-full bg-white/80 text-primary text-3xl font-bold flex items-center justify-center shadow-lg">
                        <?php echo htmlspecialchars($initials); ?>
                    </div>
                </div>
                <?php endif; ?>
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition duration-300"></div>
            </div>
            <div class="p-6 flex flex-col flex-grow">
                <div class="mb-4">
                    <h5 class="text-xl font-bold text-gray-900 dark:text-white mb-1"><?php echo htmlspecialchars($psi['full_name']); ?></h5>
                    <p class="text-sm text-primary font-medium uppercase tracking-wider"><?php echo htmlspecialchars($psi['specialization']); ?></p>
                </div>
                
                <div class="flex items-center text-gray-500 dark:text-gray-400 text-sm mb-6">
                    <span class="flex items-center bg-gray-100 dark:bg-zinc-700 px-3 py-1 rounded-full"><i class="fas fa-briefcase mr-2 text-primary"></i> <?php echo (int)$psi['experience_years']; ?> gadi</span>
                </div>

                <div class="mt-auto pt-4 border-t border-gray-100 dark:border-zinc-700 flex justify-between items-center">
                    <div>
                        <span class="text-xs text-gray-400 uppercase">Sesijas cena</span>
                        <div class="text-lg font-bold text-gray-900 dark:text-white">50 € / sesija</div>
                    </div>
                    
                    <div class="text-primary group-hover:text-primaryHover transition">
                        <i class="fas fa-arrow-right text-lg"></i>
                    </div>
                </div>
            </div>
        </a>
        <?php
    }
}

// Šis neredzamais bloks nodod lapošanas datus JavaScript kodam bez papildu JSON endpointa.
echo '<div id="pagination-data" class="u-hidden" data-total-pages="' . $total_pages . '" data-current-page="' . $page . '"></div>';
?>