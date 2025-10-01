<?php
$host = 'localhost';
$user = 'root';
$password = '';
$db = 'agentie_turism';

$conn = new mysqli($host, $user, $password, $db);
if ($conn->connect_error) {
    die("Conexiune eșuată: " . $conn->connect_error);
}
?>
