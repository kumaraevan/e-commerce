<?php
session_start();
require_once 'config.php';

// Ensure the user is logged in and is a seller
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || ($_SESSION["role"] !== 'seller' && $_SESSION["role"] !== 'admin')) {
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
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-gray-900 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="seller_dashboard.php" class="hover:bg-gray-700 px-3 py-2 rounded">Dashboard</a>
            <a href="seller_add_new_products.php" class="hover:bg-gray-700 px-3 py-2 rounded">Add New Products</a>
            <a href="seller_manage_products.php" class="hover:bg-gray-700 px-3 py-2 rounded">Manage Products</a>
            <a href="seller_orders.php" class="hover:bg-gray-700 px-3 py-2 rounded">View Orders</a>
            <a href="logout.php" class="hover:bg-gray-700 px-3 py-2 rounded">Logout</a>
        </div>
    </nav>

    <div class="container mx-auto mt-10">
        <h2 class="text-2xl font-bold mb-5 text-center">Edit Product</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?ProductID=" . $product_id); ?>" method="post" class="w-full max-w-lg mx-auto bg-white p-8 rounded-lg">
            <div class="mb-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Product Name:</label>
                <input type="text" name="name" value="<?php echo $productDetails['Name']; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            
            <div class="mb-4">
                <label for="price" class="block text-gray-700 text-sm font-bold mb-2">Price:</label>
                <input type="text" name="price" value="<?php echo $productDetails['Price']; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            
            <div class="mb-4">
                <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
                <textarea name="description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" required><?php echo $productDetails['Description']; ?></textarea>
            </div>
            
            <div class="mb-4">
                <label for="stockQuantity" class="block text-gray-700 text-sm font-bold mb-2">Stock Quantity:</label>
                <input type="number" name="stockQuantity" value="<?php echo $productDetails['StockQuantity']; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            
            <div class="mb-4">
                <label for="category" class="block text-gray-700 text-sm font-bold mb-2">Category:</label>
                <select name="category" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" required>
                    <?php echo $categoryOptions; ?>
                </select>
            </div>
            
            <div class="flex items-center justify-between">
                <input type="submit" name="update_product" value="Update Product" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            </div>
        </form>
    </div>
</body>
</html>