<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require '../api/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_update'])) {
    $uid   = trim($_POST['uid'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $nama  = trim($_POST['nama'] ?? '');

    if (empty($uid) || empty($email) || empty($nama)) {
        echo json_encode(['status' => 'error', 'msg' => 'Semua data (UID, email, nama) harus diisi!']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'msg' => 'Format email tidak valid!']);
        exit;
    }

    // ✅ Cek apakah email sudah dipakai oleh UID lain
    $check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND uid != ?");
    $check_email->bind_param("ss", $email, $uid);
    $check_email->execute();
    $check_result = $check_email->get_result();

    if ($check_result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'msg' => 'Email sudah digunakan oleh UID lain!']);
        exit;
    }

    // ✅ Update email dan nama jika valid
    $stmt = $conn->prepare("UPDATE users SET email = ?, nama = ? WHERE uid = ?");
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'msg' => 'Query gagal: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("sss", $email, $nama, $uid);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'msg' => "✅ Data berhasil diperbarui untuk UID <b>$uid</b>"]);
    } else {
        // Cek apakah UID ada tapi datanya tidak berubah
        $check = $conn->prepare("SELECT id FROM users WHERE uid = ?");
        $check->bind_param("s", $uid);
        $check->execute();
        $result = $check->get_result();
        if ($result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'msg' => 'UID tidak ditemukan!']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Tidak ada perubahan data (nama/email sama seperti sebelumnya).']);
        }
    }
    exit;
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Request tidak valid.']);
}
