<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] != 'buyer') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['ProductID']) && is_numeric($_GET['ProductID'])) {
    $product_id = $_GET['ProductID'];
    
    // Fetch the product's details from the database
    $stmt = $conn->prepare("SELECT ProductID, Name, Description, Price, StockQuantity, ImageURLs FROM products WHERE ProductID = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($product = $result->fetch_assoc()) {
    } else {
        echo "Product not found.";
        exit;
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo "No product ID provided.";
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>eCommerce</title>
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
        </style>
    </head>
    <body>
        <div class="navbar">
            <a href="index.php">Home</a>
            <a href="#products">Products</a>
            <a href="#search">Search</a>
            <a href="#about">About</a>

        <div class="navbar-right">
            <a href="account.php">My Account</a>
            <a href="cart.php">Cart (0)</a> <!-- Update '0' with dynamic cart count -->
        </div>
        </div>
    
        <div class="product-detail">
        <h1><?php echo htmlspecialchars($product['Name']); ?></h1>
        <p><?php echo htmlspecialchars($product['Description']); ?></p>
        <p>Price: Rp.<?php echo htmlspecialchars($product['Price']); ?></p>
        <p>Stocks: <?php echo htmlspecialchars($product['StockQuantity']); ?></p>
        <img src="<?php echo htmlspecialchars($product['ImageURLs']); ?>" alt="<?php echo htmlspecialchars($product['Name']); ?>">

        <form action="buy_product.php" method="post">
            <input type="hidden" name="product_id" value="<?php echo isset($product['ProductID']) ? $product['ProductID'] : ''; ?>">
            <input type="number" name="quantity" min="1" value="1">
            <input type="submit" value="Buy Now">
        </form><br>
        <form action="cart.php" method="post">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <input type="number" name="quantity" min="1" value="1">
                <input type="submit" name="add_to_cart" value="Add to Cart">
        </form>
    </div>
</body>
</html>