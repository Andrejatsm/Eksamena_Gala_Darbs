<?php
require 'db.php';

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 3; 
$offset = ($page - 1) * $limit;

$whereSQL = "";
if (!empty($search)) {
    $whereSQL = "AND (p.full_name LIKE '%$search%' OR p.specialization LIKE '%$search%')";
}

$total_sql = "SELECT COUNT(*) as count
              FROM psychologist_profiles p
              INNER JOIN accounts a ON a.id = p.account_id
              WHERE a.role = 'psychologist' AND a.status = 'active' AND p.approved_at IS NOT NULL
              $whereSQL";
$total_result = $conn->query($total_sql);
$total_rows = $total_result->fetch_assoc()['count'];
$total_pages = ceil($total_rows / $limit);

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
        WHERE a.role = 'psychologist' AND a.status = 'active' AND p.approved_at IS NOT NULL
        $whereSQL
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

$psihologi = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $row['image_path'] = !empty($row['image_path']) ? $row['image_path'] : 'img/default_user.jpg';
        $psihologi[] = $row;
    }
}

if (empty($psihologi)) {
    echo '<div class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400 bg-white dark:bg-zinc-800 rounded-xl border border-dashed border-gray-300 dark:border-zinc-700">Netika atrasts neviens psihologs.</div>';
} else {
    foreach ($psihologi as $psi) {
        ?>
        <a href="psychologist_profile.php?id=<?php echo (int)$psi['account_id']; ?>" class="group bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-700 overflow-hidden hover:shadow-xl transition duration-300 flex flex-col h-full transform hover:-translate-y-1 block">
            <div class="relative h-64 overflow-hidden">
                <img src="<?php echo htmlspecialchars($psi['image_path']); ?>" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500" alt="Psihologs">
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

echo '<div id="pagination-data" class="u-hidden" data-total-pages="' . $total_pages . '" data-current-page="' . $page . '"></div>';
?>