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

$specialization_options = [];
if ($role === 'psychologist') {
    $specResult = $conn->query("SELECT name FROM psychologist_specializations WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
    if ($specResult) {
        while ($specRow = $specResult->fetch_assoc()) {
            $specialization_options[] = $specRow['name'];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['action'] ?? '') === 'delete_account') {
    $confirm_password = $_POST['confirm_password'] ?? '';
    $stmt = $conn->prepare("SELECT password_hash FROM accounts WHERE id = ?");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || !password_verify($confirm_password, $row['password_hash'])) {
        $error = "Nepareiza parole. Konts netika dzēsts.";
    } else {
        if ($role === 'psychologist') {
            $d = $conn->prepare("DELETE FROM psychologist_profiles WHERE account_id = ?");
            $d->bind_param("i", $account_id); $d->execute(); $d->close();
        } else {
            $d = $conn->prepare("DELETE FROM user_profiles WHERE account_id = ?");
            $d->bind_param("i", $account_id); $d->execute(); $d->close();
        }
        $d = $conn->prepare("DELETE FROM accounts WHERE id = ?");
        $d->bind_param("i", $account_id); $d->execute(); $d->close();
        session_destroy();
        header("Location: index.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['action'] ?? '') !== 'delete_account') {
    $new_email = trim($_POST['email'] ?? '');
    $new_phone = trim($_POST['phone'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $old_password = $_POST['old_password'] ?? '';
    $new_specialization = trim($_POST['specialization'] ?? '');
    $new_experience_years = min(50, max(0, (int)($_POST['experience_years'] ?? 0)));
    $new_description = trim($_POST['description'] ?? '');
    $remove_profile_image = isset($_POST['remove_profile_image']);

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

    $existingPsychProfile = null;
    if (empty($error) && $role === 'psychologist') {
        if ($new_specialization === '' || !in_array($new_specialization, $specialization_options, true)) {
            $error = "Lūdzu izvēlieties derīgu specializāciju no saraksta.";
        } else {
            $psychStmt = $conn->prepare("SELECT full_name, image_path FROM psychologist_profiles WHERE account_id = ? LIMIT 1");
            $psychStmt->bind_param("i", $account_id);
            $psychStmt->execute();
            $existingPsychProfile = $psychStmt->get_result()->fetch_assoc();
            $psychStmt->close();
            if (!$existingPsychProfile) {
                $error = "Neizdevās ielādēt psihologa profila datus.";
            }
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
        
        $accountUpdated = $stmt->execute();
        if (!$accountUpdated) {
            $error = "Kļūda atjauninot profilu!";
        }
        $stmt->close();

        if (empty($error) && $role === 'psychologist' && $existingPsychProfile) {
            $updatedImagePath = (string)($existingPsychProfile['image_path'] ?? '');

            if ($remove_profile_image) {
                $updatedImagePath = null;
            }

            if (isset($_FILES['profile_image']) && (int)($_FILES['profile_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                if ((int)$_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
                    $error = "Kļūda augšupielādējot profila attēlu.";
                } else {
                    $uploadDir = 'uploads/profile_images/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $originalName = (string)($_FILES['profile_image']['name'] ?? '');
                    $safeName = preg_replace('/[^A-Za-z0-9_\.-]/', '_', basename($originalName));
                    $safeName = $safeName ?: 'profile_image';
                    $targetPath = $uploadDir . time() . '_' . $safeName;
                    $ext = strtolower((string)pathinfo($targetPath, PATHINFO_EXTENSION));
                    $allowedImageTypes = ['jpg', 'jpeg', 'png', 'webp'];

                    if (!in_array($ext, $allowedImageTypes, true)) {
                        $error = "Atļautie profila attēla formāti ir: JPG, JPEG, PNG, WEBP.";
                    } elseif (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
                        $error = "Kļūda saglabājot profila attēlu.";
                    } else {
                        $updatedImagePath = $targetPath;
                    }
                }
            }

            if (empty($error)) {
                $profileStmt = $conn->prepare("UPDATE psychologist_profiles SET specialization = ?, experience_years = ?, description = ?, image_path = ? WHERE account_id = ?");
                $profileStmt->bind_param("sissi", $new_specialization, $new_experience_years, $new_description, $updatedImagePath, $account_id);
                if (!$profileStmt->execute()) {
                    $error = "Kļūda atjauninot psihologa profilu!";
                }
                $profileStmt->close();
            }
        }

        if (empty($error)) {
            $message = "Profils veiksmīgi atjaunināts!";
        }
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
    $stmt = $conn->prepare("SELECT full_name, specialization, experience_years, description, image_path FROM psychologist_profiles WHERE account_id = ?");
}
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();
?>

<div class="page-shell-wide page-surface">
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

    <form method="POST" enctype="multipart/form-data" class="form-card stack-md">
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

        <?php if ($role === 'psychologist'): ?>
        <hr class="border-gray-200 dark:border-zinc-700 my-4">

        <div>
            <label class="field-label">Specializācija</label>
            <select name="specialization" class="select-control">
                <option value="">Izvēlieties specializāciju</option>
                <?php foreach ($specialization_options as $spec): ?>
                <option value="<?php echo htmlspecialchars($spec); ?>" <?php echo (($profile['specialization'] ?? '') === $spec) ? 'selected' : ''; ?>><?php echo htmlspecialchars($spec); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="field-label">Pieredze (gadi)</label>
            <input type="number" name="experience_years" min="0" max="50" value="<?php echo (int)($profile['experience_years'] ?? 0); ?>" class="input-control">
        </div>

        <div>
            <label class="field-label">Apraksts</label>
            <textarea name="description" rows="4" class="textarea-control"><?php echo htmlspecialchars((string)($profile['description'] ?? '')); ?></textarea>
        </div>

        <div>
            <label class="field-label">Profila attēls (JPG, PNG, WEBP)</label>
            <?php if (!empty($profile['image_path'])): ?>
                <img src="<?php echo htmlspecialchars((string)$profile['image_path']); ?>" alt="Pašreizējais profila attēls" class="w-24 h-24 rounded-full object-cover border border-gray-200 dark:border-zinc-700 mb-3">
            <?php endif; ?>
            <input type="file" name="profile_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="input-control">
            <label class="mt-2 inline-flex items-center text-sm text-gray-600 dark:text-gray-400">
                <input type="checkbox" name="remove_profile_image" value="1" class="mr-2">
                Noņemt esošo profila attēlu
            </label>
        </div>
        <?php endif; ?>

        <hr class="border-gray-200 dark:border-zinc-700 my-4">

        <div>
            <label class="field-label">Vecā parole (ja vēlaties mainīt paroli)</label>
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

    <!-- Konta dzēšana -->
    <div class="form-card mt-6 border border-[#ccecee] dark:border-zinc-700">
        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Dzēst kontu</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">Dzēšot kontu, visi jūsu dati tiks neatgriezeniski izdzēsti. Šo darbību nevar atsaukt.</p>
        <button type="button" id="openDeleteAccountModalBtn" class="px-4 py-2 bg-[#095d7e] text-white rounded-lg hover:bg-[#074e6b] transition font-semibold">
            <i class="fas fa-trash mr-2"></i>Dzēst manu kontu
        </button>
    </div>
</div>

<!-- Konta dzēšanas apstiprinājuma modālis -->
<div id="deleteAccountModal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm"></div>
        <div class="relative bg-surface dark:bg-zinc-800 rounded-2xl border border-[#ccecee] dark:border-zinc-700 shadow-2xl w-full max-w-md">
            <div class="px-6 pt-6 pb-4">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Dzēst kontu?</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-5">Šī darbība ir neatgriezeniska. Visi jūsu dati, pieraksti un profila informācija tiks izdzēsti.</p>
                <form method="POST">
                    <input type="hidden" name="action" value="delete_account">
                    <label class="field-label">Ievadiet savu paroli, lai apstiprinātu</label>
                    <input type="password" name="confirm_password" required class="input-control mb-5" placeholder="Jūsu parole">
                    <div class="border-t border-[#ccecee] dark:border-zinc-700 -mx-6 px-6 pt-4 mt-2 flex justify-end gap-2 bg-[#f1f9ff] dark:bg-zinc-700/30 rounded-b-2xl">
                        <button type="button" id="cancelDeleteAccountModalBtn" class="px-4 py-2 bg-surface dark:bg-zinc-700 border border-[#ccecee] dark:border-zinc-600 text-[#095d7e] dark:text-[#ccecee] rounded-lg hover:bg-[#ccecee] dark:hover:bg-zinc-600 transition font-semibold">Atcelt</button>
                        <button type="submit" class="px-4 py-2 bg-[#095d7e] text-white rounded-lg hover:bg-[#074e6b] transition font-semibold">
                            <i class="fas fa-trash mr-2"></i>Jā, dzēst kontu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="assets/js/user_profile.js"></script>

<?php require 'footer.php'; ?>
