<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['rol'], ['client', 'admin'])) {
    header("Location: login.php");
    exit();
}

$rol = $_SESSION['rol'];

if (!isset($_GET['id']) || !isset($_GET['checkin']) || !isset($_GET['nopti'])) {
    header("Location: destinatii.php");
    exit();
}

$id_hotel = intval($_GET['id']);
$checkin = $_GET['checkin'];
$nopti = intval($_GET['nopti']);
$user_email = $_SESSION['user'] ?? '';

if (empty($user_email)) {
    echo "Eroare: emailul utilizatorului nu este disponibil.";
    exit();
}

// Obține ID-ul utilizatorului
$stmt_user = $conn->prepare("SELECT id FROM utilizatori WHERE email = ?");
$stmt_user->bind_param("s", $user_email);
$stmt_user->execute();
$res_user = $stmt_user->get_result();
$user = $res_user->fetch_assoc();

if (!$user) {
    echo "Utilizatorul nu a fost găsit.";
    exit();
}

$id_utilizator = $user['id'];
$checkout = date('Y-m-d', strtotime($checkin . " + $nopti days"));

// Află prețul minim pe noapte
$query_pret = "
    SELECT MIN(o.pret_pe_noapte) AS pret_minim
    FROM oferte o
    WHERE o.id_hotel = ?
";

$stmt_pret = $conn->prepare($query_pret);
$stmt_pret->bind_param("i", $id_hotel);
$stmt_pret->execute();
$result_pret = $stmt_pret->get_result();
$row_pret = $result_pret->fetch_assoc();
$pret_pe_noapte = $row_pret['pret_minim'] ?? 0;

$total = $pret_pe_noapte * $nopti;

$query = "INSERT INTO rezervari (id_utilizator, id_hotel, data_checkin, data_checkout, durata_nopti, total) 
          VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iissid", $id_utilizator, $id_hotel, $checkin, $checkout, $nopti, $total);

if ($stmt->execute()) {
    header("Location: rezervari.php?rez=ok");
    exit();
} else {
    echo "Eroare la rezervare: " . $stmt->error;
}
?>