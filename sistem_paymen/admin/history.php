<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}
require '../api/db.php';

$uid = isset($_GET['uid']) ? $_GET['uid'] : '';

// 🔁 AJAX MODE: return hanya isi tabel, tanpa HTML
if (isset($_GET['ajax']) && $_GET['ajax'] == 1 && !empty($uid)) {
    $sql = "SELECT * FROM history WHERE uid = ? ORDER BY waktu DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    $no = 1;
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$no}</td>
                <td>" . ucfirst($row['aksi']) . "</td>
                <td>Rp " . number_format($row['nominal']) . "</td>
                <td>" . date('d M Y H:i:s', strtotime($row['waktu'])) . "</td>
              </tr>";
        $no++;
    }

    if ($no === 1) {
        echo "<tr><td colspan='4'>Tidak ada riwayat transaksi.</td></tr>";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Transaksi - UID <?= htmlspecialchars($uid) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1f4037, #99f2c8);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: start;
            padding: 40px 20px;
            color: #fff;
        }

        .container {
            width: 100%;
            max-width: 960px;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            padding: 40px;
            animation: fadeIn 1s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            text-align: center;
            font-weight: 600;
            margin-bottom: 20px;
        }

        a.back-link {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #fff;
            background-color: #00c9a7;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        a.back-link:hover {
            background-color: #00a18b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: rgba(255,255,255,0.1);
            border-radius: 12px;
            overflow: hidden;
            margin-top: 20px;
        }

        th, td {
            padding: 14px 16px;
            text-align: center;
            font-size: 15px;
        }

        th {
            background-color: rgba(0,0,0,0.3);
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: rgba(255,255,255,0.08);
        }

        tr:hover {
            background-color: rgba(255,255,255,0.2);
        }

        @media screen and (max-width: 600px) {
            th, td {
                font-size: 13px;
                padding: 10px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a class="back-link" href="dashboard.php">🔙 Kembali ke Dashboard</a>
        <h2>📋 Riwayat Transaksi<br><span style="font-size: 0.8em;">UID: <?= htmlspecialchars($uid); ?></span></h2>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Aksi</th>
                    <th>Nominal</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody id="tabel-data">
                <!-- Data transaksi akan muncul di sini -->
            </tbody>
        </table>
    </div>

    <script>
        function loadRiwayat() {
            const uid = "<?= htmlspecialchars($uid); ?>";
            fetch("<?= $_SERVER['PHP_SELF']; ?>?uid=" + uid + "&ajax=1")
                .then(res => res.text())
                .then(data => {
                    document.getElementById("tabel-data").innerHTML = data;
                })
                .catch(err => {
                    console.error("Gagal mengambil data:", err);
                });
        }

        window.onload = loadRiwayat;
        setInterval(loadRiwayat, 1000);
    </script>
</body>
</html>
