<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['rol'] !== 'admin') {
  header('Location: login.php');
  exit();
}

$rol = $_SESSION['rol'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
  $id = intval($_POST['id']);
  $tara = trim($_POST['tara']);
  $oras = trim($_POST['oras']);

  if (!empty($tara) && !empty($oras)) {
    if (isset($_FILES['imagine']) && $_FILES['imagine']['error'] === UPLOAD_ERR_OK) {
      $fileTmpPath = $_FILES['imagine']['tmp_name'];
      $fileName = $_FILES['imagine']['name'];
      $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
      $allowedExtensions = ['jpg', 'jpeg', 'png'];

      if (in_array($fileExtension, $allowedExtensions)) {
        $newFileName = uniqid() . '.' . $fileExtension;
        $uploadDir = 'imagini/';
        $destPath = $uploadDir . $newFileName;
        move_uploaded_file($fileTmpPath, $destPath);

        $stmt = $conn->prepare("UPDATE destinatii SET tara=?, oras=?, imagine=? WHERE id=?");
        $stmt->bind_param("sssi", $tara, $oras, $newFileName, $id);
        $stmt->execute();
      }
    } else {
      $stmt = $conn->prepare("UPDATE destinatii SET tara=?, oras=? WHERE id=?");
      $stmt->bind_param("ssi", $tara, $oras, $id);
      $stmt->execute();
    }
  }
}

$search = '';
if (isset($_GET['search'])) {
  $search = trim($_GET['search']);
  $stmt = $conn->prepare("SELECT * FROM destinatii WHERE tara LIKE ? OR oras LIKE ?");
  $param = "%{$search}%";
  $stmt->bind_param("ss", $param, $param);
  $stmt->execute();
  $result = $stmt->get_result();
} else {
  $result = $conn->query("SELECT * FROM destinatii");
}
?>

<!DOCTYPE html>
<html lang="ro">

<head>
  <meta charset="UTF-8">
  <title>Modifică Destinații</title>
  <link rel="stylesheet" href="dashboard.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    .edit-container {
      max-width: 1200px;
      margin: 40px auto;
      background: white;
      padding: 25px;
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .edit-container h2 {
      text-align: center;
      margin-bottom: 30px;
      color: #00796b;
    }

    .search-bar {
      text-align: center;
      margin-bottom: 20px;
    }

    .search-bar input[type="text"] {
      padding: 10px;
      width: 300px;
      border-radius: 8px;
      border: 1px solid #ccc;
      margin-right: 10px;
    }

    .search-bar button {
      padding: 10px 20px;
      background-color: #00796b;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    .search-bar button:hover {
      background-color: #004d40;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
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
    }

    input[type="text"],
    input[type="file"] {
      width: 90%;
      padding: 8px;
      margin: 5px 0;
      border-radius: 8px;
      border: 1px solid #ccc;
    }

    button[type="submit"] {
      background-color: #00796b;
      color: white;
      border: none;
      padding: 10px 20px;
      font-size: 14px;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s;
      margin-top: 5px;
    }

    button[type="submit"]:hover {
      background-color: #004d40;
    }

    img {
      width: 100px;
      height: 70px;
      object-fit: cover;
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
    <h2>Modifică Destinații</h2>

    <div class="search-bar">
      <form method="GET">
        <input type="text" name="search" placeholder="Caută țară sau oraș..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Caută</button>
      </form>
    </div>

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
            <form method="POST" enctype="multipart/form-data">
              <td><?= $row['id'] ?><input type="hidden" name="id" value="<?= $row['id'] ?>"></td>
              <td><input type="text" name="tara" value="<?= htmlspecialchars($row['tara']) ?>" required></td>
              <td><input type="text" name="oras" value="<?= htmlspecialchars($row['oras']) ?>" required></td>
              <td>
                <img src="imagini/<?= htmlspecialchars($row['imagine']) ?>" alt="Imagine">
                <input type="file" name="imagine">
              </td>
              <td><button type="submit" name="update">Salvează</button></td>
            </form>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

</body>

</html>