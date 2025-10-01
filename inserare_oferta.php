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
  $titlu = trim($_POST['titlu']);
  $descriere = trim($_POST['descriere']);
  $pret_pe_noapte = floatval($_POST['pret_pe_noapte']);
  $id_hotel = intval($_POST['id_hotel']);

  if (empty($titlu) || empty($descriere) || $pret_pe_noapte <= 0 || $id_hotel <= 0) {
    $error = "Te rugăm să completezi toate câmpurile corect.";
  } else {
    $stmt = $conn->prepare("INSERT INTO oferte (titlu, descriere, pret_pe_noapte, id_hotel) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdi", $titlu, $descriere, $pret_pe_noapte, $id_hotel);
    $stmt->execute();

    header('Location: destinatii.php');
    exit();
  }
}
?>
<!DOCTYPE html>
<html lang="ro">

<head>
  <meta charset="UTF-8">
  <title>Adaugă Ofertă</title>
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
    .form-container textarea,
    .form-container input[type="number"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border-radius: 8px;
      border: 1px solid #ccc;
    }

    .form-container textarea {
      resize: vertical;
      height: 100px;
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

  <div class="form-container">
    <h2 style="margin-bottom: 20px; color: #00796b;">Adaugă Ofertă</h2>

    <?php if (!empty($error)): ?>
      <div style="color: red; margin-bottom: 20px; font-weight: bold;">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <label for="titlu">Titlu:</label>
      <input type="text" name="titlu" id="titlu" required>

      <label for="descriere">Descriere:</label>
      <textarea name="descriere" id="descriere" required></textarea>

      <label for="pret_pe_noapte">Preț pe noapte (€):</label>
      <input type="number" step="0.01" name="pret_pe_noapte" id="pret_pe_noapte" required>

      <label for="id_hotel">ID Hotel:</label>
      <input type="number" name="id_hotel" id="id_hotel" required>

      <button type="submit">Adaugă</button>
    </form>
  </div>

</body>

</html>