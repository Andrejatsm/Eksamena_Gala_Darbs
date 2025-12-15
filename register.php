<?php
require 'db.php';

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vards = trim($_POST['vards']);
    $uzvards = trim($_POST['uzvards']);
    $epasts = trim($_POST['epasts']);
    $lietotajvards = trim($_POST['lietotajvards']);
    
    // Paroli obligāti šifrējam
    $parole = password_hash($_POST['parole'], PASSWORD_DEFAULT);

    // Pārbaudām, vai lietotājvārds jau eksistē
    $check = $conn->prepare("SELECT id FROM users WHERE lietotajvards = ? OR epasts = ?");
    $check->bind_param("ss", $lietotajvards, $epasts);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "Lietotājvārds vai E-pasts jau eksistē!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (vards, uzvards, epasts, lietotajvards, parole) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $vards, $uzvards, $epasts, $lietotajvards, $parole);

        if ($stmt->execute()) {
            header("Location: login.php?success=1");
            exit();
        } else {
            $error = "Kļūda sistēmā: " . $conn->error;
        }
        $stmt->close();
    }
    $check->close();
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reģistrācija - Saprasts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Saprasts</a>
            <div class="d-flex align-items-center">
                <div class="navbar-nav">
                    <a class="nav-link" href="login.php">Ielogoties</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5 flex-grow-1 d-flex justify-content-center">
        <div class="card shadow-sm card-form card-form--register">
            <div class="card-body p-4">
                <h3 class="card-title text-center mb-4">Izveidot profilu</h3>

                <?php if(!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Vārds</label>
                            <input type="text" name="vards" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Uzvārds</label>
                            <input type="text" name="uzvards" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label">E-pasts</label>
                        <input type="email" name="epasts" class="form-control" required>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label">Lietotājvārds</label>
                        <input type="text" name="lietotajvards" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Parole</label>
                        <input type="password" name="parole" class="form-control" required>
                        <div class="form-text">Vismaz 1 lielais burts, 1 simbols.</div>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100">Reģistrēties</button>
                </form>
                <div class="text-center mt-3">
                    <small>Jau ir profils? <a href="login.php">Ielogoties</a></small>
                </div>
            </div>
        </div>
    </div>

    <footer class="mt-auto py-3 text-center">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date("Y"); ?> Saprasts. Visas tiesības aizsargātas.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>