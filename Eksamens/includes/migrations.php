<?php
/**
 * Datubāzes migrācijas — tabulu izveide un kolonnu pievienošana.
 * Šis fails tiek ielādēts no includes/db.php katrā pieprasījumā.
 * Visas komandas izmanto IF NOT EXISTS / SHOW COLUMNS, tāpēc var droši palaist atkārtoti.
 */

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Migrācijas failu var izsaukt tikai no db.php');
}

// ── Kolonnu pievienošana esošām tabulām ─────────────────────────

$availabilityTypeColumn = $conn->query("SHOW COLUMNS FROM availability_slots LIKE 'consultation_type'");
if ($availabilityTypeColumn && $availabilityTypeColumn->num_rows === 0) {
    $conn->query(
        "ALTER TABLE availability_slots
         ADD COLUMN consultation_type ENUM('in_person','online') NOT NULL DEFAULT 'online' AFTER ends_at"
    );
}
if ($availabilityTypeColumn instanceof mysqli_result) {
    $availabilityTypeColumn->free();
}

$contactReadColumn = $conn->query("SHOW COLUMNS FROM contact_messages LIKE 'is_read'");
if ($contactReadColumn && $contactReadColumn->num_rows === 0) {
    $conn->query(
        "ALTER TABLE contact_messages
         ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0 AFTER message"
    );
}
if ($contactReadColumn instanceof mysqli_result) {
    $contactReadColumn->free();
}

$contactReadAtColumn = $conn->query("SHOW COLUMNS FROM contact_messages LIKE 'read_at'");
if ($contactReadAtColumn && $contactReadAtColumn->num_rows === 0) {
    $conn->query(
        "ALTER TABLE contact_messages
         ADD COLUMN read_at TIMESTAMP NULL DEFAULT NULL AFTER is_read"
    );
}
if ($contactReadAtColumn instanceof mysqli_result) {
    $contactReadAtColumn->free();
}

$slotBookedColumn = $conn->query("SHOW COLUMNS FROM availability_slots LIKE 'is_booked'");
if ($slotBookedColumn && $slotBookedColumn->num_rows === 0) {
    $conn->query(
        "ALTER TABLE availability_slots
         ADD COLUMN is_booked TINYINT(1) NOT NULL DEFAULT 0 AFTER note"
    );
}
if ($slotBookedColumn instanceof mysqli_result) {
    $slotBookedColumn->free();
}

$chatActivatedCol = $conn->query("SHOW COLUMNS FROM appointments LIKE 'chat_activated_at'");
if ($chatActivatedCol && $chatActivatedCol->num_rows === 0) {
    $conn->query(
        "ALTER TABLE appointments
         ADD COLUMN chat_activated_at TIMESTAMP NULL DEFAULT NULL AFTER status"
    );
}
if ($chatActivatedCol instanceof mysqli_result) {
    $chatActivatedCol->free();
}

$userSessionCol = $conn->query("SHOW COLUMNS FROM appointments LIKE 'user_session_id'");
if ($userSessionCol && $userSessionCol->num_rows === 0) {
    $conn->query(
        "ALTER TABLE appointments
         ADD COLUMN user_session_id VARCHAR(128) NULL DEFAULT NULL AFTER chat_activated_at"
    );
}
if ($userSessionCol instanceof mysqli_result) {
    $userSessionCol->free();
}

$psySessionCol = $conn->query("SHOW COLUMNS FROM appointments LIKE 'psychologist_session_id'");
if ($psySessionCol && $psySessionCol->num_rows === 0) {
    $conn->query(
        "ALTER TABLE appointments
         ADD COLUMN psychologist_session_id VARCHAR(128) NULL DEFAULT NULL AFTER user_session_id"
    );
}
if ($psySessionCol instanceof mysqli_result) {
    $psySessionCol->free();
}

// ── Jaunu tabulu izveide ────────────────────────────────────────

$conn->query(
    "CREATE TABLE IF NOT EXISTS article_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL UNIQUE,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        sort_order INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$conn->query(
    "CREATE TABLE IF NOT EXISTS psychologist_specializations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL UNIQUE,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        sort_order INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$conn->query(
    "CREATE TABLE IF NOT EXISTS chat_messages (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        appointment_id INT UNSIGNED NOT NULL,
        sender_account_id INT UNSIGNED NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_chat_appointment (appointment_id),
        KEY idx_chat_sender (sender_account_id),
        KEY idx_chat_created (created_at),
        CONSTRAINT fk_chat_appointment
            FOREIGN KEY (appointment_id) REFERENCES appointments(id)
            ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fk_chat_sender
            FOREIGN KEY (sender_account_id) REFERENCES accounts(id)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

$conn->query(
    "CREATE TABLE IF NOT EXISTS video_rooms (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        appointment_id INT UNSIGNED NOT NULL,
        room_token VARCHAR(64) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_video_appointment (appointment_id),
        UNIQUE KEY uq_video_token (room_token),
        CONSTRAINT fk_video_appointment
            FOREIGN KEY (appointment_id) REFERENCES appointments(id)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

// ── Sākuma dati (seed) ──────────────────────────────────────────

$categorySeed = [
    ["Stresa vadīšana", 10],
    ["Trauksme", 20],
    ["Depresija", 30],
    ["Attiecības", 40],
    ["Pašvērtējums", 50],
    ["Miegs un izdegšana", 60],
    ["Bērnu un pusaudžu psiholoģija", 70],
    ["Darbs un karjera", 80],
];

$specSeed = [
    ["Depresija un trauksme", 10],
    ["Attiecību terapija", 20],
    ["Ģimenes terapija", 30],
    ["Bērnu un pusaudžu psiholoģija", 40],
    ["Trauma un PTSS", 50],
    ["Atkarību terapija", 60],
    ["Kognitīvi biheiviorālā terapija", 70],
    ["Stresa vadība un izdegšana", 80],
];

$catStmt = $conn->prepare(
    "INSERT INTO article_categories (name, sort_order) VALUES (?, ?) ON DUPLICATE KEY UPDATE sort_order = VALUES(sort_order)"
);
if ($catStmt) {
    foreach ($categorySeed as [$name, $sort]) {
        $catStmt->bind_param("si", $name, $sort);
        $catStmt->execute();
    }
    $catStmt->close();
}

$specStmt = $conn->prepare(
    "INSERT INTO psychologist_specializations (name, sort_order) VALUES (?, ?) ON DUPLICATE KEY UPDATE sort_order = VALUES(sort_order)"
);
if ($specStmt) {
    foreach ($specSeed as [$name, $sort]) {
        $specStmt->bind_param("si", $name, $sort);
        $specStmt->execute();
    }
    $specStmt->close();
}
