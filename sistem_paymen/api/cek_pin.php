<?php
header('Content-Type: text/plain; charset=utf-8');
include 'db.php';

if (empty($_GET['uid']) || !isset($_GET['pin'])) {
    http_response_code(400);
    exit('ERROR');
}
$uid   = strtoupper(trim($_GET['uid']));
$pinIn = trim($_GET['pin']);
$uidEsc = mysqli_real_escape_string($conn, $uid);

$sql = "SELECT pin FROM users WHERE uid='$uidEsc' LIMIT 1";
$res = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($res);

if ($row && $row['pin'] === $pinIn) {
    echo 'VALID';
} else {
    echo 'INVALID';
}
$conn->close();
