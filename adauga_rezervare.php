<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['rol'] !== 'client') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_hotel = intval($_POST['id_hotel']);
    $checkin = $_POST['checkin'];
    $nopti = intval($_POST['nopti']);
    $email_client = $_SESSION['user'];

    $sql = "SELECT id, pret_pe_noapte FROM oferte WHERE id_hotel = ? ORDER BY pret_pe_noapte ASC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_hotel);
    $stmt->execute();
    $res = $stmt->get_result();
    $oferta = $res->fetch_assoc();

    if (!$oferta) {
        $_SESSION['error'] = "Nu există ofertă disponibilă pentru acest hotel!";
        header("Location: rezervari.php");
        exit();
    }

    $total = $oferta['pret_pe_noapte'] * $nopti;
    $data_rezervare = date('Y-m-d');
    $data_checkout = date('Y-m-d', strtotime($checkin . " +{$nopti} days"));

    $insert = $conn->prepare("INSERT INTO rezervari (email_client, id_hotel, id_oferta, data_checkin, data_checkout, total) VALUES (?, ?, ?, ?, ?, ?)");
    $insert->bind_param("siissd", $email_client, $id_hotel, $oferta['id'], $checkin, $data_checkout, $total);
    $insert->execute();

    $_SESSION['success'] = "Rezervarea a fost adăugată cu succes!";
    header("Location: rezervari.php");
    exit();
}
?>