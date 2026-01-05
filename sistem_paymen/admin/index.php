<?php
session_start();
if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistem Transaksi RFID MLM</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease-in-out;
        }

        body {
            margin: 0;
            padding: 0;
            background: url('https://images.unsplash.com/photo-1556740749-887f6717d7e4?auto=format&fit=crop&w=1500&q=80') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-container {
            backdrop-filter: blur(12px);
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            padding: 40px;
            width: 100%;
            max-width: 420px;
            text-align: center;
            color: #fff;
            animation: fadeIn 1.2s ease;
        }

        .login-container img {
            width: 80px;
            margin-bottom: 20px;
            animation: fadeIn 1.5s ease;
        }

        .login-container h2 {
            margin-bottom: 20px;
            font-weight: 600;
            letter-spacing: 1px;
            font-size: 24px;
        }

        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            margin: 10px 0 20px;
            border: none;
            border-radius: 12px;
            outline: none;
            background-color: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 14px;
            backdrop-filter: blur(5px);
            box-shadow: inset 0 0 10px rgba(255, 255, 255, 0.1);
        }

        .login-container input::placeholder {
            color: #ddd;
        }

        .login-container input:focus {
            background-color: rgba(255, 255, 255, 0.25);
        }

        .login-container button {
            padding: 12px 25px;
            background-color: #00c9a7;
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(0, 201, 167, 0.4);
        }

        .login-container button:hover {
            background-color: #00b494;
            transform: scale(1.05);
        }

        .error-msg {
            margin-top: 15px;
            color: #ff4d4d;
            font-weight: 500;
            animation: fadeIn 1s ease;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="https://cdn-icons-png.flaticon.com/512/2602/2602527.png" alt="MLM Logo">
        <h2>Login Sistem MLM RFID</h2>
        <form method="POST" action="login.php">
            <input type="text" name="username" placeholder="Masukkan Username" required>
            <input type="password" name="password" placeholder="Masukkan Password" required>
            <button type="submit">Login</button>
        </form>
        <?php if (isset($_GET['error'])): ?>
            <div class="error-msg">⚠️ Username atau password salah!</div>
        <?php endif; ?>
    </div>
</body>
</html>
