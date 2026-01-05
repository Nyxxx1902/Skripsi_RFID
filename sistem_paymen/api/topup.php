<?php
include "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['uid']) && isset($_POST['jumlah'])) {
        $uid = $_POST['uid'];
        $jumlah = intval($_POST['jumlah']);

        $check = mysqli_query($conn, "SELECT saldo FROM users WHERE uid='$uid'");
        if (mysqli_num_rows($check) > 0) {
            $data = mysqli_fetch_assoc($check);
            $saldoLama = intval($data['saldo']);
            $saldoBaru = $saldoLama + $jumlah;

            $update = mysqli_query($conn, "UPDATE users SET saldo='$saldoBaru' WHERE uid='$uid'");
            if ($update) {
                // Simpan ke history
                mysqli_query($conn, "INSERT INTO history (uid, aksi, nominal) VALUES ('$uid', 'topup', $jumlah)");
                echo "Top-up Berhasil";
            } else {
                echo "Gagal Update";
            }
        } else {
            echo "UID tidak terdaftar";
        }
    } else {
        echo "Data tidak lengkap";
    }
} else {
    echo "Metode tidak didukung";
}
?>
