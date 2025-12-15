<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Pārbauda, vai lietotājs ir ielogojies
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Saprasts - Psihologu pieteikumi'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="d-flex flex-column min-vh-100">
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark"> <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">Saprasts</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    
                    <li class="nav-item me-3 d-flex align-items-center">
                        <div class="theme-switch-wrapper">
                            <label class="theme-switch" for="theme-toggle">
                                <input type="checkbox" id="theme-toggle" />
                                <span class="slider round"></span>
                            </label>
                            <small class="ms-2 text-white" id="dark-mode-text">Dark Mode</small>
                        </div>
                    </li>

                    <?php if ($is_logged_in): ?>
                        <li class="nav-item me-3 text-white">
                            Sveiki, <strong><?php echo $_SESSION['vards']; ?></strong>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">Sistēma</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-light btn-sm ms-2" href="logout.php">Iziet</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Ielogoties</a>
                        </li>
                        <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>