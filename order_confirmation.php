<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] != 'buyer') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    echo "No order ID provided.";
    exit;
}

$order_id = $_GET['order_id'];

//Fetch data from database
$stmt = $conn->prepare("SELECT o.OrderID, o.TotalPrice, o.DateOrdered, u.Name AS BuyerName, GROUP_CONCAT(p.Name ORDER BY p.Name ASC SEPARATOR ', ') AS Products 
        FROM orders o 
        JOIN orderdetails od ON o.OrderID = od.OrderID 
        JOIN products p ON od.ProductID = p.ProductID 
        JOIN users u ON o.BuyerID = u.UserID 
        WHERE o.OrderID = ? 
        GROUP BY o.OrderID");

$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Order not found.";
    exit;
}

$order_details = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
            <div class="relative flex items-center justify-between h-16">
                <div class="flex-1 flex items-center justify-start">
                    <a href="index.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Home</a>
                    <a href="#products" class="ml-4 px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Products</a>
                    <a href="#search" class="ml-4 px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Search</a>
                    <a href="#about" class="ml-4 px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">About</a>
                </div>
                <div class="ml-4 flex items-center md:ml-6">
                    <a href="account.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">My Account</a>
                    <a href="cart.php" class="ml-4 px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Cart (0)</a>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="max-w-2xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:max-w-7xl lg:px-8">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 bg-gray-800 text-white">
                <h3 class="text-lg leading-6 font-medium text-white">Order Confirmation</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-300">Details about your recent order.</p>
            </div>
            <div class="border-t border-gray-200">
                <dl>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Order ID</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0"><?php echo htmlspecialchars($order_details['OrderID']); ?></dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Buyer</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0"><?php echo htmlspecialchars($order_details['BuyerName']); ?></dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Products</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0"><?php echo htmlspecialchars($order_details['Products']); ?></dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Total Price</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">Rp. <?php echo number_format($order_details['TotalPrice'], 2); ?></dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Date Ordered</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0"><?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($order_details['DateOrdered']))); ?></dd>
                    </div>
                </dl>
            </div>
        </div>
        <div class="mt-6 text-center">
            <p>Thank you for your purchase! Your order is being processed.</p>
        </div>
    </div>
</body>
</html>