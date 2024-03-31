<?php
session_start();
require_once 'config.php';

$logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'];

$products_query = "SELECT ProductID, Name, Description, Price, StockQuantity, ImageURLs FROM products ORDER BY ProductID DESC LIMIT 10";
$result = $conn->query($products_query);

$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
$conn->close();

?>

<!DOCTYPE html>
<html>
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

            .products-container {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-around;
            }

            .product {
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                margin: 10px;
                padding: 20px;
                width: calc(33.333% - 20px);
                text-align: center;
            }

            .product img {
                max-width: 100%;
                height: auto;
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
            <?php if ($logged_in): ?>
                <a href="register_seller.php">Open Shop!</a>
                <a href="account.php">My Account</a>
                <a href="cart.php">Cart (0)</a> <!-- Apply Dynamic Cart Count -->
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </div>
        </div>

        <h1>Welcome To Our eCommerce Website!</h1>

        <div class="products-container">
        <?php foreach ($products as $product): ?>
            <a href="product_detail.php?ProductID=<?php echo $product['ProductID']; ?>" class="product" style="text-decoration: none; color: black;">
                <div>
                    <h3><?php echo htmlspecialchars($product['Name']); ?></h3>
                    <p><?php echo htmlspecialchars($product['Description']); ?></p>
                    <p>Rp.<?php echo htmlspecialchars($product['Price']); ?></p>
                    <?php if (!empty($product['ImageURLs'])): ?>
                        <img src="<?php echo htmlspecialchars($product['ImageURLs']); ?>" alt="Product Image" style="width: 100px; height: auto;">
                    <?php endif; ?>
                    <p>Stocks: <?php echo htmlspecialchars($product['StockQuantity']); ?></p>
                </div>
            </a>
        <?php endforeach; ?>
        <?php if (empty($products)): ?>
            <p>No Products Found</p>
        <?php endif; ?>
    </div>
    </body>
</html>