<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['rol'] !== 'admin') {
  header('Location: login.php');
  exit();
}

$rol = $_SESSION['rol'];

$dataStart = $_GET['start'] ?? null;
$dataEnd = $_GET['end'] ?? null;

$conditiePerioada = '';
if ($dataStart && $dataEnd) {
    $dataStartSQL = $conn->real_escape_string($dataStart);
    $dataEndSQL = $conn->real_escape_string($dataEnd);
    $conditiePerioada = "WHERE data_checkin BETWEEN '$dataStartSQL' AND '$dataEndSQL'";
}

$raportVenituri = $conn->query("
    SELECT DATE(data_checkin) as zi, SUM(total) as venit_total
    FROM rezervari
    $conditiePerioada
    GROUP BY zi
    ORDER BY zi
");

$raportRezervariDestinatie = $conn->query("
    SELECT d.oras, COUNT(r.id) as nr_rezervari
    FROM rezervari r
    JOIN hoteluri h ON r.id_hotel = h.id
    JOIN destinatii d ON h.id_destinatie = d.id
    $conditiePerioada
    GROUP BY d.oras
    ORDER BY nr_rezervari DESC
");

?>

<!DOCTYPE html>
<html lang="ro">

<head>
  <meta charset="UTF-8">
  <title>Rapoarte Rezervări</title>
  <link rel="stylesheet" href="dashboard.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    .raport-container {
      max-width: 1100px;
      margin: 40px auto;
      background: white;
      padding: 25px;
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .raport-container h2 {
      text-align: center;
      margin-bottom: 30px;
      color: #00796b;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 40px;
    }

    th, td {
      padding: 12px;
      text-align: center;
      border-bottom: 1px solid #ddd;
    }

    th {
      background-color: #00796b;
      color: white;
    }

    form {
      text-align: center;
      margin-bottom: 30px;
    }

    input[type="date"] {
      padding: 8px;
      margin: 0 10px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    button {
      padding: 8px 16px;
      background-color: #00796b;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    button:hover {
      background-color: #004d40;
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

  <div class="raport-container">
    <h2>Rapoarte Rezervări</h2>

    <form method="GET">
      <label for="start">De la: </label>
      <input type="date" name="start" id="start" required value="<?= htmlspecialchars($_GET['start'] ?? '') ?>">
      <label for="end">Până la: </label>
      <input type="date" name="end" id="end" required value="<?= htmlspecialchars($_GET['end'] ?? '') ?>">
      <button type="submit">Generează Raport</button>
    </form>

    <?php if ($dataStart && $dataEnd): ?>
      <h3 style="text-align:center;">Perioadă: <?= htmlspecialchars($dataStart) ?> - <?= htmlspecialchars($dataEnd) ?></h3>

      <h2>Raport Zilnic Venituri</h2>
      <table>
        <tr>
          <th>Data</th>
          <th>Venit Total (€)</th>
        </tr>
        <?php while ($row = $raportVenituri->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['zi']) ?></td>
            <td><?= number_format($row['venit_total'], 2) ?> €</td>
          </tr>
        <?php endwhile; ?>
      </table>

      <h2>Rezervări pe Destinație</h2>
      <table>
        <tr>
          <th>Destinație</th>
          <th>Număr Rezervări</th>
        </tr>
        <?php while ($row = $raportRezervariDestinatie->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['oras']) ?></td>
            <td><?= htmlspecialchars($row['nr_rezervari']) ?></td>
          </tr>
        <?php endwhile; ?>
      </table>
    <?php else: ?>
      <p style="text-align:center;">Selectează o perioadă pentru a genera rapoarte.</p>
    <?php endif; ?>
  </div>

</body>
</html>
