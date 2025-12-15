<?php
$servername = "localhost";
$username = "root"; // XAMPP noklusējums
$password = ""; // XAMPP noklusējums (tukšs)
$dbname = "saprasts_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Savienojums neizdevās: " . $conn->connect_error);
}
?>