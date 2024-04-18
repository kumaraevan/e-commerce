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
                exit;
            case 'buyer':
                header('Location: index.php');
                exit;
            case 'admin':
                header('Location: admin_dashboard.php');
                exit;
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
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body class="bg-gray-100 flex justify-center items-center h-screen">
    <div class="flex space-x-4">
        <!-- Seller Card -->
        <form method="post" class="bg-white shadow-md rounded-lg text-center p-6 hover:scale-105 transition-transform cursor-pointer">
            <button type="submit" name="role" value="seller" class="flex flex-col items-center justify-center space-y-4 w-full h-full">
                <i class="fas fa-store fa-3x text-blue-500"></i>
                <div class="text-lg font-bold">Seller</div>
                <p class="text-sm">Access your seller dashboard.</p>
            </button>
        </form>
        <!-- Buyer Card -->
        <form method="post" class="bg-white shadow-md rounded-lg text-center p-6 hover:scale-105 transition-transform cursor-pointer">
            <button type="submit" name="role" value="buyer" class="flex flex-col items-center justify-center space-y-4 w-full h-full">
                <i class="fas fa-shopping-cart fa-3x text-green-500"></i>
                <div class="text-lg font-bold">Buyer</div>
                <p class="text-sm">View and purchase products.</p>
            </button>
        </form>
        <!-- Admin Card -->
        <form method="post" class="bg-white shadow-md rounded-lg text-center p-6 hover:scale-105 transition-transform cursor-pointer">
            <button type="submit" name="role" value="admin" class="flex flex-col items-center justify-center space-y-4 w-full h-full">
                <i class="fas fa-user-shield fa-3x text-red-500"></i>
                <div class="text-lg font-bold">Admin</div>
                <p class="text-sm">Manage all administrative functions.</p>
            </button>
        </form>
    </div>
    <a href="logout.php" class="absolute bottom-10 text-blue-500 hover:underline">Logout</a>
</body>
</html>