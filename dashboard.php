<?php
require 'db.php'; // Vispirms DB
require 'header.php'; // Tad Header (kurš palaiž sesiju)

// Pārbauda drošību
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

// Ielādējam psihologus
$sql = "SELECT id, vards_uzvards, specializacija, pieredze, cena_h, apraksts, attels FROM psychologists";
$result = $conn->query($sql);
$psihologi = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $row['attels'] = !empty($row['attels']) ? $row['attels'] : 'img/default_user.jpg';
        $psihologi[] = $row;
    }
}
?>

<div class="container mt-4 flex-grow-1">
    <h3 class="fw-bold mb-4">Pieejamie psihologi</h3>
    
    <div class="row">
        <?php if (empty($psihologi)): ?>
            <div class="col-12 text-center py-5">
                <div class="alert alert-info">
                    <h4>Pašlaik sistēmā nav pievienots neviens psihologs.</h4>
                    <p>Lūdzu, mēģiniet vēlāk vai sazinieties ar administrāciju.</p>
                </div>
            </div>
        <?php endif; ?>

        <?php foreach ($psihologi as $psi): ?>
        <div class="col-md-4 col-sm-6 d-flex align-items-stretch">
            <div class="card mb-4 shadow-sm w-100">
                <img src="<?php echo htmlspecialchars($psi['attels']); ?>" class="card-img-top psi-img" alt="Psihologs">
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
        <?php endforeach; ?>
    </div>
</div>

<div class="modal fade" id="psychologistModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="psychologistModalLabel">Psihologa informācija</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center" id="modalBodyContent">
                </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Aizvērt</button>
            </div>
        </div>
    </div>
</div>

<?php 
$conn->close();
require 'footer.php'; 
?>