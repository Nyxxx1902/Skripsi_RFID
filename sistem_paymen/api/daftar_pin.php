<?php
header('Content-Type: text/plain; charset=utf-8');
include 'db.php';

$uid = isset($_POST['uid'])  ? strtoupper(trim($_POST['uid'])) : '';
$pin = isset($_POST['pin'])  ? trim($_POST['pin'])          : '';

if (!$uid || !$pin) {
    http_response_code(400);
    exit('ERROR');
}
if (!preg_match('/^[0-9]{4,6}$/', $pin)) {
    http_response_code(400);
    exit('ERROR');
}

$uidEsc = mysqli_real_escape_string($conn, $uid);
$pinEsc = mysqli_real_escape_string($conn, $pin);

$sql = "UPDATE users SET pin='$pinEsc' WHERE uid='$uidEsc'";
if (!mysqli_query($conn, $sql)) {
    http_response_code(500);
    exit('ERROR');
}
echo (mysqli_affected_rows($conn) > 0) ? 'BERHASIL' : 'GAGAL';
$conn->close();
