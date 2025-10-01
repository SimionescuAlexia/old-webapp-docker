<?php
include 'includes/db.php';

$folder = 'exporturi/';
if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

$file = $folder . 'rezervari.xls';
$fp = fopen($file, 'w');

fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" 
          xmlns:x="urn:schemas-microsoft-com:office:excel" 
          xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" 
          xmlns:html="http://www.w3.org/TR/REC-html40">
<Worksheet ss:Name="Rezervari">
<Table>');

fwrite($fp, '<Row>
    <Cell><Data ss:Type="String">ID</Data></Cell>
    <Cell><Data ss:Type="String">ID Hotel</Data></Cell>
    <Cell><Data ss:Type="String">Check-in</Data></Cell>
    <Cell><Data ss:Type="String">Check-out</Data></Cell>
    <Cell><Data ss:Type="String">Nopti</Data></Cell>
    <Cell><Data ss:Type="String">Total (EURO)</Data></Cell>
    <Cell><Data ss:Type="String">ID Utilizator</Data></Cell>
</Row>');

$query = "SELECT id, id_hotel, data_checkin, data_checkout, durata_nopti, total, id_utilizator FROM rezervari";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    fwrite($fp, '<Row>
        <Cell><Data ss:Type="Number">' . $row['id'] . '</Data></Cell>
        <Cell><Data ss:Type="Number">' . $row['id_hotel'] . '</Data></Cell>
        <Cell><Data ss:Type="String">' . $row['data_checkin'] . '</Data></Cell>
        <Cell><Data ss:Type="String">' . $row['data_checkout'] . '</Data></Cell>
        <Cell><Data ss:Type="Number">' . $row['durata_nopti'] . '</Data></Cell>
        <Cell><Data ss:Type="Number">' . number_format($row['total'], 2, '.', '') . '</Data></Cell>
        <Cell><Data ss:Type="Number">' . $row['id_utilizator'] . '</Data></Cell>
    </Row>');
}

fwrite($fp, '</Table>
</Worksheet>
</Workbook>');

fclose($fp);

header('Location: ' . $file);
exit();
?>
