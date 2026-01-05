<?php
include "db.php";

if (isset($_GET['uid'])) {
    $uid = strtoupper(trim($_GET['uid']));
    
    $query = mysqli_query($conn, "SELECT saldo FROM users WHERE uid='$uid'");
    
    if (!$query) {
        echo "SQL Error: " . mysqli_error($conn);
    } elseif (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        echo $data['saldo'];
    } else {
        echo "TIDAK ADA";
    }
} else {
    echo "ERROR";
}
?>
