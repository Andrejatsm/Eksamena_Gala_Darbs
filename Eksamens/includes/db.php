<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/env.php';

$servername = "localhost";
$username = "grobina1_rackovs";
$password = "GmU3ehBDZxX9!!"; 
$dbname = "grobina1_rackovs";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    error_log("DB savienojuma kļūda: " . $conn->connect_error);
    die("Sistēmas kļūda. Lūdzu, mēģiniet vēlāk.");
}

$conn->set_charset("utf8mb4");

// Tabulu izveide, kolonnu pievienošana, sākuma dati
require __DIR__ . '/migrations.php';

// Automātiskā tīrīšana: dzēšam čata ziņas un video istabas priekš sesijām, kas beidzās pirms 2h
$conn->query(
    "DELETE cm FROM chat_messages cm
     INNER JOIN appointments a ON cm.appointment_id = a.id
     WHERE a.scheduled_at < DATE_SUB(NOW(), INTERVAL 2 HOUR)
       AND a.chat_activated_at IS NOT NULL"
);
$conn->query(
    "DELETE vr FROM video_rooms vr
     INNER JOIN appointments a ON vr.appointment_id = a.id
     WHERE a.scheduled_at < DATE_SUB(NOW(), INTERVAL 2 HOUR)
       AND a.chat_activated_at IS NOT NULL"
);
$conn->query(
    "UPDATE appointments SET chat_activated_at = NULL
     WHERE scheduled_at < DATE_SUB(NOW(), INTERVAL 2 HOUR)
       AND chat_activated_at IS NOT NULL"
);
// Tīrām vecos pierakstus, kas ir senāki par 30 dienām, lai saraksti neuzkrātos.
$conn->query(
    "DELETE FROM appointments
     WHERE scheduled_at < DATE_SUB(NOW(), INTERVAL 30 DAY)"
);
?>