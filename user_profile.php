<?php
session_start();
$pageTitle = "Mana profila informācija";
require 'database/db.php';

if (!isset($_SESSION['account_id'])) {
    header("Location: login.php");
    exit();
}

require 'header.php';

$account_id = (int)$_SESSION['account_id'];
$role = $_SESSION['role'];
$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_email = trim($_POST['email'] ?? '');
    $new_phone = trim($_POST['phone'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $old_password = $_POST['old_password'] ?? '';

    // Get current account info
    $stmt = $conn->prepare("SELECT password_hash FROM accounts WHERE id = ?");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $account = $result->fetch_assoc();
    $stmt->close();

    // Validate old password if changing password
    if (!empty($new_password) && !password_verify($old_password, $account['password_hash'])) {
        $error = "Senā parole nav pareiza!";
    } elseif (!empty($new_password)) {
        // Validate new password strength
        $hasUpper = preg_match('/[A-ZĀČĒĢĪĶĻŅŠŪŽ]/u', $new_password);
        $hasLower = preg_match('/[a-zāčēģīķļņšūž]/u', $new_password);
        $hasSymbol = preg_match('/[^A-Za-z0-9ĀČĒĢĪĶĻŅŠŪŽāčēģīķļņšūž]/u', $new_password);
        if (!$hasUpper || !$hasLower || !$hasSymbol) {
            $error = "Parolei jāietver vismaz 1 lielais burts, 1 mazais burts un 1 simbols.";
        }
    }

    if (empty($error)) {
        // Update account info
        if (!empty($new_password)) {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE accounts SET email = ?, phone = ?, password_hash = ? WHERE id = ?");
            $stmt->bind_param("sssi", $new_email, $new_phone, $password_hash, $account_id);
        } else {
            $stmt = $conn->prepare("UPDATE accounts SET email = ?, phone = ? WHERE id = ?");
            $stmt->bind_param("ssi", $new_email, $new_phone, $account_id);
        }
        
        if ($stmt->execute()) {
            $message = "Profils veiksmīgi atjaunināts!";
        } else {
            $error = "Kļūda atjauninot profilu!";
        }
        $stmt->close();
    }
}

// Get current info
$stmt = $conn->prepare("SELECT username, email, phone FROM accounts WHERE id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();
$current = $result->fetch_assoc();
$stmt->close();

// Iegūstam profila informāciju
if ($role === 'user') {
    $stmt = $conn->prepare("SELECT first_name, last_name FROM user_profiles WHERE account_id = ?");
} else {
    $stmt = $conn->prepare("SELECT full_name FROM psychologist_profiles WHERE account_id = ?");
}
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();
?>

<div class="page-shell-narrow page-surface">
    <div class="page-heading">
        <h1 class="page-title">Profila informācija</h1>
        <p class="page-subtitle">Rediģējiet savu profila informāciju.</p>
    </div>

    <?php if(!empty($message)): ?>
        <div class="alert-success">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if(!empty($error)): ?>
        <div class="alert-error">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="form-card stack-md">
        <div>
            <label class="field-label">Lietotājvārds (nevar mainīt)</label>
            <input type="text" value="<?php echo htmlspecialchars($current['username']); ?>" disabled class="input-control-disabled">
        </div>

        <div>
            <label class="field-label">Vārds</label>
            <input type="text" value="<?php echo htmlspecialchars($role === 'user' ? $profile['first_name'] : explode(' ', $profile['full_name'])[0]); ?>" disabled class="input-control-disabled">
        </div>

        <div>
            <label class="field-label">E-pasts</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($current['email']); ?>" class="input-control">
        </div>

        <div>
            <label class="field-label">Telefons</label>
            <input type="tel" name="phone" value="<?php echo htmlspecialchars($current['phone'] ?? ''); ?>" class="input-control">
        </div>

        <hr class="border-gray-200 dark:border-zinc-700 my-4">

        <div>
            <label class="field-label">Senā parole (ja vēlaties mainīt paroli)</label>
            <input type="password" name="old_password" class="input-control">
        </div>

        <div>
            <label class="field-label">Jaunā parole (ja vēlaties mainīt)</label>
            <input type="password" name="new_password" class="input-control">
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Vismaz 1 lielais burts, 1 simbols.</p>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="button-primary flex-1">
                Saglabāt izmaiņas
            </button>
            <a href="dashboard.php" class="button-secondary flex-1">
                Atcelt
            </a>
        </div>
    </form>
</div>

<?php require 'footer.php'; ?>
