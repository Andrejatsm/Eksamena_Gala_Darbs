<?php
require 'db.php';

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 3; 
$offset = ($page - 1) * $limit;

$whereSQL = "";
if (!empty($search)) {
    $whereSQL = "WHERE vards_uzvards LIKE '%$search%' OR specializacija LIKE '%$search%'";
}

$total_sql = "SELECT COUNT(*) as count FROM psychologists $whereSQL";
$total_result = $conn->query($total_sql);
$total_rows = $total_result->fetch_assoc()['count'];
$total_pages = ceil($total_rows / $limit);

$sql = "SELECT * FROM psychologists $whereSQL LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

$psihologi = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $row['attels'] = !empty($row['attels']) ? $row['attels'] : 'img/default_user.jpg';
        $psihologi[] = $row;
    }
}

if (empty($psihologi)) {
    echo '<div class="col-span-full text-center py-10 text-gray-500 dark:text-gray-400">Netika atrasts neviens psihologs.</div>';
} else {
    foreach ($psihologi as $psi) {
        // Tailwind Kartīte
        ?>
        <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-700 overflow-hidden hover:shadow-xl transition duration-300 flex flex-col h-full">
            <div class="relative h-64 overflow-hidden">
                <img src="<?php echo htmlspecialchars($psi['attels']); ?>" class="w-full h-full object-cover transform hover:scale-105 transition duration-500" alt="Psihologs">
            </div>
            <div class="p-6 flex flex-col flex-grow">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h5 class="text-xl font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($psi['vards_uzvards']); ?></h5>
                        <p class="text-sm text-primary font-medium"><?php echo htmlspecialchars($psi['specializacija']); ?></p>
                    </div>
                </div>
                
                <div class="flex items-center text-gray-500 dark:text-gray-400 text-sm mb-4">
                    <span class="mr-3"><i class="fas fa-briefcase mr-1"></i> <?php echo $psi['pieredze']; ?> gadi</span>
                </div>

                <div class="mt-auto pt-4 border-t border-gray-100 dark:border-zinc-700 flex justify-between items-center">
                    <span class="text-lg font-bold text-gray-900 dark:text-white"><?php echo number_format($psi['cena_h'], 2); ?> €/h</span>
                    
                    <button type="button" class="details-btn px-4 py-2 bg-primary/10 text-primary hover:bg-primary hover:text-white rounded-lg transition font-medium text-sm" 
                            data-vards="<?php echo htmlspecialchars($psi['vards_uzvards']); ?>"
                            data-spec="<?php echo htmlspecialchars($psi['specializacija']); ?>"
                            data-pieredze="<?php echo $psi['pieredze']; ?>"
                            data-cena="<?php echo number_format($psi['cena_h'], 2); ?>"
                            data-apraksts="<?php echo htmlspecialchars($psi['apraksts']); ?>"
                            data-attels="<?php echo htmlspecialchars($psi['attels']); ?>">
                        Vairāk
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
}

echo '<div id="pagination-data" style="display:none;" data-total-pages="' . $total_pages . '" data-current-page="' . $page . '"></div>';
?>