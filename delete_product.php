<?php
session_start();
require_once 'config.php';

// Redirect if not logged in or if the user is not a seller or admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"]!== true || ($_SESSION["role"]!== 'seller' && $_SESSION["role"]!== 'admin')) {
    header("Location: login.php");
    exit;
}

// Process deletion only on POST request to enhance security
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];

    // Get the imageURLs of the product to be deleted
    $select_query = "SELECT ImageURLs FROM products WHERE ProductID =? AND SellerID =?";
    $select_stmt = $conn->prepare($select_query);
    $select_stmt->bind_param("ii", $product_id, $_SESSION['user_id']);
    $select_stmt->execute();
    $select_stmt->bind_result($imageURLs);
    $select_stmt->fetch();
    $select_stmt->close();

    // Prepare a delete statement
    $sql = "DELETE FROM products WHERE ProductID =? AND SellerID =?";
    if ($stmt = $conn->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("ii", $product_id, $_SESSION['user_id']);

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Delete ImageURLs
            if (file_exists($imageURLs)) {
                unlink($imageURLs);
            }
            echo "Product deleted successfully.";
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }

        // Close statement
        $stmt->close();
    }

    // Close connection
    $conn->close();
} else {
    echo "Invalid request method.";
}

// Redirect back to the manage products page
header("Location: seller_manage_products.php");
exit();
?>