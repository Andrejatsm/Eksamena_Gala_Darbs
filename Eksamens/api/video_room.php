<?php
session_start();
require '../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['account_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Nav autorizēts.']);
    exit();
}

$account_id = (int)$_SESSION['account_id'];
$appointment_id = (int)($_GET['appointment_id'] ?? $_POST['appointment_id'] ?? 0);

function ensureSessionLock(mysqli $conn, array $appt, string $sessionId, int $accountId): bool {
    $role = (int)$appt['psychologist_account_id'] === $accountId ? 'psychologist' : 'user';
    $column = $role === 'psychologist' ? 'psychologist_session_id' : 'user_session_id';
    $existingSession = $appt[$column] ?? '';

    if ($existingSession !== '' && $existingSession !== $sessionId) {
        return false;
    }

    if ($existingSession === '') {
        $stmt = $conn->prepare("UPDATE appointments SET {$column} = ? WHERE id = ? AND ({$column} IS NULL OR {$column} = '')");
        $stmt->bind_param("si", $sessionId, $appt['id']);
        $stmt->execute();
        $stmt->close();
    }

    return true;
}

if ($appointment_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Nepareizs pieraksta ID.']);
    exit();
}

// Verify participant
$stmt = $conn->prepare(
    "SELECT id, user_account_id, psychologist_account_id, status, consultation_type, chat_activated_at, user_session_id, psychologist_session_id
     FROM appointments WHERE id = ? AND status = 'approved' AND consultation_type = 'online' AND chat_activated_at IS NOT NULL"
);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$appt = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$appt) {
    http_response_code(403);
    echo json_encode(['error' => 'Pieraksts nav atrasts vai nav pieejams videozvaniem.']);
    exit();
}

if ((int)$appt['user_account_id'] !== $account_id && (int)$appt['psychologist_account_id'] !== $account_id) {
    http_response_code(403);
    echo json_encode(['error' => 'Nav piekļuves.']);
    exit();
}

if (!ensureSessionLock($conn, $appt, session_id(), $account_id)) {
    http_response_code(403);
    echo json_encode(['error' => 'Šī sesija ir bloķēta citai ierīcei vai pārlūkprogrammā.']);
    exit();
}

// Get or create room token
$stmt = $conn->prepare("SELECT room_token FROM video_rooms WHERE appointment_id = ?");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$room = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($room) {
    $token = $room['room_token'];
} else {
    $token = bin2hex(random_bytes(32));
    $stmt = $conn->prepare("INSERT INTO video_rooms (appointment_id, room_token) VALUES (?, ?)");
    $stmt->bind_param("is", $appointment_id, $token);
    $stmt->execute();
    $stmt->close();
}

echo json_encode(['room_token' => $token]);
