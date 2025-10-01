<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Login - World Travel</title>
    <link rel="icon" href="imagini/logo.png" sizes="64x64" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="overlay"></div>
    <?php
session_start();
?>
<div class="login-box">
  <h2>Login</h2>

  <?php if (isset($_SESSION['error'])): ?>
  <div class="error-message">
    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
  </div>
<?php endif; ?>

        <form action="includes/auth.php" method="POST">
            <input type="text" name="username" placeholder="Utilizator" required>
            <input type="password" name="password" placeholder="Parolă" required>
            <button type="submit">Autentificare</button>
        </form>
    </div>
</body>
</html>
