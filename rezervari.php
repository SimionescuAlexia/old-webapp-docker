<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['rol'], ['client', 'admin'])) {
  header("Location: login.php");
  exit();
}

$rol = $_SESSION['rol'];
$user_email = $_SESSION['user'];

$stmt = $conn->prepare("SELECT id FROM utilizatori WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$id_utilizator = $user['id'];

$query = "
SELECT r.id, d.oras, d.tara, h.nume AS hotel, 
r.data_checkin, 
r.data_checkout,
DATEDIFF(r.data_checkout, r.data_checkin) AS nopti,
r.total AS pret_total
FROM rezervari r
JOIN hoteluri h ON r.id_hotel = h.id
JOIN destinatii d ON h.id_destinatie = d.id
WHERE r.id_utilizator = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_utilizator);
$stmt->execute();
$reservari = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ro">

<head>
  <meta charset="UTF-8">
  <title>Rezervările mele</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    .rez-container {
      max-width: 1000px;
      margin: 50px auto;
      background: white;
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }

    h2 {
      text-align: center;
      color: #00796b;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 25px;
    }

    th,
    td {
      padding: 12px;
      text-align: center;
      border-bottom: 1px solid #ccc;
    }

    th {
      background-color: #00796b;
      color: white;
      font-size: 16px;
    }

    .continue-explore {
      text-align: center;
      margin-top: 40px;
      animation: fadeIn 1s ease forwards;
      opacity: 0;
    }

    @keyframes fadeIn {
      to {
        opacity: 1;
      }
    }

    .continue-explore a {
      display: inline-block;
      margin-top: 15px;
      padding: 12px 24px;
      background-color: #00796b;
      color: white;
      border-radius: 8px;
      font-weight: bold;
      text-decoration: none;
    }

    .continue-explore a:hover {
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

  <div class="rez-container">
    <h2>Rezervările mele</h2>
    <table>
      <tr>
        <th>Hotel</th>
        <th>Locație</th>
        <th>Check-in</th>
        <th>Check-out</th>
        <th>Nopți</th>
        <th>Preț Total (€)</th>
      </tr>
      <?php while ($row = $reservari->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['hotel']) ?></td>
          <td><?= htmlspecialchars($row['oras'] . ', ' . $row['tara']) ?></td>
          <td><?= htmlspecialchars($row['data_checkin']) ?></td>
          <td><?= htmlspecialchars($row['data_checkout']) ?></td>
          <td><?= (int) $row['nopti'] ?></td>
          <td><?= number_format((float) $row['pret_total'], 2) ?></td>
        </tr>
      <?php endwhile; ?>
    </table>
  </div>

  <?php if (isset($_GET['rez']) && $_GET['rez'] === 'ok'): ?>
    <div class="continue-explore">
      <h3>🎯 Vrei să continui să explorezi alte oferte?</h3>
      <a href="destinatii.php">Vezi Destinațiile</a>
    </div>
  <?php endif; ?>

</body>

</html>
