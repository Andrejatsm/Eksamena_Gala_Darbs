<?php
session_start();
require '../includes/db.php';
require '../includes/encryption.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['account_id'], $_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Nav autorizēts.']);
    exit();
}

$account_id = (int)$_SESSION['account_id'];
$role = $_SESSION['role'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Verify that this user is a participant in the appointment
function verifyParticipant(mysqli $conn, int $appointmentId, int $accountId): ?array {
    $stmt = $conn->prepare(
        "SELECT id, user_account_id, psychologist_account_id, status, consultation_type, scheduled_at, chat_activated_at
         FROM appointments WHERE id = ?"
    );
    $stmt->bind_param("i", $appointmentId);
    $stmt->execute();
    $appt = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$appt) return null;
    if ((int)$appt['user_account_id'] !== $accountId && (int)$appt['psychologist_account_id'] !== $accountId) return null;

    return $appt;
}

// Čats ir aktīvs tikai tad, kad psihologs to ir aktivizējis un sesija nav beigusies
function isChatActive(array $appt): bool {
    if (empty($appt['chat_activated_at'])) return false;
    $scheduledTs = strtotime($appt['scheduled_at'] ?? '');
    return $scheduledTs > time() - 7200; // 2h pēc sesijas sākuma
}

// GET: Fetch messages for an appointment
if ($action === 'fetch' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $appointment_id = (int)($_GET['appointment_id'] ?? 0);
    $after_id = (int)($_GET['after_id'] ?? 0);

    if ($appointment_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Nepareizs pieraksta ID.']);
        exit();
    }

    $appt = verifyParticipant($conn, $appointment_id, $account_id);
    if (!$appt) {
        http_response_code(403);
        echo json_encode(['error' => 'Nav piekļuves.']);
        exit();
    }

    if (!isChatActive($appt)) {
        echo json_encode(['messages' => [], 'chat_inactive' => true]);
        exit();
    }

    // Mark messages from the other person as read
    $markStmt = $conn->prepare(
        "UPDATE chat_messages SET is_read = 1 WHERE appointment_id = ? AND sender_account_id != ? AND is_read = 0"
    );
    $markStmt->bind_param("ii", $appointment_id, $account_id);
    $markStmt->execute();
    $markStmt->close();

    // Fetch messages (after_id for polling new ones)
    $stmt = $conn->prepare(
        "SELECT m.id, m.sender_account_id, m.message, m.is_read, m.created_at
         FROM chat_messages m
         WHERE m.appointment_id = ? AND m.id > ?
         ORDER BY m.created_at ASC
         LIMIT 200"
    );
    $stmt->bind_param("ii", $appointment_id, $after_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = [
            'id' => (int)$row['id'],
            'sender_id' => (int)$row['sender_account_id'],
            'message' => saprasts_decrypt($row['message']),
            'is_read' => (bool)$row['is_read'],
            'created_at' => $row['created_at'],
            'is_mine' => (int)$row['sender_account_id'] === $account_id,
        ];
    }
    $stmt->close();

    echo json_encode(['messages' => $messages]);
    exit();
}

// POST: Send a message
if ($action === 'send' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = (int)($_POST['appointment_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');

    if ($appointment_id <= 0 || $message === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Trūkst datu.']);
        exit();
    }

    if (mb_strlen($message) > 5000) {
        http_response_code(400);
        echo json_encode(['error' => 'Ziņojums ir pārāk garš.']);
        exit();
    }

    $appt = verifyParticipant($conn, $appointment_id, $account_id);
    if (!$appt) {
        http_response_code(403);
        echo json_encode(['error' => 'Nav piekļuves.']);
        exit();
    }

    // Only approved appointments can have chat
    if ($appt['status'] !== 'approved') {
        http_response_code(403);
        echo json_encode(['error' => 'Čats pieejams tikai apstiprinātiem pierakstiem.']);
        exit();
    }

    if (!isChatActive($appt)) {
        http_response_code(403);
        echo json_encode(['error' => 'Čats pašlaik nav aktīvs. Psihologs to vēl nav aktivizējis.']);
        exit();
    }

    $encryptedMessage = saprasts_encrypt($message);
    $stmt = $conn->prepare(
        "INSERT INTO chat_messages (appointment_id, sender_account_id, message) VALUES (?, ?, ?)"
    );
    $stmt->bind_param("iis", $appointment_id, $account_id, $encryptedMessage);
    $stmt->execute();
    $newId = (int)$stmt->insert_id;
    $stmt->close();

    echo json_encode([
        'success' => true,
        'message' => [
            'id' => $newId,
            'sender_id' => $account_id,
            'message' => $message,
            'is_read' => false,
            'created_at' => date('Y-m-d H:i:s'),
            'is_mine' => true,
        ]
    ]);
    exit();
}

// GET: Unread count for current user across all appointments
if ($action === 'unread' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare(
        "SELECT m.appointment_id, COUNT(*) as unread_count
         FROM chat_messages m
         JOIN appointments a ON m.appointment_id = a.id
         WHERE m.sender_account_id != ? AND m.is_read = 0
           AND (a.user_account_id = ? OR a.psychologist_account_id = ?)
         GROUP BY m.appointment_id"
    );
    $stmt->bind_param("iii", $account_id, $account_id, $account_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $unread = [];
    while ($row = $result->fetch_assoc()) {
        $unread[(int)$row['appointment_id']] = (int)$row['unread_count'];
    }
    $stmt->close();

    echo json_encode(['unread' => $unread]);
    exit();
}

http_response_code(400);
echo json_encode(['error' => 'Nezināma darbība.']);
