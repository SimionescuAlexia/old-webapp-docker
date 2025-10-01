<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user = $_POST['username'];
    $pass = md5($_POST['password']); 

    $query = "SELECT rol FROM utilizatori WHERE email = ? AND parola = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $user, $pass);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $_SESSION['user'] = $user;
        $_SESSION['email'] = $user; 
        $row = $result->fetch_assoc();
        $_SESSION['rol'] = $row['rol'];

        header("Location:/dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Utilizator sau parolă incorectă!";
        header("Location:../login.php");
        exit();
    }
}
?>
