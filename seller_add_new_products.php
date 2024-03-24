<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'seller') {
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {

    $target_dir = "uploads/";  // Make sure this directory exists
    $target_file = $target_dir . basename($_FILES["product_image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $image_path = '';

    if (isset($_FILES["product_image"])) {
        $check = getimagesize($_FILES["product_image"]["tmp_name"]);
        if ($check !== false) {
            if ($_FILES["product_image"]["size"] > 500000) {  // 500KB limit
                $message = "Sorry, your file is too large.";
            } else {
                if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                    $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                } else {
                    if (file_exists($target_file)) {
                        $message = "Sorry, file already exists.";
                    } else {
                        if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
                            $image_path = $target_file;
                        } else {
                            $message = "Sorry, there was an error uploading your file.";
                        }
                    }
                }
            }
        } else {
            $message = "File is not an image.";
        }
    } else {
        $message = "No file was uploaded.";
    }

    if (empty($message)) {
        $stmt = $conn->prepare("INSERT INTO products (SellerID, Name, Description, Price, StockQuantity, Category, ImageURLs, DateAdded) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdisss", $_SESSION['user_id'], $product_name, $product_description, $product_price, $stock_quantity, $category, $image_path, $date_added);

        $product_name = $conn->real_escape_string(trim($_POST['product_name']));
        $product_description = $conn->real_escape_string(trim($_POST['product_description']));
        $product_price = $conn->real_escape_string(trim($_POST['product_price']));
        $stock_quantity = $conn->real_escape_string(trim($_POST['stock_quantity']));
        $category = $conn->real_escape_string(trim($_POST['category']));
        $date_added = date('Y-m-d H:i:s');

        if ($stmt->execute()) {
            $message = "Product added successfully!";
        } else {
            $message = "Error adding product: " . $conn->error;
        }

        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Add New Product</title>
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
        <a href="seller_dashboard.php">Dashboard</a>
        <a href="seller_add_new_products.php">Add New Products</a>
        <a href="seller_manage_products.php">Manage Products</a>
        <a href="seller_orders.php">View Orders</a>
        <a href="logout.php">Logout</a>
        </div>

        <h1>Add New Product</h1>
        <?php if($message): ?>
            <p><?php echo $message; ?></p>
        <?php endif; ?>

        <form action="seller_add_new_products.php" method="POST" enctype="multipart/form-data">
            <label for="product_name">Product Name:</label>
            <input type="text" id="product_name" name="product_name" required>

            <label for="product_description">Description:</label>
            <textarea id="product_description" name="product_description" required></textarea>

            <label for="product_price">Product Price:</label>
            <input type="number" id="product_price" name="product_price" required step="0.01">

            <label for="stock_quantity">Stock Quantity:</label>
            <input type="number" id="stock_quantity" name="stock_quantity" required>

            <label for="category">Category</label>
            <select id="category" name="category" required>
                <option value="">Choose a category</option>
                <option value="electronics">Electronics</option>
                <option value="clothing">Clothing</option>
                <option value="cosmetics">Cosmetics</option>
                <option value="food">Food</option>
                <!-- Add More Categories -->
            </select>

            <label for="product_image">Product Image:</label>
            <input type="file" id="product_image" name="product_image" required>

            <input type="submit" name="submit" value="Add Product">
        </form>
    </body>
</html>