<?php
require 'db.php';

// Iegūstam meklēšanas frāzi un lapas numuru
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 3; // Cik psihologus rādīt vienā lapā
$offset = ($page - 1) * $limit;

// SQL vaicājums ar filtru un pagināciju
$whereSQL = "";
if (!empty($search)) {
    $whereSQL = "WHERE vards_uzvards LIKE '%$search%' OR specializacija LIKE '%$search%'";
}

// 1. Iegūstam kopējo skaitu (priekš paginācijas pogām)
$total_sql = "SELECT COUNT(*) as count FROM psychologists $whereSQL";
$total_result = $conn->query($total_sql);
$total_rows = $total_result->fetch_assoc()['count'];
$total_pages = ceil($total_rows / $limit);

// 2. Iegūstam konkrētos datus
$sql = "SELECT * FROM psychologists $whereSQL LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

$psihologi = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $row['attels'] = !empty($row['attels']) ? $row['attels'] : 'img/default_user.jpg';
        $psihologi[] = $row;
    }
}

// Ģenerējam HTML atbildi
if (empty($psihologi)) {
    echo '<div class="col-12 text-center py-5"><div class="alert alert-info">Netika atrasts neviens psihologs.</div></div>';
} else {
    foreach ($psihologi as $psi) {
        ?>
        <div class="col-md-4 col-sm-6 d-flex align-items-stretch">
            <div class="card mb-4 shadow-sm w-100">
                <img src="<?php echo htmlspecialchars($psi['attels']); ?>" class="card-img-top" alt="Psihologs" style="height: 250px; object-fit: cover;">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title fw-bold"><?php echo htmlspecialchars($psi['vards_uzvards']); ?></h5>
                    <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($psi['specializacija']); ?></h6>
                    <p class="card-text mb-1">Pieredze: <strong><?php echo $psi['pieredze']; ?></strong> gadi</p>
                    <p class="card-text mb-3">Cena: <strong><?php echo number_format($psi['cena_h'], 2); ?></strong> EUR/h</p>

                    <button type="button" class="btn btn-outline-success mt-auto details-btn w-100" 
                            data-bs-toggle="modal" 
                            data-bs-target="#psychologistModal"
                            data-vards="<?php echo htmlspecialchars($psi['vards_uzvards']); ?>"
                            data-spec="<?php echo htmlspecialchars($psi['specializacija']); ?>"
                            data-pieredze="<?php echo $psi['pieredze']; ?>"
                            data-cena="<?php echo number_format($psi['cena_h'], 2); ?>"
                            data-apraksts="<?php echo htmlspecialchars($psi['apraksts']); ?>"
                            data-attels="<?php echo htmlspecialchars($psi['attels']); ?>">
                        Vairāk informācijas
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
}

// Paginācijas dati (paslēpti, lai JS tos nolasītu)
echo '<div id="pagination-data" style="display:none;" data-total-pages="' . $total_pages . '" data-current-page="' . $page . '"></div>';
?>