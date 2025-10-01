<?php
session_start();
include("includes/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT tip FROM login WHERE user = ? AND password = ?");
    $stmt->bind_param("ss", $user, $pass);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($tip);
    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        $_SESSION['user'] = $user;
        $_SESSION['tip'] = $tip;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Utilizator invalid!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body style="background-image: url('imagini/login.png'); background-size: cover;">
    <form method="post" class="login-form">
        <h2>Login</h2>
        <input type="text" name="username" placeholder="Utilizator" required>
        <input type="password" name="password" placeholder="Parolă" required>
        <button type="submit">Login</button>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    </form>
</body>
</html>
