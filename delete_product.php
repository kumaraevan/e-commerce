<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] != 'seller') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['product_id']) && is_numeric($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    $stmt = $conn->prepare("DELETE FROM products WHERE ProductID = ? AND SellerID = ?");
    $stmt->bind_param("ii", $product_id, $_SESSION['user_id']);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: seller_manage_products.php");
        exit;
    } else {
        echo "Error deleting product: " . $conn->error; 
    }
}
?>