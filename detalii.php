<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['rol'], ['client', 'admin'])) {
  header("Location: login.php");
  exit();
}

$rol = $_SESSION['rol'];

if (!isset($_GET['id'])) {
  header("Location: destinatii.php");
  exit();
}

$id_destinatie = intval($_GET['id']);
$checkin = $_GET['checkin'] ?? '';
$nopti = isset($_GET['nopti']) ? intval($_GET['nopti']) : '';
$pret_min = isset($_GET['pret_min']) && $_GET['pret_min'] !== '' ? floatval($_GET['pret_min']) : null;
$pret_max = isset($_GET['pret_max']) && $_GET['pret_max'] !== '' ? floatval($_GET['pret_max']) : null;
$sortare = $_GET['sortare'] ?? '';
$action = $_GET['action'] ?? 'cauta';

$checkout = '';
if (!empty($checkin) && !empty($nopti)) {
  $checkout = date('Y-m-d', strtotime($checkin . " + $nopti days"));
}

$query = "
SELECT h.id AS id_hotel, h.nume AS nume_hotel, h.stele, h.imagine, MIN(o.pret_pe_noapte) AS pret_minim
FROM hoteluri h
JOIN oferte o ON h.id = o.id_hotel
WHERE h.id_destinatie = ?
";

if (!empty($checkin) && !empty($nopti)) {
  $query .= "
    AND NOT EXISTS (
        SELECT 1 FROM rezervari r
        WHERE r.id_hotel = h.id
        AND NOT (
            r.data_checkout <= ?
            OR r.data_checkin >= ?
        )
    )
    ";
}

$query .= " GROUP BY h.id ";

$having = [];
if ($pret_min !== null) {
  $having[] = "pret_minim >= $pret_min";
}
if ($pret_max !== null) {
  $having[] = "pret_minim <= $pret_max";
}

if (!empty($having)) {
  $query .= " HAVING " . implode(" AND ", $having);
}

if (!empty($sortare)) {
  if ($sortare === 'pret_crescator') {
    $query .= " ORDER BY pret_minim ASC";
  } elseif ($sortare === 'pret_descrescator') {
    $query .= " ORDER BY pret_minim DESC";
  } elseif ($sortare === 'stele_crescator') {
    $query .= " ORDER BY h.stele ASC";
  } elseif ($sortare === 'stele_descrescator') {
    $query .= " ORDER BY h.stele DESC";
  }
}

$stmt = $conn->prepare($query);

if (!empty($checkin) && !empty($nopti)) {
  $stmt->bind_param("iss", $id_destinatie, $checkin, $checkout);
} else {
  $stmt->bind_param("i", $id_destinatie);
}

$stmt->execute();
$result = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="ro">

<head>
  <meta charset="UTF-8">
  <title>Detalii Destinație</title>
  <link rel="stylesheet" href="dashboard.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    .filter-form {
      text-align: center;
      margin-top: 30px;
      margin-bottom: 40px;
    }

    .filter-form h2 {
      margin-bottom: 20px;
    }

    .filter-form form {
      display: flex;
      flex-direction: column;
      gap: 15px;
      align-items: center;
    }

    .linie-cautare,
    .linie-filtrare {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      justify-content: center;
    }

    .filter-form input[type="date"],
    .filter-form input[type="number"],
    .filter-form select,
    .filter-form button {
      padding: 10px 16px;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 16px;
      font-family: 'Poppins', sans-serif;
      height: 45px;
      box-sizing: border-box;
    }

    .filter-form input[type="number"] {
      width: 150px;
    }

    .filter-form select {
      width: 180px;
    }

    .filter-form button {
      background-color: #00796b;
      color: white;
      font-weight: bold;
      cursor: pointer;
    }

    .hoteluri-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
      gap: 30px;
      padding: 40px;
      max-width: 1200px;
      margin: 0 auto;
    }

    .hotel-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      text-align: center;
      display: flex;
      flex-direction: column;
      padding-bottom: 20px;
    }

    .hotel-img {
      width: 100%;
      height: 200px;
      object-fit: cover;
    }

    .hotel-info {
      padding: 20px;
      flex-grow: 1;
    }

    .hotel-info h3 {
      margin-bottom: 10px;
      color: #00796b;
    }

    .pret-minim {
      font-weight: bold;
      margin: 15px 0;
    }

    .stele {
      color: gold;
      font-size: 1.2rem;
      margin-bottom: 10px;
    }

    .btn-group {
      display: flex;
      justify-content: center;
      gap: 15px;
    }

    .btn-group a {
      text-decoration: none;
      background-color: #00796b;
      color: white;
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: 600;
      transition: background 0.3s;
    }

    .btn-group a:hover {
      background-color: #004d40;
    }

    #error-msg {
      color: #f44336;
      font-weight: bold;
      margin-bottom: 15px;
      font-size: 16px;
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

  <section class="hero">
    <div class="hero-content">
      <h1>Let's Discover The World Together 🌍</h1>
    </div>
  </section>

  <section class="filter-form">
    <div id="error-msg" style="display: none;"></div>
    <h2>Selectează perioada:</h2>
    <form method="GET" id="search-form">
      <input type="hidden" name="id" value="<?= $id_destinatie ?>">
      <div class="linie-cautare">
        <input type="date" name="checkin" value="<?= htmlspecialchars($checkin) ?>" min="<?= date('Y-m-d') ?>" required>
        <input type="number" name="nopti" value="<?= htmlspecialchars($nopti) ?>" min="1" max="30"
          placeholder="Număr nopți" required>
        <button type="submit" name="action" value="cauta">Caută</button>
        <button type="button" id="filtreaza-btn" onclick="arataFiltre()">Filtrează</button>
      </div>

      <div id="filtrare-section"
        style="display:<?= ($action === 'filtreaza' || !empty($pret_min) || !empty($pret_max) || !empty($sortare)) ? 'block' : 'none' ?>; margin-top: 20px;"
        class="linie-filtrare">
        <input type="number" name="pret_min" value="<?= htmlspecialchars($pret_min ?? '') ?>" min="0"
          placeholder="Preț minim">
        <input type="number" name="pret_max" value="<?= htmlspecialchars($pret_max ?? '') ?>" min="0"
          placeholder="Preț maxim">

        <select name="sortare">
          <option value="">Sortare</option>
          <option value="pret_crescator" <?= $sortare == 'pret_crescator' ? 'selected' : '' ?>>Preț crescător</option>
          <option value="pret_descrescator" <?= $sortare == 'pret_descrescator' ? 'selected' : '' ?>>Preț descrescător
          </option>
          <option value="stele_crescator" <?= $sortare == 'stele_crescator' ? 'selected' : '' ?>>Stele crescător</option>
          <option value="stele_descrescator" <?= $sortare == 'stele_descrescator' ? 'selected' : '' ?>>Stele descrescător
          </option>
        </select>

        <button type="submit" name="action" value="filtreaza">Aplică Filtre</button>
      </div>
    </form>
  </section>


  <script>
    function arataFiltre() {
      document.getElementById('filtrare-section').style.display = 'block';
      document.getElementById('filtreaza-btn').style.display = 'none';
    }

    function verificaData(id_hotel) {
      var checkin = document.querySelector('input[name="checkin"]').value;
      var nopti = document.querySelector('input[name="nopti"]').value;

      if (!checkin || !nopti) {
        var errorMsg = document.getElementById('error-msg');
        errorMsg.textContent = 'Te rugăm să selectezi data de check-in și numărul de nopți!';
        errorMsg.style.display = 'block';

        window.scrollTo({
          top: document.querySelector('.filter-form').offsetTop - 100,
          behavior: 'smooth'
        });
      } else {
        window.location.href = 'detalii_hotel.php?id=' + id_hotel + '&checkin=' + checkin + '&nopti=' + nopti;
      }
    }
  </script>

  <div class="hoteluri-container">
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="hotel-card">
        <img src="imagini/<?= htmlspecialchars($row['imagine']) ?>" alt="Hotel" class="hotel-img">
        <div class="hotel-info">
          <h3><?= htmlspecialchars($row['nume_hotel']) ?></h3>
          <div class="stele">
            <?php for ($i = 0; $i < $row['stele']; $i++)
              echo "⭐"; ?>
          </div>
          <div class="pret-minim">De la <?= number_format($row['pret_minim'], 2) ?> € / noapte</div>
          <div class="btn-group">
            <a href="#" onclick="verificaData(<?= $row['id_hotel'] ?>); return false;">Detalii</a>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>

</body>

</html>