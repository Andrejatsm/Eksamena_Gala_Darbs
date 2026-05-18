<?php
session_start();
require '../includes/db.php';
require_once __DIR__ . '/../includes/lang.php';
require_once __DIR__ . '/../includes/mail.php';

$error = '';
$success = '';
$token = trim($_GET['token'] ?? $_POST['token'] ?? '');

function ensurePasswordResetColumns(mysqli $conn): void {
    $tokenColumn = $conn->query("SHOW COLUMNS FROM accounts LIKE 'password_reset_token'");
    if ($tokenColumn && $tokenColumn->num_rows === 0) {
        $conn->query("ALTER TABLE accounts ADD COLUMN password_reset_token VARCHAR(64) NULL");
    }

    $expiresColumn = $conn->query("SHOW COLUMNS FROM accounts LIKE 'password_reset_expires_at'");
    if ($expiresColumn && $expiresColumn->num_rows === 0) {
        $conn->query("ALTER TABLE accounts ADD COLUMN password_reset_expires_at DATETIME NULL");
    }
}

ensurePasswordResetColumns($conn);

function tokenIsValid(mysqli $conn, string $token): bool {
    $stmt = $conn->prepare("SELECT id FROM accounts WHERE password_reset_token = ? AND password_reset_expires_at > NOW() LIMIT 1");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $valid = $result->num_rows > 0;
    $stmt->close();
    return $valid;
}

if ($token === '') {
    header('Location: forgot_password.php');
    exit();
}

$isTokenValid = tokenIsValid($conn, $token);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    if (!$isTokenValid) {
        $error = t('invalid_reset_token');
    } elseif ($password === '' || strlen($password) < 8) {
        $error = t('password_too_short');
    } elseif ($password !== $confirmPassword) {
        $error = t('passwords_not_match');
    } else {
        $stmt = $conn->prepare("UPDATE accounts SET password_hash = ?, password_reset_token = NULL, password_reset_expires_at = NULL WHERE password_reset_token = ? AND password_reset_expires_at > NOW() LIMIT 1");
        if ($stmt) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bind_param('ss', $passwordHash, $token);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                $success = t('password_reset_success');
            } else {
                $error = t('invalid_reset_token');
            }
            $stmt->close();
        } else {
            $error = t('invalid_reset_token');
        }
    }
}

require '../includes/header.php';
?>

<div class="auth-shell page-surface transition-colors duration-300">
    <div class="auth-card stack-md">
        <div>
            <h2 class="mt-2 text-center text-3xl font-extrabold text-gray-900 dark:text-white"><?php echo t('reset_password'); ?></h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400"><?php echo t('create_new_password'); ?></p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="bg-[#f1f9ff] dark:bg-[#095d7e]/20 border border-[#ccecee] dark:border-[#095d7e]/40 text-[#095d7e] dark:text-[#ccecee] px-4 py-3 rounded-lg text-sm text-center">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if(!empty($success)): ?>
            <div class="bg-[#e2fcd6] dark:bg-[#14967f]/20 border border-[#14967f]/30 text-[#14967f] dark:text-[#e2fcd6] px-4 py-3 rounded-lg text-sm text-center">
                <?php echo htmlspecialchars($success); ?>
            </div>
            <p class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">
                <a href="<?php echo htmlspecialchars($pathPrefix); ?>auth/login.php" class="font-medium text-primary hover:text-primaryHover transition"><?php echo t('back_to_login'); ?></a>
            </p>
        <?php else: ?>
            <?php if ($isTokenValid): ?>
                <form class="mt-8 stack-md" method="POST">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <div class="space-y-4">
                        <div>
                            <label for="password" class="field-label"><?php echo t('new_password'); ?></label>
                            <input id="password" name="password" type="password" required class="input-control" placeholder="••••••••">
                        </div>
                        <div>
                            <label for="confirm_password" class="field-label"><?php echo t('confirm_new_password'); ?></label>
                            <input id="confirm_password" name="confirm_password" type="password" required class="input-control" placeholder="••••••••">
                        </div>
                    </div>

                    <button type="submit" class="button-primary w-full"><?php echo t('reset_password'); ?></button>
                </form>
            <?php else: ?>
                <div class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
                    <?php echo t('invalid_reset_token'); ?>
                </div>
                <p class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">
                    <a href="<?php echo htmlspecialchars($pathPrefix); ?>auth/forgot_password.php" class="font-medium text-primary hover:text-primaryHover transition"><?php echo t('request_new_link'); ?></a>
                </p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require '../includes/footer.php'; ?>