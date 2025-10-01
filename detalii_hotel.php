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


$checkout = date('Y-m-d', strtotime($checkin . " + $nopti days"));


$checkin_display = date('d F Y', strtotime($checkin));
$checkout_display = date('d F Y', strtotime($checkout));

$query_hotel = "
SELECT h.*, d.tara, d.oras
FROM hoteluri h
JOIN destinatii d ON h.id_destinatie = d.id
WHERE h.id = ?
";

$stmt_hotel = $conn->prepare($query_hotel);
$stmt_hotel->bind_param("i", $id_hotel);
$stmt_hotel->execute();
$result_hotel = $stmt_hotel->get_result();

if ($result_hotel->num_rows === 0) {
    header("Location: destinatii.php");
    exit();
}

$hotel = $result_hotel->fetch_assoc();

$query_pret = "
SELECT MIN(pret_pe_noapte) as pret_minim_noapte
FROM oferte
WHERE id_hotel = ?
";

$stmt_pret = $conn->prepare($query_pret);
$stmt_pret->bind_param("i", $id_hotel);
$stmt_pret->execute();
$result_pret = $stmt_pret->get_result();
$pret_data = $result_pret->fetch_assoc();
$pret_minim_noapte = $pret_data['pret_minim_noapte'] ?? null;

$pret_total = $pret_minim_noapte * $nopti;

$query_descriere = "
SELECT descriere
FROM oferte
WHERE id_hotel = ?
LIMIT 1
";

$stmt_descriere = $conn->prepare($query_descriere);
$stmt_descriere->bind_param("i", $id_hotel);
$stmt_descriere->execute();
$result_descriere = $stmt_descriere->get_result();
$descriere_data = $result_descriere->fetch_assoc();
$descriere_hotel = $descriere_data['descriere'] ?? '';
?>

<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($hotel['nume']) ?> - Detalii Hotel</title>
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .hotel-details {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .hotel-header {
            display: flex;
            flex-direction: column;
            margin-bottom: 30px;
            align-items: center;
            text-align: center;
        }

        .hotel-name {
            font-size: 2.5rem;
            color: #00796b;
            margin-bottom: 10px;
        }

        .hotel-location {
            font-size: 1.2rem;
            color: #555;
            margin-bottom: 15px;
        }

        .hotel-address-header {
            font-size: 1.1rem;
            color: #555;
            margin-bottom: 15px;
        }

        .hotel-stars {
            color: gold;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .hotel-image-container {
            width: 100%;
            height: 500px;
            overflow: hidden;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .hotel-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hotel-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        @media (max-width: 768px) {
            .hotel-info-grid {
                grid-template-columns: 1fr;
            }
        }

        .hotel-description-panel,
        .hotel-price,
        .hotel-dates,
        .hotel-description {
            background-color: #f5f5f5;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .info-title {
            font-size: 1.4rem;
            color: #00796b;
            margin-bottom: 15px;
            border-bottom: 2px solid #00796b;
            padding-bottom: 10px;
        }

        .description-text {
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .price-details,
        .dates-details {
            font-size: 1.1rem;
            line-height: 1.8;
        }

        .price-highlight {
            font-size: 1.4rem;
            font-weight: bold;
            color: #00796b;
            margin: 10px 0;
        }

        .total-price {
            font-size: 1.6rem;
            font-weight: bold;
            color: #e65100;
            margin: 15px 0;
            padding: 10px;
            background-color: #fff8e1;
            border-radius: 8px;
            display: inline-block;
        }

        .dates-highlight {
            font-weight: bold;
            color: #00796b;
        }

        .button-group {
            display: flex;
            justify-content: center;
            margin-top: 40px;
            gap: 20px;
        }

        .action-button {
            display: inline-block;
            background-color: #00796b;
            color: white;
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 1.1rem;
            font-weight: 600;
            transition: background-color 0.3s;
        }

        .action-button:hover {
            background-color: #004d40;
        }

        .back-button {
            background-color: #555;
        }

        .reserve-button {
            background-color: #e65100;
        }

        .reserve-button:hover {
            background-color: #bf360c;
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <div class="logo"><img src="imagini/logo.png" alt="logo"></div>
        <ul class="menu">
            <li><a href="destinatii.php">Destinații</a></li>
            <li><a href="comentarii.php">De ce să ne alegi?</a></li>
            <li><a href="rezervari.php">Rezervări</a></li>

            <?php if ($rol === 'admin'): ?>
                <li class="dropdown">
                    <a href="#">Vizualizare</a>
                    <div class="dropdown-content">
                        <a href="view_destinatie.php">Destinație</a>
                        <a href="view_hotel.php">Hotel</a>
                        <a href="view_oferta.php">Oferta</a>
                        <a href="view_rezervare.php">Rezervare</a>
                    </div>
                </li>
                <li class="dropdown">
                    <a href="#">Inserare</a>
                    <div class="dropdown-content">
                        <a href="inserare_destinatie.php">Destinație</a>
                        <a href="inserare_hotel.php">Hotel</a>
                        <a href="inserare_oferta.php">Oferta</a>
                        <a href="inserare_rezervare.php">Rezervare</a>
                    </div>
                </li>
                <li class="dropdown">
                    <a href="#">Modificare</a>
                    <div class="dropdown-content">
                        <a href="edit_destinatie.php">Destinație</a>
                        <a href="edit_hotel.php">Hotel</a>
                        <a href="edit_oferta.php">Oferta</a>
                        <a href="edit_rezervare.php">Rezervare</a>
                    </div>
                </li>
                <li class="dropdown">
                    <a href="#">Ștergere</a>
                    <div class="dropdown-content">
                        <a href="delete_destinatie.php">Destinație</a>
                        <a href="delete_hotel.php">Hotel</a>
                        <a href="delete_oferta.php">Oferta</a>
                        <a href="delete_rezervare.php">Rezervare</a>
                    </div>
                </li>

                <li><a href="rapoarte.php">Rapoarte</a></li>
                <li><a href="grafice.php">Grafice</a></li>
                <li class="dropdown">
                    <a href="#">Export</a>
                    <div class="dropdown-content">
                        <a href="export_pdf.php">Exportă în PDF</a>
                        <a href="export_excel.php">Exportă în XLS</a>
                    </div>
                </li>
                <li><a href="import.php">Import</a></li>
            <?php endif; ?>

            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <section class="hotel-details">
        <div class="hotel-header">
            <h1 class="hotel-name"><?= htmlspecialchars($hotel['nume']) ?></h1>
            <div class="hotel-location"><?= htmlspecialchars($hotel['oras']) ?>, <?= htmlspecialchars($hotel['tara']) ?>
            </div>
            <div class="hotel-address-header"><?= htmlspecialchars($hotel['adresa']) ?></div>
            <div class="hotel-stars">
                <?php for ($i = 0; $i < $hotel['stele']; $i++)
                    echo "⭐"; ?>
            </div>
        </div>

        <div class="hotel-image-container">
            <img src="imagini/<?= htmlspecialchars($hotel['imagine']) ?>" alt="<?= htmlspecialchars($hotel['nume']) ?>"
                class="hotel-image">
        </div>

        <div class="hotel-dates">
            <h2 class="info-title">Perioada Selectată</h2>
            <div class="dates-details">
                <p>Check-in: <span class="dates-highlight"><?= $checkin_display ?></span></p>
                <p>Check-out: <span class="dates-highlight"><?= $checkout_display ?></span></p>
                <p>Număr nopți: <span class="dates-highlight"><?= $nopti ?></span></p>
            </div>
        </div>

        <div class="hotel-info-grid">
            <div class="hotel-description-panel">
                <h2 class="info-title">Descriere Hotel</h2>
                <p class="description-text">
                    <?= nl2br(htmlspecialchars($descriere_hotel)) ?>
                </p>
            </div>

            <div class="hotel-price">
                <h2 class="info-title">Informații Preț</h2>
                <div class="price-details">
                    <?php if ($pret_minim_noapte): ?>
                        <p>Preț pe noapte:</p>
                        <p class="price-highlight"><?= number_format($pret_minim_noapte, 2) ?> € / noapte</p>
                        <p>Pentru <?= $nopti ?> nopți, prețul total este:</p>
                        <p class="total-price"><?= number_format($pret_total, 2) ?> €</p>
                    <?php else: ?>
                        <p>Momentan nu există oferte disponibile pentru acest hotel.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="button-group">
            <a href="javascript:history.back()" class="action-button back-button">Înapoi</a>
            <a href="rezerva.php?id=<?= $id_hotel ?>&checkin=<?= htmlspecialchars($checkin) ?>&nopti=<?= htmlspecialchars($nopti) ?>"
                class="action-button reserve-button">Rezervă Cameră</a>
        </div>
    </section>

</body>

</html>