<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['rol'], ['client', 'admin'])) {
  header("Location: login.php");
  exit();
}

$rol = $_SESSION['rol'];

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$pret_min = isset($_GET['min_pret']) ? floatval($_GET['min_pret']) : null;
$pret_max = isset($_GET['max_pret']) ? floatval($_GET['max_pret']) : null;
$sortare = isset($_GET['sortare']) ? $_GET['sortare'] : '';

$query = "SELECT d.id, d.tara, d.oras, d.imagine, MIN(o.pret_pe_noapte) AS pret_minim
          FROM destinatii d
          LEFT JOIN hoteluri h ON d.id = h.id_destinatie
          LEFT JOIN oferte o ON h.id = o.id_hotel
          WHERE 1 ";

$params = [];
$types = "";

if (!empty($search)) {
  $query .= "AND (d.tara LIKE ? OR d.oras LIKE ?) ";
  $searchParam = "%{$search}%";
  $params[] = $searchParam;
  $params[] = $searchParam;
  $types .= "ss";
}

if (!empty($pret_min)) {
  $query .= "AND o.pret_pe_noapte >= ? ";
  $params[] = $pret_min;
  $types .= "d";
}

if (!empty($pret_max)) {
  $query .= "AND o.pret_pe_noapte <= ? ";
  $params[] = $pret_max;
  $types .= "d";
}

$query .= "GROUP BY d.id ";

if ($sortare === 'crescator') {
  $query .= "ORDER BY pret_minim ASC";
} elseif ($sortare === 'descrescator') {
  $query .= "ORDER BY pret_minim DESC";
}

$stmt = $conn->prepare($query);

if (!empty($params)) {
  $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ro">

<head>
  <meta charset="UTF-8">
  <title>Destinații - Client</title>
  <link rel="stylesheet" href="dashboard.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    .search-bar {
      max-width: 900px;
      margin: 40px auto 20px auto;
      display: flex;
      gap: 10px;
      justify-content: center;
      flex-wrap: wrap;
    }

    .search-bar input[type="text"] {
      padding: 10px;
      width: 300px;
      border: 1px solid #ccc;
      border-radius: 6px;
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

    .filter-options {
      display: none;
      margin-top: 15px;
      text-align: center;
    }

    .filter-options input,
    .filter-options select {
      margin: 8px;
      padding: 8px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }

    .destinatii-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 30px;
      justify-items: center;
      padding: 40px 60px;
      max-width: 1200px;
      margin: 0 auto 60px auto;
    }

    .destinatie-card {
      width: 280px;
      background: white;
      border-radius: 12px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      cursor: pointer;
      transition: transform 0.2s ease-in-out;
    }

    .destinatie-card:hover {
      transform: scale(1.03);
    }

    .destinatie-card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
    }

    .destinatie-info {
      padding: 15px;
      flex-grow: 1;
      text-align: center;
    }

    .destinatie-info h3 {
      margin: 0 0 5px;
      color: #00796b;
    }

    .pret-minim {
      color: #444;
      font-size: 0.9rem;
      font-weight: 600;
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

  <form class="search-bar" method="GET" action="">
    <input type="text" name="search" placeholder="Caută oraș sau țară" value="<?= htmlspecialchars($search) ?>">
    <select name="sortare">
      <option value="">Sortează după preț</option>
      <option value="crescator" <?= ($sortare == 'crescator') ? 'selected' : '' ?>>Preț crescător</option>
      <option value="descrescator" <?= ($sortare == 'descrescator') ? 'selected' : '' ?>>Preț descrescător</option>
    </select>
    <button type="submit">Caută</button>
  </form>

  <div class="destinatii-container">
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="destinatie-card" onclick="location.href='detalii.php?id=<?= $row['id'] ?>'">
        <img src="imagini/<?= htmlspecialchars($row['imagine']) ?>" alt="Destinatie">
        <div class="destinatie-info">
          <h3><?= htmlspecialchars($row['oras']) ?>, <?= htmlspecialchars($row['tara']) ?></h3>
          <div class="pret-minim">de la
            <?= $row['pret_minim'] ? number_format($row['pret_minim'], 2) . ' € / noapte' : 'N/A' ?></div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>

</body>

</html>