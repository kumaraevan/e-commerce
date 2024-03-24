<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] != 'seller') {
    header("Location: login.php");
    exit;
}

$message = "";

if (isset($_GET['product_id']) && is_numeric($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    $stmt = $conn->prepare("SELECT * FROM products WHERE ProductID = ? AND SellerID = ?");
    $stmt->bind_param("ii", $product_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if (!$product) {
        $message = "Product not found";
        header("Location: seller_manage_products.php");
        exit;
    }
    $stmt->close();
} else {
    header("Location: seller_manage_products.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $product_name = $conn->real_escape_string(trim($_POST['product_name']));
    $product_description = $conn->real_escape_string(trim($_POST['product_description']));
    $product_price = $conn->real_escape_string(trim($_POST['product_price']));
    $stock_quantity = $conn->real_escape_string(trim($_POST['stock_quantity']));
    $category = $conn->real_escape_string(trim($_POST['category']));

    if (empty($product_name) || empty($product_price) || empty($stock_quantity) || empty($category)) {
        $message = "Please fill in all required fields";
    } elseif (!is_numeric($product_price) || !is_numeric($stock_quantity)) {
        $message = "Price and Stock Quantity must be numbers.";
    } else {
        $stmt = $conn->prepare("UPDATE products SET Name = ?, Description = ?, Price = ?, StockQuantity = ?, Category = ? WHERE ProductID = ? AND SellerID = ?");
        $stmt->bind_param("ssdisii", $product_name, $product_description, $product_price, $stock_quantity, $category, $product_id, $_SESSION['user_id']);

        if ($stmt->execute()) {
            $message = "Product updated successfully!";
            header("Location: seller_manage_products.php");
            exit;
        } else {
            $message = "Error updating product: " . $conn->error;
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
        <title>Edit Product</title>
        <?php if (!empty($message)): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <?php if ($product): ?>
            <form action="edit_product.php?product_id=<?php echo htmlspecialchars($product_id); ?>" method="POST">

                <label for="product_name">Product Name</label>
                <input type="text" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product['Name']); ?>" required>

                <label for="product_description">Product Description</label>
                <textarea id="product_description" name="product_description" required><?php echo htmlspecialchars($product['Description']); ?></textarea>

                <label for="product_price">Product Price</label>
                <input type="number" id="product_price" name="product_price" value="<?php echo htmlspecialchars($product['Price']); ?>"required step="0.01">

                <label for="stock_quantity">Stock Quantity</label>
                <input type="number" id="stock_quantity" name="stock_quantity" value="<?php echo htmlspecialchars($product['StockQuantity']); ?>" required>

                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="<?php echo htmlspecialchars($product['Category']); ?>" selected><?php echo htmlspecialchars($product['Category']); ?></option>
                </select>

                <input type="submit" name="update" value="Update Product">
            </form>
        <?php endif; ?>
    </head>
</html>