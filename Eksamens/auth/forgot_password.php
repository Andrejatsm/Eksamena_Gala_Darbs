<?php
session_start();
require '../includes/db.php';
require_once __DIR__ . '/../includes/lang.php';
require_once __DIR__ . '/../includes/mail.php';

$error = '';
$success = '';
$email = '';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(strtolower($_POST['email'] ?? ''));

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = t('enter_valid_email');
    } else {
        ensurePasswordResetColumns($conn);

        $stmt = $conn->prepare("SELECT id, username FROM accounts WHERE email = ? AND status = 'active' LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', time() + 3600);
                $update = $conn->prepare("UPDATE accounts SET password_reset_token = ?, password_reset_expires_at = ? WHERE id = ?");
                if ($update) {
                    $update->bind_param('ssi', $token, $expiresAt, $user['id']);
                    $update->execute();
                    $update->close();
                }

                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $resetUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/reset_password.php?token=' . urlencode($token);
                $subject = t('reset_password_email_subject');
                $body = t('reset_password_email_body', $user['username'], $resetUrl);
                $from = get_email_config()['from_address'];
                $headers = [
                    'From: ' . get_email_config()['from_name'] . ' <' . $from . '>',
                    'Content-Type: text/plain; charset=UTF-8'
                ];
                send_email($email, $subject, $body, $headers);
            }

            $success = t('reset_password_email_sent');
        } else {
            $error = t('email_send_error');
        }
    }
}

require '../includes/header.php';
?>

<div class="auth-shell page-surface transition-colors duration-300">
    <div class="auth-card stack-md">
        <div>
            <h2 class="mt-2 text-center text-3xl font-extrabold text-gray-900 dark:text-white"><?php echo t('forgot_password'); ?></h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400"><?php echo t('enter_email_to_reset'); ?></p>
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
        <?php endif; ?>

        <form class="mt-8 stack-md" method="POST">
            <div class="space-y-4">
                <div>
                    <label for="email" class="field-label"><?php echo t('email'); ?></label>
                    <input id="email" name="email" type="email" required class="input-control" placeholder="<?php echo t('enter_email'); ?>" value="<?php echo htmlspecialchars($email); ?>">
                </div>
            </div>

            <button type="submit" class="button-primary w-full"><?php echo t('send_reset_link'); ?></button>
        </form>

        <p class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">
            <a href="<?php echo htmlspecialchars($pathPrefix); ?>auth/login.php" class="font-medium text-primary hover:text-primaryHover transition"><?php echo t('back_to_login'); ?></a>
        </p>
    </div>
</div>

<?php require '../includes/footer.php'; ?>