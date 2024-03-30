<?php
session_start();
require_once 'config.php';

// Redirect if not logged in or if the user is not a seller
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];
$products = [];

// Fetch seller's products, adjust SELECT query based on your actual database schema
$sql = "SELECT p.ProductID, p.Name, p.Price, p.Description, p.StockQuantity, c.CategoryName, p.ImageURLs 
        FROM products AS p 
        LEFT JOIN categories AS c ON p.CategoryID = c.CategoryID 
        WHERE p.SellerID = ? 
        ORDER BY p.ProductID DESC";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products</title>
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

    <h2>Manage Your Products</h2>
    <a href="seller_add_new_products.php">Add New Product</a>    
    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Price</th>
                <th>Description</th>
                <th>Stock Quantity</th>
                <th>Category</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['Name']); ?></td>
                    <td><?php echo htmlspecialchars($product['Price']); ?></td>
                    <td><?php echo htmlspecialchars($product['Description']); ?></td>
                    <td><?php echo htmlspecialchars($product['StockQuantity']); ?></td>
                    <td><?php echo htmlspecialchars($product['CategoryName']); ?></td>
                    <td>
                        <a href="edit_product.php?ProductID=<?php echo $product['ProductID']; ?>" style="color: #add8e6;">Edit</a> |
                        <a href="delete_product.php?ProductID=<?php echo $product['ProductID']; ?>" style="color: red;" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>