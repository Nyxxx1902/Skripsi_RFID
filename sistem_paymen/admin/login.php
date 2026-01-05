<?php
session_start();
require '../api/db.php';

$username = $_POST['username'];
$password = $_POST['password'];

// Ganti nama tabel jadi admin_users
$sql = "SELECT * FROM admin_users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($password, $row['password'])) {
        $_SESSION['login'] = true;
        $_SESSION['username'] = $row['username'];
        header("Location: dashboard.php");
        exit;
    }
}

header("Location: index.php?error=1");
exit;
?>
