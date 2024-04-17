<?php
session_start();
require_once 'config.php';

// Redirect non-logged in users or non-admins
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
  header("Location: login.php");
  exit;
}
?>

<html>
<head>
</head>
<body>
<a href="users_table.php">Users</a>
<a href="categories_table.php">Categories</a>
<a href="products_table.php">Products</a>  
<a href="orders_table.php">Orders</a>  
<a href="order_details_table.php">Order Details</a>  
<a href="reviews_table.php">Reviews</a>  
<a href="payments_table.php">Payment</a>  
<a href="deliveries_table.php">Deliveries</a>  
<a href="transaction_reports_table.php">Transaction Reports</a>  
<a href="register_admin.php">Register Admin</a>
</body>
</html>
