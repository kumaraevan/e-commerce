<?php
session_start();
require_once 'config.php';

// Ensure the user is logged in and is a seller
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] != 'seller') {
    header("Location: login.php");
    exit;
}

$product_id = isset($_GET['ProductID']) ? $_GET['ProductID'] : '';
$productDetails = [];

// Fetch product details for editing
if ($product_id) {
    $sql = "SELECT ProductID, Name, Price, Description, StockQuantity, CategoryID, ImageURLs FROM products WHERE ProductID = ? AND SellerID = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $product_id, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $productDetails = $result->fetch_assoc();
        } else {
            echo "Product not found or does not belong to you.";
            $stmt->close();
            $conn->close();
            exit;
        }
        $stmt->close();
    }
}

// Fetching categories for the dropdown
$categoryOptions = "";
$categoryQuery = "SELECT CategoryID, CategoryName FROM categories";
if ($categoryResult = $conn->query($categoryQuery)) {
    while ($category = $categoryResult->fetch_assoc()) {
        $selected = ($category['CategoryID'] == $productDetails['CategoryID']) ? 'selected' : '';
        $categoryOptions .= "<option value='" . $category['CategoryID'] . "' $selected>" . $category['CategoryName'] . "</option>";
    }
}

// Process the form when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_product'])) {
    $name = $conn->real_escape_string(trim($_POST['name']));
    $price = $conn->real_escape_string(trim($_POST['price']));
    $description = $conn->real_escape_string(trim($_POST['description']));
    $stockQuantity = $conn->real_escape_string(trim($_POST['stockQuantity']));
    $categoryID = intval($_POST['category']);

    $updateSql = "UPDATE products SET Name = ?, Price = ?, Description = ?, StockQuantity = ?, CategoryID = ? WHERE ProductID = ? AND SellerID = ?";
    if ($updateStmt = $conn->prepare($updateSql)) {
        $updateStmt->bind_param("sssiisi", $name, $price, $description, $stockQuantity, $categoryID, $product_id, $_SESSION['user_id']);
        if ($updateStmt->execute()) {
            $_SESSION['success_message'] = "Product updated successfully.";
            header("Location: seller_manage_products.php");
            exit();
        } else {
            echo "Error updating record: " . $conn->error;
        }
        $updateStmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
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
        <a href="logout.php">Logout</a>
    </div>
    
    <h2>Edit Product</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?ProductID=" . $product_id); ?>" method="post">
        <label for="name">Product Name:</label>
        <input type="text" name="name" value="<?php echo $productDetails['Name']; ?>" required><br>
        
        <label for="price">Price:</label>
        <input type="text" name="price" value="<?php echo $productDetails['Price']; ?>" required><br>
        
        <label for="description">Description:</label>
        <textarea name="description" required><?php echo $productDetails['Description']; ?></textarea><br>
        
        <label for="stockQuantity">Stock Quantity:</label>
        <input type="number" name="stockQuantity" value="<?php echo $productDetails['StockQuantity']; ?>" required><br>
        
        <label for="category">Category:</label>
        <select name="category" required>
            <?php echo $categoryOptions; ?>
        </select><br>
        
        <input type="submit" name="update_product" value="Update Product">
    </form>
</body>
</html>