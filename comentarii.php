<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['rol'], ['client', 'admin'])) {
  header("Location: login.php");
  exit();
}

$rol = $_SESSION['rol'];

$comentarii_query = "SELECT u.nume, c.text, c.data_adaugarii FROM comentarii c JOIN utilizatori u ON c.id_utilizator = u.id ORDER BY c.data_adaugarii DESC";
$comentarii_result = $conn->query($comentarii_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['comentariu'])) {
  $comentariu = trim($_POST['comentariu']);
  $email = $_SESSION['user'];
  $get_user = $conn->prepare("SELECT id FROM utilizatori WHERE email = ?");
  $get_user->bind_param("s", $email);
  $get_user->execute();
  $res = $get_user->get_result();
  if ($user = $res->fetch_assoc()) {
    $id_user = $user['id'];
    $insert = $conn->prepare("INSERT INTO comentarii (id_utilizator, text) VALUES (?, ?)");
    $insert->bind_param("is", $id_user, $comentariu);
    $insert->execute();
    header("Location: comentarii.php");
    exit();
  }
}
?>

<!DOCTYPE html>
<html lang="ro">

<head>
  <meta charset="UTF-8">
  <title>De ce să ne alegi?</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    .intro-section {
      text-align: center;
      padding: 40px 20px;
      background-color: #e0f2f1;
    }

    .comentariu-form {
      max-width: 800px;
      margin: 30px auto;
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    textarea {
      width: 100%;
      height: 160px;
      padding: 15px;
      border-radius: 8px;
      border: 1px solid #ccc;
      resize: vertical;
      font-size: 16px;
    }

    button[type="submit"] {
      background-color: #00796b;
      color: white;
      border: none;
      padding: 15px 25px;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
      margin-top: 12px;
      font-size: 16px;
    }

    label[for="comentariu"] {
      font-size: 18px;
      display: block;
      margin-bottom: 10px;
    }

    .comentarii-container {
      max-width: 800px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .comentariu-card {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      padding: 20px;
      margin-bottom: 20px;
    }

    .comentariu-card h4 {
      color: #00796b;
      margin-bottom: 5px;
    }

    .comentariu-card small {
      color: #666;
      display: block;
      margin-bottom: 10px;
    }

    .intro-section h2 {
      margin-bottom: 20px;
      font-size: 30px;
      font-weight: bold;
      color: #0d6a6a;
    }

    .intro-section p {
      margin-top: 0;
      font-size: 20px;
      line-height: 1.6;
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

  <section class="intro-section">
    <h2>De ce să alegi World Travel?</h2>

    <p>
      La World Travel credem că fiecare vacanță trebuie să fie specială. De aceea, alegem cu grijă destinațiile, colaborăm
      cu parteneri de încredere și ne asigurăm că fiecare detaliu este pus la punct.
      Oferim nu doar călătorii, ci experiențe de neuitat. Descoperă mai jos impresiile celor care au ales să
      călătorească alături de noi!
    </p>

  </section>

  <div class="comentariu-form">
    <form method="POST">
      <label for="comentariu"><strong>Lasă un comentariu:</strong></label>
      <textarea name="comentariu" id="comentariu" required></textarea>
      <button type="submit">Trimite</button>
    </form>
  </div>

  <section class="comentarii-container">
    <?php while ($row = $comentarii_result->fetch_assoc()): ?>
      <div class="comentariu-card">
        <h4><?= htmlspecialchars($row['nume']) ?></h4>
        <small><?= date('d.m.Y', strtotime($row['data_adaugarii'])) ?></small>
        <p><?= nl2br(htmlspecialchars($row['text'])) ?></p>
      </div>
    <?php endwhile; ?>
  </section>

</body>

</html>