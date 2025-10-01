<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['rol'] !== 'admin') {
  header('Location: login.php');
  exit();
}

$rol = $_SESSION['rol'];

$query = "SELECT * FROM destinatii";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="ro">

<head>
  <meta charset="UTF-8">
  <title>Vizualizare Destinații</title>
  <link rel="stylesheet" href="dashboard.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    .table-container {
      max-width: 1000px;
      margin: 50px auto;
      background: white;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      text-align: center;
    }

    th,
    td {
      padding: 15px;
      border-bottom: 1px solid #ccc;
    }

    th {
      background-color: #00796b;
      color: white;
      font-weight: bold;
    }

    tr:hover {
      background-color: #f1f1f1;
    }

    img {
      width: 100px;
      height: 70px;
      object-fit: cover;
      border-radius: 8px;
    }

    h2 {
      text-align: center;
      margin: 30px 0;
      color: #00796b;
    }

    .hero {
      background-image: url('imagini/login1.jpg');
      background-size: cover;
      background-position: center;
      height: 550px;
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .hero::after {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.3);
      backdrop-filter: blur(2px);
    }

    .hero-content {
      position: relative;
      color: white;
      z-index: 1;
      text-align: center;
    }

    .hero-content h1 {
      font-size: 2.5rem;
      font-weight: 700;
      text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.5);
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

  <div class="table-container">
    <h2>Destinații Disponibile</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Țară</th>
          <th>Oraș</th>
          <th>Imagine</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['tara']) ?></td>
            <td><?= htmlspecialchars($row['oras']) ?></td>
            <td><img src="imagini/<?= htmlspecialchars($row['imagine']) ?>" alt="Imagine"></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

</body>

</html>