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
        <style>
            .navbar {
                overflow: hidden;
                background-color: #333;
            }

            .navbar a {
                float: left;
                display: block;
                color: white;
                text-align: center;
                padding: 14px 20px;
                text-decoration: none;
            }

            .navbar-right {
                float: right;
            }

            .navbar::after {
                content: "";
                display: table;
                clear: both;
            }

            .navbar a:hover {
                background-color: #ddd;
                color: black;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            table, th, td {
                border: 1px solid #ddd;
            }

            th, td {
                padding: 8px;
                text-align: left;
            }

            th {
                background-color: #f2f2f2;
            }
        </style>
    </head>
    <body>
    <div class="navbar">
        <a href="seller_dashboard.php">Dashboard</a>
        <a href="seller_add_new_products.php">Add New Products</a>
        <a href="seller_manage_products.php">Manage Products</a>
        <a href="seller_orders.php">View Orders</a>
        <div class="navbar-right">
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <div class="dashboard-content">
    <h1>Welcome To Your Dashboard, <?php echo htmlspecialchars($_SESSION["name"]); ?>!</h1>
        
        <?php if (!empty($products)): ?>
            <table>
                <tr>
                    <th>No.</th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Category</th>
                </tr>
                <?php $counter = 1; ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo $counter++; ?></td>
                        <td><?php echo htmlspecialchars($product['Name']); ?></td>
                        <td>Rp.<?php echo htmlspecialchars($product['Price']); ?></td>
                        <td><?php echo htmlspecialchars($product['Description']); ?></td>
                        <td><?php echo htmlspecialchars($product['StockQuantity']); ?></td>
                        <td><?php echo htmlspecialchars($product['CategoryName']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No products found.</p>
        <?php endif; ?>
        
        <div class="dashboard-widget">
            <h2>Recent Orders</h2>
                <?php if (!empty($orders)): ?>
                    <table>
                        <tr>
                            <th>No.</th>
                            <th>Order ID</th>
                            <th>Products</th>
                            <th>Total Price</th>
                            <th>Date Ordered</th>
                            <th>Order Status</th>
                        </tr>
                        <?php $counter = 1; ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td><?php echo htmlspecialchars($order['OrderID']); ?></td>
                                <td><?php echo htmlspecialchars($order['Products']); ?></td>
                                <td>Rp.<?php echo number_format($order['TotalPrice'], 2); ?></td>
                                <td><?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($order['DateOrdered']))); ?></td>
                                <td><?php echo htmlspecialchars($order['OrderStatus']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php else: ?>
                    <p>No recent orders found.</p>
                <?php endif; ?>
            </div>
        </div>
</body>
</html>