<?php
session_start();
require '../includes/db.php';

function sanitize_next(string $next): string {
    $next = trim($next);
    if ($next === '' || str_contains($next, '://') || str_starts_with($next, '//') || str_contains($next, "\n") || str_contains($next, "\r")) {
        return '';
    }
    if (!preg_match('/^[a-zA-Z0-9_\-\/\.\?=&%#]+$/', $next)) {
        return '';
    }
    return $next;
}

$next = sanitize_next($_GET['next'] ?? $_POST['next'] ?? '');

$specialization_options = [];
$specResult = $conn->query("SELECT name FROM psychologist_specializations WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
if ($specResult) {
    while ($specRow = $specResult->fetch_assoc()) {
        $specialization_options[] = $specRow['name'];
    }
}


if (isset($_SESSION['account_id'])) {
    $redirect = match ($_SESSION['role'] ?? 'user') {
        'admin' => '../admin/admin_dashboard.php',
        'psychologist' => '../specialist/specialist_dashboard.php',
        default => '../pages/dashboard.php',
    };
    header("Location: " . $redirect);
    exit();
}

$error = "";
$role = 'user';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'] ?? 'user';
    $vards = trim($_POST['vards']);
    $uzvards = trim($_POST['uzvards']);
    $telefons = trim($_POST['telefons']);
    $epasts = trim($_POST['epasts']);
    $lietotajvards = trim($_POST['lietotajvards']);
    $rawPassword = $_POST['parole'];

    // Papildu lauki psihologam
    $specialization = trim($_POST['specialization'] ?? '');
    $experience_years = min(50, max(0, (int)($_POST['experience_years'] ?? 0)));
    $description = trim($_POST['description'] ?? '');

    if ($role === 'psychologist') {
        if ($specialization === '') {
            $error = "Lūdzu izvēlieties specializāciju.";
        } else {
            $specCheck = $conn->prepare("SELECT id FROM psychologist_specializations WHERE name = ? AND is_active = 1 LIMIT 1");
            $specCheck->bind_param("s", $specialization);
            $specCheck->execute();
            $specExists = $specCheck->get_result()->num_rows > 0;
            $specCheck->close();

            if (!$specExists) {
                $error = "Lūdzu izvēlieties derīgu specializāciju no saraksta.";
            }
        }
    }

    // Paroles validācija: vismaz 1 lielais, 1 mazais, 1 simbols
    $hasUpper = preg_match('/[A-ZĀČĒĢĪĶĻŅŠŪŽ]/u', $rawPassword);
    $hasLower = preg_match('/[a-zāčēģīķļņšūž]/u', $rawPassword);
    $hasSymbol = preg_match('/[^A-Za-z0-9ĀČĒĢĪĶĻŅŠŪŽāčēģīķļņšūž]/u', $rawPassword);
    if (!$hasUpper || !$hasLower || !$hasSymbol) {
        $error = "Parolei jāietver vismaz 1 lielais burts, 1 mazais burts un 1 simbols.";
    } else {
        $parole = password_hash($rawPassword, PASSWORD_DEFAULT);
    }

    $certificate_path = null;
    $profile_image_path = null;
    if ($role === 'psychologist' && empty($error)) {
        if (isset($_FILES['certificate']) && $_FILES['certificate']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/certificates/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = time() . '_' . basename($_FILES['certificate']['name']);
            $targetFilePath = $uploadDir . $fileName;
            
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
            $allowedTypes = array('pdf', 'jpg', 'jpeg', 'png');
            
            if (in_array($fileType, $allowedTypes)) {
                if (move_uploaded_file($_FILES['certificate']['tmp_name'], $targetFilePath)) {
                    $certificate_path = $targetFilePath;
                } else {
                    $error = "Kļūda augšupielādējot sertifikātu.";
                }
            } else {
                $error = "Atļautie sertifikāta formāti ir: PDF, JPG, JPEG, PNG.";
            }
        } else {
            $error = "Sertifikāta augšupielāde ir obligāta psihologiem.";
        }
    }

    if ($role === 'psychologist' && empty($error) && isset($_FILES['profile_image']) && (int)($_FILES['profile_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        if ((int)$_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
            $error = "Kļūda augšupielādējot profila attēlu.";
        } else {
            $imageUploadDir = 'uploads/profile_images/';
            if (!is_dir($imageUploadDir)) {
                mkdir($imageUploadDir, 0777, true);
            }

            $originalName = (string)($_FILES['profile_image']['name'] ?? '');
            $safeName = preg_replace('/[^A-Za-z0-9_\.-]/', '_', basename($originalName));
            $safeName = $safeName ?: 'profile_image';
            $imageFileName = time() . '_' . $safeName;
            $imageTargetPath = $imageUploadDir . $imageFileName;
            $imageExt = strtolower((string)pathinfo($imageTargetPath, PATHINFO_EXTENSION));
            $allowedImageTypes = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($imageExt, $allowedImageTypes, true)) {
                $error = "Atļautie profila attēla formāti ir: JPG, JPEG, PNG, WEBP.";
            } elseif (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $imageTargetPath)) {
                $error = "Kļūda saglabājot profila attēlu.";
            } else {
                $profile_image_path = $imageTargetPath;
            }
        }
    }

    if (empty($error)) {
        // Pārbaudām, vai lietotājvārds vai e-pasts jau eksistē
        $check = $conn->prepare("SELECT id FROM accounts WHERE username = ? OR email = ?");
        $check->bind_param("ss", $lietotajvards, $epasts);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Lietotājvārds vai E-pasts jau eksistē!";
        } else {
            $conn->begin_transaction();
            try {
                $status = ($role === 'psychologist') ? 'pending' : 'active';
                $stmt = $conn->prepare("INSERT INTO accounts (username, email, phone, password_hash, role, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $lietotajvards, $epasts, $telefons, $parole, $role, $status);

                if (!$stmt->execute()) {
                    throw new Exception($conn->error);
                }
                $accountId = (int)$conn->insert_id;
                $stmt->close();

                if ($role === 'user') {
                    $stmt2 = $conn->prepare("INSERT INTO user_profiles (account_id, first_name, last_name) VALUES (?, ?, ?)");
                    $stmt2->bind_param("iss", $accountId, $vards, $uzvards);
                } else {
                    $full_name = $vards . ' ' . $uzvards;
                    $session_price = 50.00;
                    $stmt2 = $conn->prepare("INSERT INTO psychologist_profiles (account_id, full_name, specialization, experience_years, description, hourly_rate, certificate_path, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt2->bind_param("issisdss", $accountId, $full_name, $specialization, $experience_years, $description, $session_price, $certificate_path, $profile_image_path);
                }
                if (!$stmt2->execute()) {
                    throw new Exception($conn->error);
                }
                $stmt2->close();

                $conn->commit();
                if ($role === 'psychologist') {
                    $redirect = "login.php?message=Pieteikums iesniegts. Administrators pārbaudīs jūsu kvalifikāciju.";
                    if ($next !== '') {
                        $redirect .= "&next=" . rawurlencode($next);
                    }
                    header("Location: " . $redirect);
                } else {
                    $redirect = "login.php?success=1";
                    if ($next !== '') {
                        $redirect .= "&next=" . rawurlencode($next);
                    }
                    header("Location: " . $redirect);
                }
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Kļūda sistēmā: " . $e->getMessage();
            }
        }
        $check->close();
    }
}

require '../includes/header.php';
?>

<div class="auth-shell page-surface">
    <div class="auth-card stack-md">
        <div>
            <h2 class="mt-2 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
                Izveidot profilu
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                Jau ir profils? <a href="login.php<?php echo $next !== '' ? '?next=' . rawurlencode($next) : ''; ?>" class="font-medium text-primary hover:text-primaryHover transition">Ielogoties</a>
            </p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="bg-[#f1f9ff] dark:bg-[#095d7e]/20 border border-[#ccecee] dark:border-[#095d7e]/40 text-[#095d7e] dark:text-[#ccecee] px-4 py-3 rounded-lg text-sm text-center">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form class="mt-8 stack-md" method="POST" enctype="multipart/form-data">
            <?php if ($next !== ''): ?>
            <input type="hidden" name="next" value="<?php echo htmlspecialchars($next); ?>">
            <?php endif; ?>
            <div class="space-y-4">
                <!-- Lomas izvēle -->
                <div>
                    <label class="field-label mb-2">Reģistrēties kā</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="role" value="user" <?php echo $role === 'user' ? 'checked' : ''; ?> class="text-primary focus:ring-primary">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Lietotājs (meklēt speciālistus)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="role" value="psychologist" <?php echo $role === 'psychologist' ? 'checked' : ''; ?> class="text-primary focus:ring-primary">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Psihologs (piedāvāt pakalpojumus)</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Vārds</label>
                        <input type="text" name="vards" required class="input-control">
                    </div>
                    <div>
                        <label class="field-label">Uzvārds</label>
                        <input type="text" name="uzvards" required class="input-control">
                    </div>
                </div>

                <div>
                    <label class="field-label">Telefons</label>
                    <input type="text" name="telefons" required class="input-control" placeholder="+371...">
                </div>

                <div>
                    <label class="field-label">E-pasts</label>
                    <input type="email" name="epasts" required class="input-control">
                </div>

                <div>
                    <label class="field-label">Lietotājvārds</label>
                    <input type="text" name="lietotajvards" required class="input-control">
                </div>

                <div>
                    <label class="field-label">Parole</label>
                    <input type="password" name="parole" required class="input-control">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Vismaz 1 lielais burts, 1 simbols.</p>
                </div>

                <!-- Psihologa specifiskie lauki -->
                <div id="psychologist-fields" class="<?php echo $role === 'psychologist' ? '' : 'hidden '; ?>space-y-4">
                    <div>
                        <label class="field-label">Specializācija</label>
                        <select name="specialization" class="select-control">
                            <option value="">Izvēlieties specializāciju</option>
                            <?php foreach ($specialization_options as $spec): ?>
                                <option value="<?php echo htmlspecialchars($spec); ?>" <?php echo (($specialization ?? '') === $spec) ? 'selected' : ''; ?>><?php echo htmlspecialchars($spec); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Pieredze (gadi)</label>
                        <input type="number" name="experience_years" min="0" max="50" class="input-control">
                    </div>
                    <div>
                        <label class="field-label">Apraksts</label>
                        <textarea name="description" rows="3" class="textarea-control" placeholder="Īss apraksts par sevi un pakalpojumiem"></textarea>
                    </div>
                    <div>
                        <label class="field-label">Sertifikāts (obligāts: PDF, JPG, PNG)</label>
                        <input type="file" name="certificate" accept=".pdf,.jpg,.jpeg,.png" class="input-control">
                    </div>
                    <div>
                        <label class="field-label">Profila attēls (neobligāts: JPG, PNG, WEBP)</label>
                        <input type="file" name="profile_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="input-control">
                    </div>
                </div>
            </div>

            <button type="submit" class="button-primary w-full">
                Reģistrēties
            </button>
        </form>
    </div>
</div>

<script src="../assets/js/register.js"></script>

<?php require '../includes/footer.php'; ?>