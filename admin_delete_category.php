<?php
session_start();
require_once 'config.php';

// Redirect non-logged in users or non-admins
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("Location: login.php");
    exit;
}

$category_id = $_GET['id'];

// Delete category based on category ID
$sql = "DELETE FROM categories WHERE CategoryID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $category_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// Redirect back to categories page after deletion
header("Location: categories_table.php");
exit;
?>