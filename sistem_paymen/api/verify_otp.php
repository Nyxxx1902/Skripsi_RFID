<?php
require 'db.php';

$uid = isset($_POST['uid']) ? trim($_POST['uid']) : '';
$otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';

if (!$uid || !$otp) { echo "OTP_INVALID"; exit; }

$res = mysqli_query($conn, "SELECT otp, otp_expired FROM users WHERE uid='$uid'");
$row = mysqli_fetch_assoc($res);

if ($row && $row['otp'] == $otp && strtotime($row['otp_expired']) > time()) {
    // Hapus OTP setelah berhasil supaya tidak reuse
    mysqli_query($conn, "UPDATE users SET otp=NULL, otp_expired=NULL WHERE uid='$uid'");
    echo "OTP_VALID";
} else {
    echo "OTP_INVALID";
}
?>
