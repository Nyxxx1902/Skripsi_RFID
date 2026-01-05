<?php
include "db.php";
header('Content-Type: application/json');

if (isset($_GET['uid'])) {
    $uid = mysqli_real_escape_string($conn, $_GET['uid']);
    $result = mysqli_query($conn, "SELECT * FROM history WHERE uid='$uid' ORDER BY waktu DESC");

    if ($result) {
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        echo json_encode($data);
    } else {
        echo json_encode(["error" => "Query gagal"]);
    }
} else {
    echo json_encode(["error" => "UID belum dikirim"]);
}
?>
