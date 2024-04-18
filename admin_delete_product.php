<?php
session_start();
require_once 'config.php';

// Redirect non-logged in users or non-admins
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("Location: login.php");
    exit;
}

$product_id = $_GET['id'];

// Delete product based on product ID
$sql = "DELETE FROM products WHERE ProductID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// Redirect back to products page after deletion
header("Location: admin_products.php");
exit;
?>