<?php
$password = "yolo112233"; // Ganti sama password yang lo mau
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password asli: $password<br>";
echo "Hash-nya: $hash";
?>
