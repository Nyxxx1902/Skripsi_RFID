<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require '../api/db.php';

$table = 'users';
$search = $_GET['search'] ?? '';
$like = "%$search%";
$like_sql = mysqli_real_escape_string($conn, $like);
$sql = "SELECT * FROM $table WHERE uid LIKE '$like_sql' OR nama LIKE '$like_sql' ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>

<style>
    .data-container {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(10px);
        overflow-x: auto;
        margin-top: 30px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 15px;
        color: #fff;
        min-width: 700px;
    }

    th, td {
        padding: 14px 16px;
        text-align: center;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    th {
        background: rgba(0, 0, 0, 0.3);
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    tr:nth-child(even) {
        background-color: rgba(255, 255, 255, 0.04);
    }

    tr:hover {
        background-color: rgba(0, 255, 213, 0.1);
    }

    .action-link {
        color: #00ffd5;
        font-weight: bold;
        text-decoration: none;
    }

    .action-link:hover {
        text-decoration: underline;
        color: #1de9b6;
    }

    @media screen and (max-width: 768px) {
        table, thead, tbody, th, td, tr {
            display: block;
        }
        th {
            text-align: left;
        }
        tr {
            margin-bottom: 15px;
            border-bottom: 1px solid #444;
        }
        td {
            text-align: left;
            padding-left: 40%;
            position: relative;
        }
        td::before {
            content: attr(data-label);
            position: absolute;
            left: 0;
            width: 40%;
            padding-left: 15px;
            font-weight: bold;
            text-transform: uppercase;
            color: #aaa;
        }
    }
</style>

<div class="data-container">
    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>UID</th>
                <th>Email</th>
                <th>Saldo</th>
                <th>Tanggal Daftar</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
            <tr>
                <td data-label="Nama"><?= htmlspecialchars($row['nama']); ?></td>
                <td data-label="UID"><?= htmlspecialchars($row['uid']); ?></td>
                <td data-label="Email"><?= htmlspecialchars($row['email']); ?></td>
                <td data-label="Saldo">Rp <?= number_format($row['saldo']); ?></td>
                <td data-label="Tanggal"><?= date('d M Y H:i', strtotime($row['created_at'])); ?></td>
                <td data-label="Aksi">
                    <a class="action-link" href="history.php?uid=<?= urlencode($row['uid']); ?>">📜 Riwayat</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
