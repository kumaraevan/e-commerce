<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];
$products = [];
$orders = [];

// Fetch seller's products
$products_sql = "SELECT ProductID, Name, Price, Description, StockQuantity, CategoryName 
                 FROM products 
                 LEFT JOIN categories ON products.CategoryID = categories.CategoryID 
                 WHERE SellerID = ? 
                 ORDER BY ProductID DESC";
if ($stmt = mysqli_prepare($conn, $products_sql)) {
    mysqli_stmt_bind_param($stmt, "i", $seller_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
}

// Fetch orders containing seller's products
$orders_sql = "SELECT o.OrderID, o.TotalPrice, o.DateOrdered, o.OrderStatus, GROUP_CONCAT(p.Name ORDER BY p.Name ASC SEPARATOR ', ') AS Products 
        FROM orders AS o
        JOIN orderdetails AS od ON o.OrderID = od.OrderID
        JOIN products AS p ON od.ProductID = p.ProductID
        WHERE p.SellerID = ?
        GROUP BY o.OrderID
        ORDER BY o.DateOrdered DESC";
if ($stmt = mysqli_prepare($conn, $orders_sql)) {
    mysqli_stmt_bind_param($stmt, "i", $seller_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-gray-900 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="seller_dashboard.php" class="hover:bg-gray-700 px-3 py-2 rounded">Dashboard</a>
            <a href="seller_add_new_products.php" class="hover:bg-gray-700 px-3 py-2 rounded">Add New Products</a>
            <a href="seller_manage_products.php" class="hover:bg-gray-700 px-3 py-2 rounded">Manage Products</a>
            <a href="seller_orders.php" class="hover:bg-gray-700 px-3 py-2 rounded">View Orders</a>
            <div class="flex space-x-4">
                <a href="logout.php" class="hover:bg-gray-700 px-3 py-2 rounded">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container mx-auto mt-10">
        <h1 class="text-2xl font-bold mb-5 text-center">Welcome To Your Dashboard, <?php echo htmlspecialchars($_SESSION["name"]); ?>!</h1>
        <div class="mb-10">
            <h2 class="text-lg leading-6 font-medium text-gray-900 mb-2">Your Products</h2>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="rounded-t-lg bg-gray-600">
                    <?php if (!empty($products)): ?>
                        <table class="min-w-full leading-normal">
                            <thead class="text-white rounded-t-lg">
                                <tr>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">No.</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">Product Name</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">Price</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">Description</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">Quantity</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">Category</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                <?php $counter = 1; ?>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?php echo $counter++; ?></td>
                                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?php echo htmlspecialchars($product['Name']); ?></td>
                                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">Rp<?php echo htmlspecialchars($product['Price']); ?></td>
                                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?php echo htmlspecialchars($product['Description']); ?></td>
                                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?php echo htmlspecialchars($product['StockQuantity']); ?></td>
                                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?php echo htmlspecialchars($product['CategoryName']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No products found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div>    
            <h2 class="text-lg leading-6 font-medium text-gray-900 mb-2">Recent Orders</h2>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="rounded-t-lg bg-gray-600">
                    <?php if (!empty($orders)): ?>
                        <table class="min-w-full">
                        <thead class="text-white rounded-t-lg">
                            <tr>
                                <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">No.</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">Order ID</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">Products</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">Total Price</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">Date Ordered</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">Order Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <?php $counter = 1; ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?php echo $counter++; ?></td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?php echo htmlspecialchars($order['OrderID']); ?></td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?php echo htmlspecialchars($order['Products']); ?></td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">Rp<?php echo number_format($order['TotalPrice'], 2); ?></td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($order['DateOrdered']))); ?></td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?php echo htmlspecialchars($order['OrderStatus']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No recent orders found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>