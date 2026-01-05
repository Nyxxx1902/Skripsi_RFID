<?php
include "db.php"; // ini udah otomatis konek ke rfid_payment

// Tes query
$result = mysqli_query($conn, "SELECT * FROM users");

if (!$result) {
    echo "Query error: " . mysqli_error($conn);
} else {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "UID: " . $row['uid'] . " | Saldo: " . $row['saldo'] . "<br>";
    }
}
?>
