<?php 
$pageTitle = "Saprasts - Sākums";
require 'header.php'; 

// Poga ved uz sistēmu vai reģistrāciju
$btn_link = isset($_SESSION['user_id']) ? 'dashboard.php' : 'login.php'; // Nomainīju uz login, jo reģistrācija ir turpat
$btn_text = isset($_SESSION['user_id']) ? 'Doties uz sistēmu' : 'Sākt lietot';
?>

<div class="container mt-5 text-center flex-grow-1">
    <h1 class="display-4 fw-bold">"Saprasts" - Tava atbalsta platforma</h1>
    <p class="lead mt-3">Mēs savienojam cilvēkus ar sertificētiem psihologiem ātrai, drošai un ērtai palīdzībai.</p>
    <a href="<?php echo $btn_link; ?>" class="btn btn-lg btn-success mt-4 px-5"><?php echo $btn_text; ?></a>
</div>

<div class="container mt-5 mb-5">
    <div class="row text-center info-section">
        <div class="col-md-4 mb-4">
            <h4 class="fw-bold">Lietotāju Autorizācija</h4>
            <p>Droša **reģistrācijas un ielogošanās sistēma** ar šifrētām parolēm.</p>
        </div>
        <div class="col-md-4 mb-4">
            <h4 class="fw-bold">Psihologu meklēšana</h4>
            <p>Ērts **filtrēšanas rīks** palīdz atrast speciālistu tieši jums.</p>
        </div>
        <div class="col-md-4 mb-4">
            <h4 class="fw-bold">Maksājumu Integrācija</h4>
            <p>Droša **maksājumu apstrāde** caur Stripe.</p>
        </div>
        <div class="col-md-4 mb-4">
            <h4 class="fw-bold">Video Konsultācijas</h4>
            <p>Saņem atbalstu attālināti ar **video zvanu** funkciju.</p>
        </div>
        <div class="col-md-4 mb-4">
            <h4 class="fw-bold">AI Aģents</h4>
            <p>Mākslīgais intelekts palīdzēs noteikt piemērotāko speciālistu.</p>
        </div>
        <div class="col-md-4 mb-4">
            <h4 class="fw-bold">Konfidencialitāte</h4>
            <p>Pilnīga datu drošība un anonimitāte.</p>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>