<?php
require('fpdf/fpdf.php');
include 'includes/db.php';

$folder = 'exporturi/';
if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);

$pdf->Cell(10, 10, 'ID', 1);
$pdf->Cell(20, 10, 'ID Hotel', 1);
$pdf->Cell(30, 10, 'Check-in', 1);
$pdf->Cell(30, 10, 'Check-out', 1);
$pdf->Cell(20, 10, 'Nopti', 1);
$pdf->Cell(30, 10, 'Total (EURO)', 1);
$pdf->Cell(30, 10, 'ID Utilizator', 1);
$pdf->Ln();


$query = "
    SELECT id, id_hotel, data_checkin, data_checkout, durata_nopti, total, id_utilizator
    FROM rezervari
";
$result = $conn->query($query);

$pdf->SetFont('Arial', '', 11);
while ($row = $result->fetch_assoc()) {
    $pdf->Cell(10, 10, $row['id'], 1);
    $pdf->Cell(20, 10, $row['id_hotel'], 1);
    $pdf->Cell(30, 10, $row['data_checkin'], 1);
    $pdf->Cell(30, 10, $row['data_checkout'], 1);
    $pdf->Cell(20, 10, $row['durata_nopti'], 1);
    $pdf->Cell(30, 10, number_format($row['total'], 2), 1);
    $pdf->Cell(30, 10, $row['id_utilizator'], 1);
    $pdf->Ln();
}

$path = $folder . 'rezervari.pdf';
$pdf->Output('F', $path);

header('Location: ' . $path);
exit();
?>
