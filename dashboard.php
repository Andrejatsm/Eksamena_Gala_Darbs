<?php
require 'db.php';
require 'header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}
?>

<div class="container mt-4 flex-grow-1">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Pieejamie psihologi</h3>
        <input type="text" id="searchInput" class="form-control w-25" placeholder="Meklēt speciālistu...">
    </div>
    
    <div class="row" id="psychologistsContainer">
        <div class="text-center w-100 mt-5">
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center" id="paginationControls">
            </ul>
    </nav>
</div>

<div class="modal fade" id="psychologistModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="psychologistModalLabel">Psihologa informācija</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center" id="modalBodyContent"></div>
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