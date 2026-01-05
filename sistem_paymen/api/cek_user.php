<?php
header('Content-Type: text/plain; charset=utf-8');
include 'db.php';

if (empty($_GET['uid'])) {
    http_response_code(400);
    exit('ERROR');
}
$uid    = strtoupper(trim($_GET['uid']));
$uidEsc = mysqli_real_escape_string($conn, $uid);

$sql = "SELECT 1 FROM users WHERE uid='$uidEsc' LIMIT 1";
$res = mysqli_query($conn, $sql);
if (! $res) {
    http_response_code(500);
    exit('ERROR');
}
echo (mysqli_num_rows($res) > 0) ? 'ADA' : 'TIDAK';
$conn->close();
