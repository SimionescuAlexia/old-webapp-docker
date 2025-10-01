<?php
// Conexiune pentru varianta cu containere (host = numele containerului MySQL)
$host     = 'mysql';
$user     = 'root';
$pass     = 'pass';
$db       = 'agentie_turism';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli = new mysqli($host, $user, $pass, $db);
$mysqli->set_charset('utf8mb4');
?>
