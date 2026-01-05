<?php
include "db.php";

if (isset($_GET['uid']) && isset($_GET['jumlah'])) {
    $uid = strtoupper(trim($_GET['uid']));
    $jumlah = intval($_GET['jumlah']);

    $query = mysqli_query($conn, "SELECT saldo FROM users WHERE uid='$uid'");
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        $saldo = intval($data['saldo']);

        if ($saldo >= $jumlah) {
            $newSaldo = $saldo - $jumlah;
            $update = mysqli_query($conn, "UPDATE users SET saldo=$newSaldo WHERE uid='$uid'");
            if ($update) {
                // Simpan ke history
                mysqli_query($conn, "INSERT INTO history (uid, aksi, nominal) VALUES ('$uid', 'bayar', $jumlah)");
                echo "Bayar Berhasil";
            } else {
                echo "Gagal Update";
            }
        } else {
            echo "Saldo Kurang";
        }
    } else {
        echo "UID Tidak Ditemukan";
    }
} else {
    echo "Param Kurang";
}
?>
