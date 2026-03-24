<?php
$pageTitle = "Mana profila informācija";
require 'db.php';
require 'header.php';

if (!isset($_SESSION['account_id'])) {
    header("Location: login.php");
    exit();
}

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

// Get profile info
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

<div class="flex-grow max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Profila informācija</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2">Rediģējiet savo profila datus.</p>
    </div>

    <?php if(!empty($message)): ?>
        <div class="mb-6 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-600 dark:text-green-400 px-4 py-3 rounded-lg">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if(!empty($error)): ?>
        <div class="mb-6 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 px-4 py-3 rounded-lg">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-100 dark:border-zinc-700 p-6 space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lietotājvārds (nevar mainīt)</label>
            <input type="text" value="<?php echo htmlspecialchars($current['username']); ?>" disabled class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-gray-400 shadow-sm p-2.5 border transition bg-gray-100">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vārds</label>
            <input type="text" value="<?php echo htmlspecialchars($role === 'user' ? $profile['first_name'] : explode(' ', $profile['full_name'])[0]); ?>" disabled class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-gray-400 shadow-sm p-2.5 border transition bg-gray-100">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">E-pasts</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($current['email']); ?>" class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2.5 border transition">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Telefons</label>
            <input type="tel" name="phone" value="<?php echo htmlspecialchars($current['phone'] ?? ''); ?>" class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2.5 border transition">
        </div>

        <hr class="border-gray-200 dark:border-zinc-700 my-4">

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Senā parole (ja vēlaties mainīt paroli)</label>
            <input type="password" name="old_password" class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2.5 border transition">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jaunā parole (ja vēlaties mainīt)</label>
            <input type="password" name="new_password" class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2.5 border transition">
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Vismaz 1 lielais burts, 1 simbols.</p>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="flex-1 bg-primary hover:bg-primaryHover text-white px-6 py-3 rounded-lg transition font-medium">
                Saglabāt izmaiņas
            </button>
            <a href="dashboard.php" class="flex-1 text-center border border-gray-300 dark:border-zinc-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700 px-6 py-3 rounded-lg transition font-medium">
                Atcelt
            </a>
        </div>
    </form>
</div>

<?php require 'footer.php'; ?>
