<?php
require 'db.php'; // File koneksi database di folder yang sama dengan script ini
require 'PHPMailer-6.10.0/src/PHPMailer.php';
require 'PHPMailer-6.10.0/src/SMTP.php';
require 'PHPMailer-6.10.0/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ambil UID dari POST/GET
$uid = trim($_POST['uid'] ?? $_GET['uid'] ?? '');
if (!$uid) {
    echo 'ERROR';
    exit;
}

// Ambil email dari database berdasarkan UID (prepared statement, biar aman)
$stmt = $conn->prepare("SELECT email FROM users WHERE uid = ? LIMIT 1");
$stmt->bind_param("s", $uid);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $email = $row['email'];
} else {
    echo 'NO_EMAIL';
    exit;
}

if (!$email) {
    echo 'NO_EMAIL';
    exit;
}

// Generate kode OTP 6 digit
$otp = rand(100000, 999999);

// Simpan OTP ke database (dan expired 5 menit dari sekarang)
$stmt2 = $conn->prepare("UPDATE users SET otp = ?, otp_expired = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE uid = ?");
$stmt2->bind_param("ss", $otp, $uid);
$stmt2->execute();
$stmt2->close();

// Kirim OTP ke email dengan PHPMailer
$mail = new PHPMailer(true);
try {
    // Konfigurasi SMTP Gmail (ganti sesuai akun pengirimmu!)
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'jrjuniors111@gmail.com'; // GANTI dengan email pengirim
    $mail->Password = 'tdrpfygvvjdqtzku'; // GANTI dengan App Password Gmail, **BUKAN password Gmail biasa!**
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('jrjuniors111@gmail.com', 'Sistem OTP');
    $mail->addAddress($email);
    $mail->Subject = 'Kode OTP Sistem';
    $mail->Body = "Kode OTP anda: $otp\n\nBerlaku 5 menit.";

    $mail->send();
    echo 'OTP_SENT';
} catch (Exception $e) {
    // Untuk debugging: echo 'OTP_FAILED: ' . $mail->ErrorInfo;
    echo 'OTP_FAILED';
}

$stmt->close();
$conn->close();
?>
