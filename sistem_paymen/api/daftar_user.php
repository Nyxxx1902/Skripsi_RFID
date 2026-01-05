<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uid = trim($_POST['uid']);
    $pin = isset($_POST['pin']) ? trim($_POST['pin']) : '0000';  // default PIN

    if (!$uid) {
        echo "GAGAL: UID tidak boleh kosong";
        exit;
    }

    $stmt = $conn->prepare(
      "INSERT INTO users (uid, saldo, pin) VALUES (?, 0, ?)"
    );
    $stmt->bind_param("ss", $uid, $pin);

    if ($stmt->execute()) {
        echo "BERHASIL";
    } else {
        echo "GAGAL: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "ERROR_METHOD";
}

$conn->close();
?>
