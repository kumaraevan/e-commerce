<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'seller') {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

// Seller Functionality Not Yet Added
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

            .navbar::after {
                content: "";
                display: table;
                clear: both;
            }

            .navbar a:hover {
                background-color: #ddd;
                color: black;
            }
        </style>
    </head>
    <body>
    <div class="navbar">
    <a href="seller_add_new_products.php">Add New Products</a>
    <a href="seller_manage_products.php">Manage Products</a>
    <a href="seller_orders.php">View Orders</a>
    <a href="logout.php">Logout</a>
    </div>

    <div class="dashboard-content">
    <h1>Welcome to Your Seller Dashboard, <?php echo isset($_SESSION["name"]) ? htmlspecialchars($_SESSION["name"]) : 'Guest'; ?>!</h1>

    
    <div class="dashboard-widget">
        <h2>Product Listings</h2>
    </div>
    
    <div class="dashboard-widget">
        <h2>Recent Orders</h2>
    </div>
    
</div>
</body>
</html>