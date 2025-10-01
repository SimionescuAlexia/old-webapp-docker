<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['rol'] !== 'admin') {
  header('Location: login.php');
  exit();
}

$rol = $_SESSION['rol'];

if (isset($_GET['id'])) {
  $id = intval($_GET['id']);
  $stmt = $conn->prepare("DELETE FROM destinatii WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();

  header('Location: delete_destinatie.php');
  exit();
}

$result = $conn->query("SELECT * FROM destinatii");
?>

<!DOCTYPE html>
<html lang="ro">

<head>
  <meta charset="UTF-8">
  <title>Șterge Destinații</title>
  <link rel="stylesheet" href="dashboard.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      background-color: #e0f2f1;
      font-family: 'Poppins', sans-serif;
    }

    .delete-container {
      max-width: 1200px;
      margin: 40px auto;
      background: white;
      padding: 25px;
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .delete-container h2 {
      text-align: center;
      margin-bottom: 30px;
      color: #00796b;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
    }

    th,
    td {
      padding: 15px;
      text-align: center;
      border-bottom: 1px solid #ddd;
    }

    th {
      background-color: #00796b;
      color: white;
      font-size: 16px;
    }

    td {
      font-size: 15px;
    }

    a.delete-button {
      background-color: #d32f2f;
      color: white;
      padding: 10px 20px;
      border-radius: 10px;
      text-decoration: none;
      font-size: 16px;
      font-weight: 600;
      transition: background-color 0.3s, transform 0.2s;
      display: inline-block;
    }

    a.delete-button:hover {
      background-color: #9a0007;
      transform: scale(1.05);
    }

    img {
      width: 100px;
      height: 70px;
      object-fit: cover;
      border-radius: 8px;
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

  <div class="delete-container">
    <h2>Șterge Destinații</h2>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Țară</th>
          <th>Oraș</th>
          <th>Imagine</th>
          <th>Acțiune</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['tara']) ?></td>
            <td><?= htmlspecialchars($row['oras']) ?></td>
            <td><img src="imagini/<?= htmlspecialchars($row['imagine']) ?>" alt="Imagine Destinație"></td>
            <td><a class="delete-button" href="delete_destinatie.php?id=<?= $row['id'] ?>"
                onclick="return confirm('Sigur vrei să ștergi această destinație?');">Șterge</a></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

</body>

</html>