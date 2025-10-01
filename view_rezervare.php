<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['rol'] !== 'admin') {
  header('Location: login.php');
  exit();
}

$rol = $_SESSION['rol'];

$query = "
SELECT id, id_utilizator, id_hotel, data_checkin, data_checkout, durata_nopti, total
FROM rezervari
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="ro">

<head>
  <meta charset="UTF-8">
  <title>Rezervări Active</title>
  <link rel="stylesheet" href="dashboard.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    .container {
      max-width: 1200px;
      margin: 50px auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    }

    h2 {
      text-align: center;
      color: #00796b;
      margin-bottom: 30px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      text-align: center;
    }

    table th,
    table td {
      padding: 12px;
      border: 1px solid #ccc;
    }

    table th {
      background-color: #00796b;
      color: white;
    }

    table tr:nth-child(even) {
      background-color: #f9f9f9;
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
            <a href="view_oferta.php">Ofertă</a>
            <a href="view_rezervare.php">Rezervare</a>
          </div>
        </li>
        <li class="dropdown">
          <a href="#">Inserare</a>
          <div class="dropdown-content">
            <a href="inserare_destinatie.php">Destinație</a>
            <a href="inserare_hotel.php">Hotel</a>
            <a href="inserare_oferta.php">Ofertă</a>
            <a href="inserare_rezervare.php">Rezervare</a>
          </div>
        </li>
        <li class="dropdown">
          <a href="#">Modificare</a>
          <div class="dropdown-content">
            <a href="edit_destinatie.php">Destinație</a>
            <a href="edit_hotel.php">Hotel</a>
            <a href="edit_oferta.php">Ofertă</a>
            <a href="edit_rezervare.php">Rezervare</a>
          </div>
        </li>
        <li class="dropdown">
          <a href="#">Ștergere</a>
          <div class="dropdown-content">
            <a href="delete_destinatie.php">Destinație</a>
            <a href="delete_hotel.php">Hotel</a>
            <a href="delete_oferta.php">Ofertă</a>
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

  <div class="container">
    <h2>Rezervări</h2>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>ID Utilizator</th>
          <th>ID Hotel</th>
          <th>Check-in</th>
          <th>Check-out</th>
          <th>Număr Nopți</th>
          <th>Total (€)</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['id_utilizator']) ?></td>
            <td><?= htmlspecialchars($row['id_hotel']) ?></td>
            <td><?= htmlspecialchars($row['data_checkin']) ?></td>
            <td><?= htmlspecialchars($row['data_checkout']) ?></td>
            <td><?= htmlspecialchars($row['durata_nopti']) ?></td>
            <td><?= number_format($row['total'], 2) ?> €</td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

</body>

</html>
