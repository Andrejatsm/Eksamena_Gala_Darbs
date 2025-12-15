<?php
session_start();
require 'db.php';

// Ja lietotājs jau ir ielogojies, sūtam uz dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lietotajvards = trim($_POST['lietotajvards']);
    $parole = $_POST['parole'];

    // Izmantojam prepared statements drošībai
    $stmt = $conn->prepare("SELECT id, vards, parole FROM users WHERE lietotajvards = ?");
    $stmt->bind_param("s", $lietotajvards);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($parole, $row['parole'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['vards'] = $row['vards'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Nepareiza parole!";
        }
    } else {
        $error = "Lietotājs nav atrasts!";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ielogoties - Saprasts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Saprasts</a>
            <div class="d-flex align-items-center">
                <div class="navbar-nav">
                    <a class="nav-link" href="register.php">Reģistrēties</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5 flex-grow-1 d-flex justify-content-center">
        <div class="card shadow-sm card-form card-form--login">
            <div class="card-body p-4">
                <h3 class="card-title text-center mb-4">Ielogoties</h3>
                
                <?php if(!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success">Reģistrācija veiksmīga! Lūdzu, ielogojieties.</div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Lietotājvārds</label>
                        <input type="text" name="lietotajvards" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Parole</label>
                        <input type="password" name="parole" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Ielogoties</button>
                </form>
                <div class="text-center mt-3">
                    <small>Nav profila? <a href="register.php">Reģistrēties</a></small>
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