<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard RFID Cinematic</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
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
            max-width: 80%;
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
        a.logout {
            float: right;
            text-decoration: none;
            background-color: #ff4d4d;
            padding: 10px 20px;
            border-radius: 12px;
            color: white;
            transition: background 0.3s ease;
        }
        a.logout:hover { background-color: #e60000; }
        form {
            margin-top: 30px;
            text-align: center;
        }
        input[type="text"], input[type="email"] {
            padding: 12px 15px;
            border-radius: 12px;
            border: none;
            width: 250px;
            max-width: 80%;
            outline: none;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            margin-bottom: 10px;
        }
        button {
            padding: 12px 20px;
            border: none;
            border-radius: 12px;
            background-color: #00c9a7;
            color: white;
            font-weight: bold;
            cursor: pointer;
            margin-left: 10px;
            transition: background 0.3s ease;
        }
        button:hover { background-color: #00a18b; }
        .action-link {
            color: #00ffd5;
            text-decoration: none;
            font-weight: bold;
        }
        .action-link:hover { text-decoration: underline; }
        @media screen and (max-width: 600px) {
            input[type="text"], input[type="email"], button {
                width: 100%;
                margin: 5px 0;
            }
            .logout {
                float: none;
                display: block;
                margin: 10px auto;
                text-align: center;
            }
        }
        #update-result {
            font-weight: bold;
            min-height: 24px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a class="logout" href="logout.php">Logout</a>
        <h2>🎉 Selamat datang, <?= htmlspecialchars($_SESSION['username']); ?>!</h2>

        <h3 style="text-align:center; margin-top:10px;">👤 Update Data Pengguna</h3>
        <form id="update-user-form" style="text-align:center; margin-top:5px;">
            <input type="text" name="uid" id="uid" placeholder="UID" required>
            <input type="text" name="nama" id="nama" placeholder="Nama baru" required>
            <input type="email" name="email" id="email" placeholder="Email baru" required>
            <button type="submit">Update Data</button>
        </form>
        <div id="update-result" style="text-align:center; margin-top:5px;"></div>

        <form id="search-form">
            <input type="text" name="search" id="search" placeholder="Cari UID...">
            <button type="submit">Cari</button>
        </form>

        <h3 style="text-align:center; margin-top:5px;">📋 Daftar UID Terdaftar</h3>
        <div id="tabel-data">Memuat data...</div>
    </div>

    <script>
        function loadTable() {
            const search = document.getElementById('search').value;
            fetch('data.php?search=' + encodeURIComponent(search))
                .then(res => res.text())
                .then(html => {
                    document.getElementById('tabel-data').innerHTML = html;
                });
        }

        document.getElementById('search-form').addEventListener('submit', function(e) {
            e.preventDefault();
            loadTable();
        });

        loadTable();
        setInterval(loadTable, 100000);

        function showPopupUpdate(msg, success) {
            const el = document.getElementById('update-result');
            el.style.color = success ? "#00ffae" : "#ff6e6e";
            el.innerHTML = msg;
            setTimeout(() => el.innerHTML = '', 2500);
        }

        document.getElementById('update-user-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = this;
            const data = new FormData(form);
            data.append('ajax_update', '1');

            fetch('update_email.php', { // ganti ke update_user.php
                method: 'POST',
                body: data
            })
            .then(res => res.json())
            .then(res => {
                showPopupUpdate(res.msg, res.status === 'success');
                if (res.status === 'success') {
                    loadTable();
                    form.reset();
                }
            })
            .catch(() => {
                showPopupUpdate('Gagal koneksi ke server!', false);
            });
        });
    </script>
</body>
</html>