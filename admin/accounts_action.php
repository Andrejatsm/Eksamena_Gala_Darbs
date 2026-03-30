<?php
session_start();
require '../database/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['account_id'], $_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Piekļuve liegta.']);
    exit();
}

$countQuery = static function (mysqli $conn, string $query): int {
    $result = $conn->query($query);
    if (!$result) {
        return 0;
    }
    $row = $result->fetch_assoc();
    return (int)($row['count'] ?? 0);
};

$buildStats = static function (mysqli $conn) use ($countQuery): array {
    return [
        'users' => $countQuery($conn, "SELECT COUNT(*) AS count FROM accounts WHERE role = 'user' AND status = 'active'"),
        'psychologists' => $countQuery($conn, "SELECT COUNT(*) AS count FROM accounts WHERE role = 'psychologist' AND status = 'active'"),
        'pendingPsychologists' => $countQuery($conn, "SELECT COUNT(*) AS count FROM accounts WHERE role = 'psychologist' AND status = 'pending'"),
        'appointments' => $countQuery($conn, "SELECT COUNT(*) AS count FROM appointments WHERE status IN ('pending', 'approved')"),
        'articles' => $countQuery($conn, "SELECT COUNT(*) AS count FROM articles WHERE is_published = 0"),
        'tests' => $countQuery($conn, "SELECT COUNT(*) AS count FROM tests WHERE status = 'published'"),
    ];
};

$hasLinkedAppointments = static function (mysqli $conn, int $accountId, string $role) use ($countQuery): bool {
    if ($role === 'psychologist') {
        return $countQuery($conn, "SELECT COUNT(*) AS count FROM appointments WHERE psychologist_account_id = " . $accountId) > 0;
    }

    if ($role === 'user') {
        return $countQuery($conn, "SELECT COUNT(*) AS count FROM appointments WHERE user_account_id = " . $accountId) > 0;
    }

    return false;
};

$action = (string)($_POST['action'] ?? '');
$accountId = (int)($_POST['account_id'] ?? 0);

if ($accountId <= 0 || $action === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Trūkst darbības datu.']);
    exit();
}

try {
    if ($action === 'approve_psych') {
        $conn->begin_transaction();

        $stmt = $conn->prepare("UPDATE psychologist_profiles SET approved_at = NOW() WHERE account_id = ?");
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE accounts SET status = 'active' WHERE id = ? AND role = 'psychologist'");
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $updated = $stmt->affected_rows > 0;
        $stmt->close();

        if (!$updated) {
            throw new RuntimeException('Neizdevās apstiprināt psihologa profilu.');
        }

        $conn->commit();
        $message = 'Psihologs apstiprināts sekmīgi!';
    } elseif ($action === 'reject_psych') {
        $stmt = $conn->prepare("UPDATE accounts SET status = 'rejected' WHERE id = ? AND role = 'psychologist'");
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $updated = $stmt->affected_rows > 0;
        $stmt->close();

        if (!$updated) {
            throw new RuntimeException('Neizdevās noraidīt psihologa profilu.');
        }

        $message = 'Psihologa profils noraidīts.';
    } elseif ($action === 'delete_psych') {
        if ($hasLinkedAppointments($conn, $accountId, 'psychologist')) {
            throw new RuntimeException('Psihologa kontu nevar dzēst, jo tam ir saistīti pieraksti. Vispirms jāizņem vai jāpabeidz šie ieraksti.');
        }

        $stmt = $conn->prepare("DELETE FROM accounts WHERE id = ? AND role = 'psychologist'");
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $deleted = $stmt->affected_rows > 0;
        $stmt->close();

        if (!$deleted) {
            throw new RuntimeException('Neizdevās izdzēst psihologa kontu.');
        }

        $message = 'Psihologa konts izdzēsts.';
    } elseif ($action === 'delete_user') {
        if ($hasLinkedAppointments($conn, $accountId, 'user')) {
            throw new RuntimeException('Lietotāja kontu nevar dzēst, jo tam ir saistīti pieraksti. Vispirms jāizņem vai jāpabeidz šie ieraksti.');
        }

        $stmt = $conn->prepare("DELETE FROM accounts WHERE id = ? AND role = 'user'");
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $deleted = $stmt->affected_rows > 0;
        $stmt->close();

        if (!$deleted) {
            throw new RuntimeException('Neizdevās izdzēst lietotāja kontu.');
        }

        $message = 'Lietotāja konts izdzēsts.';
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Neatbalstīta darbība.']);
        exit();
    }

    echo json_encode([
        'success' => true,
        'message' => $message,
        'stats' => $buildStats($conn),
    ]);
} catch (Throwable $e) {
    if ($conn->errno) {
        $conn->rollback();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage() ?: 'Notika neparedzēta kļūda.',
    ]);
}
?>