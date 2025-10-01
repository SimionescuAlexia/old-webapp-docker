<?php
include 'includes/db.php';

$referer = $_SERVER['HTTP_REFERER'] ?? 'admin.php';
$file_path = 'exporturi/rezervari.xls';

if (file_exists($file_path)) {

    $xml_content = file_get_contents($file_path);
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($xml_content);

    if ($xml !== false) {
        $xml->registerXPathNamespace('ss', 'urn:schemas-microsoft-com:office:spreadsheet');
        $rows = $xml->xpath('//ss:Row');

        $conn->begin_transaction();

        try {
            $conn->query("TRUNCATE TABLE rezervari");

            // Începem de la 1 pentru a sări peste header
            for ($i = 1; $i < count($rows); $i++) {
                $cells = $rows[$i]->xpath('.//ss:Cell/ss:Data');

                if (count($cells) >= 7) {
                    $id = (int) $cells[0];
                    $id_hotel = (int) $cells[1];
                    $data_checkin = $conn->real_escape_string((string) $cells[2]);
                    $data_checkout = $conn->real_escape_string((string) $cells[3]);
                    $durata_nopti = (int) $cells[4];
                    $total = (float) str_replace(',', '.', (string) $cells[5]);
                    $id_utilizator = (int) $cells[6];

                    $sql = "REPLACE INTO rezervari (id, id_hotel, data_checkin, data_checkout, durata_nopti, total, id_utilizator) 
                            VALUES ($id, $id_hotel, '$data_checkin', '$data_checkout', $durata_nopti, $total, $id_utilizator)";

                    $conn->query($sql);
                }
            }

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
        }
    }
}

header("Location: $referer");
exit();
?>
