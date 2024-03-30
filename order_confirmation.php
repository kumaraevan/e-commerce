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
        <style>
            body { font-family: Arial, sans-serif; }
            .order-confirmation { width: 600px; margin: auto; padding-top: 50px; }
            .order-details { background-color: #f4f4f4; padding: 20px; margin-bottom: 20px; }
            .order-details h2, .order-details p { margin: 0; }
            .order-details p { margin-bottom: 10px; }
        </style>
    </head>
    <body>
        <div class="order-confirmation">
            <div class="order-details">
                <h2>Order Confirmation</h2>
                <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order_details['OrderID']); ?></p>
                <p><strong>Buyer:</strong> <?php echo htmlspecialchars($order_details['BuyerName']); ?></p>
                <p><strong>Products:</strong> <?php echo htmlspecialchars($order_details['Products']); ?></p>
                <p><strong>Total Price:</strong> Rp. <?php echo number_format($order_details['TotalPrice'], 2); ?></p>
                <p><strong>Date Ordered:</strong> <?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($order_details['DateOrdered']))); ?></p>
            </div>
            <p>Thank you for your purchase! Your order is being processed.</p>
        </div>
    </body>
</html>