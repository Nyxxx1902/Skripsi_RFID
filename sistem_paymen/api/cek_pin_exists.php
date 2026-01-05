<?php
header('Content-Type: text/plain; charset=utf-8');
include 'db.php';

if (empty($_GET['uid'])) {
    http_response_code(400);
    exit('ERROR');
}
$uid = strtoupper(trim($_GET['uid']));
$uidEsc = mysqli_real_escape_string($conn, $uid);

$sql = "SELECT pin FROM users WHERE uid='$uidEsc' LIMIT 1";
$res = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($res);

if ($row && $row['pin'] !== null && $row['pin'] !== '') {
    echo 'ADA_PIN';
} else {
    echo 'TIDAK_PIN';
}
$conn->close();
