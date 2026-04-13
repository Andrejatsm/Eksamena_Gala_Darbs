<?php 
session_start();
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/lang.php';

\Stripe\Stripe::setApiKey('sk_test_51S7XQt3b1XY7a31CCbstqHPSNYoEFGXr5zcqQaaB5t25CYs3mFuYzOl1GB9jQ0Hzh7MJC8Gc1XneHycmqUYbqn5O00hVcvezVP');

function send_payment_confirmation_email(string $toEmail, string $userName, ?string $scheduledAt, string $consultationType): bool {
    if (!function_exists('mail')) {
        return false;
    }

    $subject = t('email_subject');
    $consultationLabel = $consultationType === 'online' ? t('email_consultation_online') : t('email_consultation_in_person');
    $timeText = $scheduledAt ? date('d.m.Y H:i', strtotime($scheduledAt)) : t('email_time_tbd');
    $safeName = trim($userName) !== '' ? $userName : 'Client';

    $body = t('email_greeting', $safeName) . "\n\n"
        . t('email_payment_ok') . "\n"
        . t('email_type_line', $consultationLabel) . "\n"
        . t('email_time_line', $timeText) . "\n\n"
        . t('email_contact_line') . "\n\n"
        . t('email_regards');

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/plain; charset=UTF-8',
        'From: Saprasts <noreply@saprasts.local>',
        'Reply-To: noreply@saprasts.local',
        'X-Mailer: PHP/' . phpversion(),
    ];

    return @mail($toEmail, $subject, $body, implode("\r\n", $headers));
}

// Pārbaudām, vai esam ielogojušies
if (!isset($_SESSION['account_id'], $_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

// Iegūstam datus
$psihologs_id = 0;
$user_id = (int)$_SESSION['account_id'];
$user_name = $_SESSION['display_name'] ?? ''; 
$stripe_session_id = trim((string)($_GET['session_id'] ?? ''));
$payment_verified = false;
$payment_error = '';
$session_owner_verified = false;

// --- Iegūstam E-PASTU no datubāzes ---
$user_email = ""; // Inicializējam mainīgo
$stmt_email = $conn->prepare("SELECT email FROM accounts WHERE id = ?");
$stmt_email->bind_param("i", $user_id);
$stmt_email->execute();
$res_email = $stmt_email->get_result();

if ($row_email = $res_email->fetch_assoc()) {
    $user_email = $row_email['email'];
}
$stmt_email->close();
// -------------------------------------

// Success lapā pierakstu veidojam tikai tad, ja Stripe sesija tiešām ir apmaksāta.
$check = null;
$scheduled_at = null;
$consultation_type = 'online';
$appointment_created = false;
$email_sent = null;

if ($stripe_session_id === '') {
    $payment_error = t('payment_session_missing');
} else {
    try {
        $checkout_session = \Stripe\Checkout\Session::retrieve($stripe_session_id);
        $metadata = (array)($checkout_session->metadata ?? []);

        if (($checkout_session->payment_status ?? '') !== 'paid') {
            throw new RuntimeException(t('payment_not_confirmed'));
        }

        $meta_user_id = (int)($metadata['user_account_id'] ?? 0);
        $client_ref_user_id = (int)($checkout_session->client_reference_id ?? 0);
        $session_match = isset($_SESSION['last_checkout_session_id'])
            && is_string($_SESSION['last_checkout_session_id'])
            && $_SESSION['last_checkout_session_id'] !== ''
            && hash_equals((string)$_SESSION['last_checkout_session_id'], $stripe_session_id);

        if ($meta_user_id > 0 && $meta_user_id === $user_id) {
            $session_owner_verified = true;
        } elseif ($client_ref_user_id > 0 && $client_ref_user_id === $user_id) {
            $session_owner_verified = true;
        } elseif ($session_match) {
            $session_owner_verified = true;
        }

        if (!$session_owner_verified) {
            throw new RuntimeException(t('session_mismatch'));
        }

        $psihologs_id = (int)($metadata['psychologist_account_id'] ?? 0);

        if ($psihologs_id <= 0) {
            $slot_from_meta = (int)($metadata['slot_id'] ?? 0);
            if ($slot_from_meta > 0) {
                $slotStmt = $conn->prepare('SELECT psychologist_account_id FROM availability_slots WHERE id = ? LIMIT 1');
                $slotStmt->bind_param('i', $slot_from_meta);
                $slotStmt->execute();
                $slotRow = $slotStmt->get_result()->fetch_assoc();
                $slotStmt->close();
                $psihologs_id = (int)($slotRow['psychologist_account_id'] ?? 0);
            }
        }

        if ($psihologs_id <= 0 && isset($_SESSION['last_paid_psychologist_account_id'])) {
            $psihologs_id = (int)$_SESSION['last_paid_psychologist_account_id'];
        }

        if ($psihologs_id <= 0) {
            throw new RuntimeException(t('psych_from_session_error'));
        }

        $consultation_raw = (string)($metadata['consultation_type'] ?? ($_SESSION['booking_consultation_type'] ?? 'online'));
        $consultation_type = in_array($consultation_raw, ['online', 'in_person'], true)
            ? $consultation_raw
            : 'online';

        $scheduled_raw = trim((string)($metadata['scheduled_at'] ?? ($_SESSION['booking_scheduled_at'] ?? '')));
        if ($scheduled_raw !== '') {
            $scheduled_ts = strtotime($scheduled_raw);
            if ($scheduled_ts !== false) {
                $scheduled_at = date('Y-m-d H:i:s', $scheduled_ts);
            }
        }

        $payment_verified = true;
        unset($_SESSION['last_checkout_session_id']);
    } catch (Throwable $e) {
        $payment_error = t('stripe_confirm_error') . $e->getMessage();
    }
}

if ($payment_verified && $psihologs_id > 0) {
    $checkSql = "SELECT id FROM appointments WHERE user_account_id = ? AND psychologist_account_id = ?";
    $checkTypes = "ii";
    $checkParams = [$user_id, $psihologs_id];

    if ($scheduled_at) {
        $checkSql .= " AND scheduled_at = ?";
        $checkTypes .= "s";
        $checkParams[] = $scheduled_at;
    }
    // Laika logs ļauj izvairīties no dubultiem ierakstiem uzreiz pēc viena un tā paša maksājuma.
    $checkSql .= " AND created_at > NOW() - INTERVAL 5 MINUTE";

    $stmt_check = $conn->prepare($checkSql);
    if ($checkTypes === "iis") {
        $stmt_check->bind_param($checkTypes, $checkParams[0], $checkParams[1], $checkParams[2]);
    } else {
        $stmt_check->bind_param($checkTypes, $checkParams[0], $checkParams[1]);
    }
    $stmt_check->execute();
    $check = $stmt_check->get_result();
    $stmt_check->close();
}

if ($payment_verified && $check && $check->num_rows == 0) {
    if ($scheduled_at) {
        // Ja slota laiks jau ir zināms, pierakstu uzreiz atzīmējam kā apstiprinātu konkrētam laikam.
        $stmt = $conn->prepare("INSERT INTO appointments (user_account_id, psychologist_account_id, scheduled_at, consultation_type, user_name_snapshot, user_email_snapshot, status) VALUES (?, ?, ?, ?, ?, ?, 'approved')");
        $stmt->bind_param("iissss", $user_id, $psihologs_id, $scheduled_at, $consultation_type, $user_name, $user_email);
    } else {
        // Ja konkrēts slots nav saglabāts, izveidojam vispārīgu pieteikumu, ko vēlāk var apstrādāt sistēmā.
        $stmt = $conn->prepare("INSERT INTO appointments (user_account_id, psychologist_account_id, consultation_type, user_name_snapshot, user_email_snapshot, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("iisss", $user_id, $psihologs_id, $consultation_type, $user_name, $user_email);
    }
    $appointment_created = $stmt->execute();
    $stmt->close();

    // Atzīmējam slotu kā rezervētu, lai citi lietotāji to vairs neredz profila lapā.
    $slot_id_from_meta = (int)($metadata['slot_id'] ?? $_SESSION['booking_slot_id'] ?? 0);
    if ($appointment_created && $slot_id_from_meta > 0) {
        $markStmt = $conn->prepare("UPDATE availability_slots SET is_booked = 1 WHERE id = ?");
        $markStmt->bind_param("i", $slot_id_from_meta);
        $markStmt->execute();
        $markStmt->close();
    }
    unset($_SESSION['booking_slot_id']);
}

if ($payment_verified && $appointment_created && filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
    $email_sent = send_payment_confirmation_email($user_email, $user_name, $scheduled_at, $consultation_type);
}

$pageTitle = t('payment_title');
require __DIR__ . '/../includes/header.php'; 
?>

<div class="auth-shell page-surface">
    <div class="result-card max-w-md w-full text-center border-2 <?php echo $payment_verified ? 'border-[#ccecee] dark:border-[#14967f]/30' : 'border-[#ccecee] dark:border-[#095d7e]/30'; ?>">
        
        <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-[#e2fcd6] dark:bg-[#14967f]/20 mb-6">
            <i class="fas fa-check text-4xl text-[#14967f] dark:text-[#e2fcd6]"></i>
        </div>

        <?php if ($payment_verified): ?>
        <h1 class="text-3xl font-extrabold text-[#14967f] dark:text-[#e2fcd6] mb-2"><?php echo t('thanks'); ?></h1>
        <?php else: ?>
        <h1 class="text-3xl font-extrabold text-[#095d7e] dark:text-[#ccecee] mb-2"><?php echo t('payment_confirm_failed'); ?></h1>
        <?php endif; ?>
        
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <?php echo $payment_verified ? t('payment_received') : t('booking_not_created'); ?>
        </h3>
        
        <?php if ($scheduled_at): ?>
        <div class="bg-[#f1f9ff] dark:bg-[#095d7e]/20 border border-[#ccecee] dark:border-[#095d7e]/40 rounded-lg p-4 mb-6">
            <h4 class="font-bold text-[#095d7e] dark:text-[#ccecee] mb-2"><?php echo t('consultation_confirmed'); ?></h4>
            <p class="text-[#095d7e] dark:text-[#ccecee]">
                <strong><?php echo t('time_label'); ?></strong> <?php echo date('d.m.Y H:i', strtotime($scheduled_at)); ?><br>
                <strong><?php echo t('type') . ':'; ?></strong> <?php echo $consultation_type === 'online' ? t('online') : t('in_person'); ?><br>
                <strong><?php echo t('duration_label'); ?></strong> <?php echo t('duration_1h'); ?>
            </p>
        </div>
        <?php endif; ?>
        
        <?php if ($payment_verified): ?>
        <p class="text-gray-600 dark:text-gray-300 mb-8">
            <?php echo t('booking_registered'); ?>
        </p>
        <?php if ($appointment_created && $email_sent === true): ?>
        <p class="text-sm text-[#14967f] dark:text-[#e2fcd6] mb-6">
            <?php echo t('email_sent_to'); ?><strong><?php echo htmlspecialchars($user_email); ?></strong>.
        </p>
        <?php elseif ($appointment_created && $email_sent === false): ?>
        <p class="text-sm text-[#095d7e] dark:text-[#ccecee] mb-6">
            <?php echo t('email_send_error'); ?>
        </p>
        <?php endif; ?>
        <?php else: ?>
        <p class="text-[#095d7e] dark:text-[#ccecee] mb-8">
            <?php echo htmlspecialchars($payment_error ?: t('payment_error_default')); ?>
        </p>
        <?php endif; ?>
        
        <a href="../pages/dashboard.php" class="button-primary w-full">
            <?php echo t('back_to_system'); ?>
        </a>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>