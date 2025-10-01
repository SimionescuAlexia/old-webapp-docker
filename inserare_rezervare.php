<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['rol'] !== 'admin') {
  header('Location: login.php');
  exit();
}

$rol = $_SESSION['rol'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id_utilizator = intval($_POST['id_utilizator']);
  $id_hotel = intval($_POST['id_hotel']);
  $data_checkin = $_POST['data_checkin'];
  $data_checkout = $_POST['data_checkout'];

  if ($id_utilizator <= 0 || $id_hotel <= 0 || empty($data_checkin) || empty($data_checkout)) {
    $error = "Te rugăm să completezi toate câmpurile.";
  } else {
    $checkin = new DateTime($data_checkin);
    $checkout = new DateTime($data_checkout);
    $interval = $checkin->diff($checkout);
    $durata_nopti = $interval->days;

    if ($durata_nopti <= 0) {
      $error = "Data de check-out trebuie să fie după data de check-in.";
    } else {
      $stmt = $conn->prepare("SELECT pret_pe_noapte FROM oferte WHERE id_hotel = ?");
      $stmt->bind_param("i", $id_hotel);
      $stmt->execute();
      $result = $stmt->get_result();
      $row = $result->fetch_assoc();

      if (!$row) {
        $error = "Hotelul selectat nu are ofertă disponibilă.";
      } else {
        $pret_pe_noapte = $row['pret_pe_noapte'];
        $total = $pret_pe_noapte * $durata_nopti;

        $stmt = $conn->prepare("INSERT INTO rezervari (id_utilizator, id_hotel, data_checkin, data_checkout, durata_nopti, total) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissid", $id_utilizator, $id_hotel, $data_checkin, $data_checkout, $durata_nopti, $total);
        $stmt->execute();{
          header('Location: destinatii.php');
                exit();

        }
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="ro">

<head>
  <meta charset="UTF-8">
  <title>Adaugă Rezervare</title>
  <link rel="stylesheet" href="dashboard.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    .form-container {
      max-width: 500px;
      margin: 40px auto;
      background: white;
      padding: 25px;
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      text-align: center;
    }

    .form-container label {
      font-weight: 600;
      display: block;
      margin-bottom: 8px;
      text-align: left;
    }

    .form-container input[type="text"],
    .form-container input[type="number"],
    .form-container input[type="date"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border-radius: 8px;
      border: 1px solid #ccc;
    }

    .form-container button {
      background-color: #00796b;
      color: white;
      border: none;
      padding: 12px 25px;
      font-size: 16px;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    .form-container button:hover {
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


  <div class="form-container">
    <h2 style="margin-bottom: 20px; color: #00796b;">Adaugă Rezervare</h2>

    <?php if (!empty($error)): ?>
      <div style="color: red; margin-bottom: 20px; font-weight: bold;">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <label for="id_utilizator">ID Utilizator:</label>
      <input type="number" name="id_utilizator" id="id_utilizator" required>

      <label for="id_hotel">ID Hotel:</label>
      <input type="number" name="id_hotel" id="id_hotel" required>

      <label for="data_checkin">Data Check-in:</label>
      <input type="date" name="data_checkin" id="data_checkin" required>

      <label for="data_checkout">Data Check-out:</label>
      <input type="date" name="data_checkout" id="data_checkout" required>

      <button type="submit">Adaugă</button>
    </form>
  </div>

</body>

</html>
