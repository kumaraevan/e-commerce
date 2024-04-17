<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['role'])) {
        $role = $_POST['role'];
        switch ($role) {
            case 'seller':
                header('Location: seller_dashboard.php');
                exit();
            case 'buyer':
                header('Location: index.php');
                exit();
            case 'admin':
                header('Location: admin_dashboard.php');
                exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Role Selection</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .card { background: white; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); text-align: center; padding: 20px; width: 200px; margin: 10px; cursor: pointer; transition: transform 0.3s ease; }
        .card:hover { transform: translateY(-10px); }
        .card img { width: 100px; height: auto; margin-bottom: 20px; }
        .card-title { color: #333; font-size: 20px; margin-bottom: 10px; }
        .card form { height: 100%; position: relative; }
        .card input[type="submit"] {
            background: none; border: none; color: inherit;
            font: inherit; cursor: pointer; width: 100%;
            height: 100%; position: absolute; top: 0; left: 0;
            opacity: 0;
        }
    </style>
</head>
<body>
    <div class="card">
        <form method="post">
            <img src="img/seller_icon.png" alt="Seller">
            <div class="card-title">Seller</div>
            <p>Access your seller dashboard.</p>
            <input type="submit" name="role" value="seller">
        </form>
    </div>
    <div class="card">
        <form method="post">
            <img src="img/buyer_icon.png" alt="Buyer">
            <div class="card-title">Buyer</div>
            <p>View and purchase products.</p>
            <input type="submit" name="role" value="buyer">
        </form>
    </div>
    <div class="card">
        <form method="post">
            <img src="img/admin_icon.png" alt="Admin">
            <div class="card-title">Admin</div>
            <p>Manage all administrative functions.</p>
            <input type="submit" name="role" value="admin">
        </form>
    </div>
    <a href="logout.php" class="text-blue-500 hover:underline">Logout</a>
</body>
</html>