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

function get_display_name(mysqli $conn, int $accountId, string $role, string $fallback): string {
    $queries = [
        'psychologist' => "SELECT full_name AS name FROM psychologist_profiles WHERE account_id = ?",
        'user' => "SELECT first_name AS name FROM user_profiles WHERE account_id = ?",
    ];

    if (!isset($queries[$role])) {
        return $fallback;
    }

    $stmt = $conn->prepare($queries[$role]);
    if (!$stmt) {
        return $fallback;
    }

    $stmt->bind_param("i", $accountId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return !empty($row['name']) ? (string)$row['name'] : $fallback;
}

$next = sanitize_next($_GET['next'] ?? $_POST['next'] ?? '');

// Ja jau ielogojies -> ej uz atbilstošo paneli
if (isset($_SESSION['account_id'], $_SESSION['role'])) {
    $role = $_SESSION['role'];
    if ($role === 'user' && $next !== '') {
        header("Location: " . $next);
    } elseif ($role === 'admin') {
        header("Location: ../admin/admin_dashboard.php");
    } elseif ($role === 'psychologist') {
        header("Location: ../specialist/specialist_dashboard.php");
    } else {
        header("Location: ../pages/dashboard.php");
    }
    exit();
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lietotajvards = trim($_POST['lietotajvards']);
    $parole = $_POST['parole'];

    $stmt = $conn->prepare("SELECT id, password_hash, role, status FROM accounts WHERE username = ?");
    $stmt->bind_param("s", $lietotajvards);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (!password_verify($parole, $row['password_hash'])) {
            $error = t('wrong_password');
        } elseif ($row['status'] !== 'active') {
            $error = match($row['status']) {
                'pending' => t('profile_not_approved'),
                'rejected' => t('application_rejected'),
                'disabled' => t('profile_deactivated'),
                default => t('login_not_allowed'),
            };
        } else {
            $accountId = (int)$row['id'];
            $role = $row['role'];

            $_SESSION['account_id'] = $accountId;
            $_SESSION['role'] = $role;

            $_SESSION['display_name'] = get_display_name($conn, $accountId, $role, $lietotajvards);

            if ($role === 'user' && $next !== '') {
                header("Location: " . $next);
            } elseif ($role === 'admin') {
                header("Location: ../admin/admin_dashboard.php");
            } elseif ($role === 'psychologist') {
                header("Location: ../specialist/specialist_dashboard.php");
            } else {
                header("Location: ../pages/dashboard.php");
            }
            exit();
        }
    } else {
        $error = t('user_not_found');
    }
    $stmt->close();
}

require '../includes/header.php';
?>

<div class="auth-shell page-surface transition-colors duration-300">
    <div class="auth-card stack-md">
        <div>
            <h2 class="mt-2 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
                <?php echo t('login_title'); ?>
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                <?php echo t('or'); ?> <a href="register.php" class="font-medium text-primary hover:text-primaryHover transition"><?php echo t('create_new_profile'); ?></a>
            </p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="bg-[#f1f9ff] dark:bg-[#095d7e]/20 border border-[#ccecee] dark:border-[#095d7e]/40 text-[#095d7e] dark:text-[#ccecee] px-4 py-3 rounded-lg text-sm text-center">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['success'])): ?>
            <div class="bg-[#e2fcd6] dark:bg-[#14967f]/20 border border-[#14967f]/30 text-[#14967f] dark:text-[#e2fcd6] px-4 py-3 rounded-lg text-sm text-center">
                <?php echo t('registration_success'); ?>
            </div>
        <?php endif; ?>

        <form class="mt-8 stack-md" method="POST">
            <?php if ($next !== ''): ?>
            <input type="hidden" name="next" value="<?php echo htmlspecialchars($next); ?>">
            <?php endif; ?>
            <div class="space-y-4">
                <div>
                    <label for="lietotajvards" class="field-label"><?php echo t('username'); ?></label>
                    <input id="lietotajvards" name="lietotajvards" type="text" required class="input-control" placeholder="<?php echo t('enter_username'); ?>">
                </div>
                <div>
                    <label for="parole" class="field-label"><?php echo t('password'); ?></label>
                    <input id="parole" name="parole" type="password" required class="input-control" placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="button-primary w-full">
                <?php echo t('login'); ?>
            </button>
        </form>
    </div>
</div>

<?php require '../includes/footer.php'; ?>