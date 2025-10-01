<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['rol'] !== 'admin') {
  header('Location: login.php');
  exit();
}

$rol = $_SESSION['rol'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
  $id = intval($_POST['id']);
  $id_utilizator = intval($_POST['id_utilizator']);
  $id_hotel = intval($_POST['id_hotel']);
  $data_checkin = $_POST['data_checkin'];
  $data_checkout = $_POST['data_checkout'];

  if ($id_utilizator > 0 && $id_hotel > 0 && !empty($data_checkin) && !empty($data_checkout)) {
    $checkin = new DateTime($data_checkin);
    $checkout = new DateTime($data_checkout);
    $durata_nopti = $checkin->diff($checkout)->days;

    $stmtHotel = $conn->prepare("SELECT pret_pe_noapte FROM oferte WHERE id_hotel = ?");
    $stmtHotel->bind_param("i", $id_hotel);
    $stmtHotel->execute();
    $pret_pe_noapte = $stmtHotel->get_result()->fetch_assoc()['pret_pe_noapte'] ?? 0;

    $total = $durata_nopti * $pret_pe_noapte;

    $stmt = $conn->prepare("UPDATE rezervari SET id_utilizator=?, id_hotel=?, data_checkin=?, data_checkout=?, durata_nopti=?, total=? WHERE id=?");
    $stmt->bind_param("iissidi", $id_utilizator, $id_hotel, $data_checkin, $data_checkout, $durata_nopti, $total, $id);
    $stmt->execute();
  }
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search)) {
  $stmt = $conn->prepare("SELECT * FROM rezervari WHERE id_utilizator = ?");
  $stmt->bind_param("i", $search);
  $stmt->execute();
  $result = $stmt->get_result();
} else {
  $result = $conn->query("SELECT * FROM rezervari");
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Modifică Rezervări</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    .edit-container {
      max-width: 1300px;
      margin: 40px auto;
      background: white;
      padding: 25px;
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .edit-container h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #00796b;
    }

    .search-bar {
      text-align: center;
      margin-bottom: 20px;
    }

    .search-bar input[type="text"] {
      width: 300px;
      padding: 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }

    .search-bar button {
      padding: 10px 20px;
      background-color: #00796b;
      color: white;
      border: none;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 10px;
      text-align: center;
      border-bottom: 1px solid #ddd;
    }

    th {
      background-color: #00796b;
      color: white;
    }

    input[type="text"],
    input[type="number"],
    input[type="date"] {
      width: 90%;
      padding: 6px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }

    button {
      background-color: #00796b;
      color: white;
      padding: 8px 15px;
      border: none;
      border-radius: 8px;
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
<div class="edit-container">
  <h2>Modifică Rezervări</h2>

  <div class="search-bar">
    <form method="GET">
      <input type="text" name="search" placeholder="Caută după ID utilizator..." value="<?= htmlspecialchars($search) ?>">
      <button type="submit">Caută</button>
    </form>
  </div>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>ID Utilizator</th>
        <th>ID Hotel</th>
        <th>Check-in</th>
        <th>Check-out</th>
        <th>Durată</th>
        <th>Total (€)</th>
        <th>Acțiune</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <form method="POST">
            <td><?= $row['id'] ?><input type="hidden" name="id" value="<?= $row['id'] ?>"></td>
            <td><input type="number" name="id_utilizator" value="<?= $row['id_utilizator'] ?>" required></td>
            <td><input type="number" name="id_hotel" value="<?= $row['id_hotel'] ?>" required></td>
            <td><input type="date" name="data_checkin" value="<?= $row['data_checkin'] ?>" required></td>
            <td><input type="date" name="data_checkout" value="<?= $row['data_checkout'] ?>" required></td>
            <td><?= $row['durata_nopti'] ?></td>
            <td><?= number_format($row['total'], 2) ?></td>
            <td><button type="submit" name="update">Salvează</button></td>
          </form>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

</body>
</html>
