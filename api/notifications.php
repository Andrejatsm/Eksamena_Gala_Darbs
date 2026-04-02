<?php
session_start();
require '../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['account_id'], $_SESSION['role'])) {
    echo json_encode(['upcoming' => [], 'unread_total' => 0, 'unread_by_appointment' => new stdClass()]);
    exit();
}

$account_id = (int)$_SESSION['account_id'];
$role = $_SESSION['role'];

$upcoming = [];
$unread_total = 0;
$unread_by_appointment = [];

if ($role === 'user' || $role === 'psychologist') {
    // Tuvākās sesijas (30 min laikā)
    $col = $role === 'user' ? 'user_account_id' : 'psychologist_account_id';
    $joinPart = $role === 'user'
        ? "JOIN psychologist_profiles p ON ap.psychologist_account_id = p.account_id"
        : "";
    $namePart = $role === 'user'
        ? "p.full_name AS partner_name"
        : "ap.user_name_snapshot AS partner_name";

    $sql = "SELECT ap.id, ap.scheduled_at, ap.consultation_type, {$namePart}
            FROM appointments ap {$joinPart}
            WHERE ap.{$col} = ? AND ap.status = 'approved'
              AND ap.scheduled_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 MINUTE)
            ORDER BY ap.scheduled_at ASC
            LIMIT 5";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $upcoming[] = [
            'id' => (int)$row['id'],
            'scheduled_at' => $row['scheduled_at'],
            'consultation_type' => $row['consultation_type'],
            'partner_name' => $row['partner_name'] ?? '',
        ];
    }
    $stmt->close();

    // Nelasītās čata ziņas
    $stmt = $conn->prepare(
        "SELECT m.appointment_id, COUNT(*) AS cnt
         FROM chat_messages m
         JOIN appointments a ON m.appointment_id = a.id
         WHERE m.sender_account_id != ? AND m.is_read = 0
           AND (a.user_account_id = ? OR a.psychologist_account_id = ?)
         GROUP BY m.appointment_id"
    );
    $stmt->bind_param("iii", $account_id, $account_id, $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $unread_by_appointment[(int)$row['appointment_id']] = (int)$row['cnt'];
        $unread_total += (int)$row['cnt'];
    }
    $stmt->close();
}

echo json_encode([
    'upcoming' => $upcoming,
    'unread_total' => $unread_total,
    'unread_by_appointment' => (object)$unread_by_appointment,
]);
