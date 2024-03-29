<?php
session_start();
require_once 'config.php';

// Redirect if not logged in or not a seller
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] != 'seller') {
    header("Location: login.php");
    exit;
}

// Process deletion only on POST request to enhance security
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];

    // Prepare a delete statement
    $sql = "DELETE FROM products WHERE ProductID = ? AND SellerID = ?";
    if ($stmt = $conn->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("ii", $product_id, $_SESSION['user_id']);
        
        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
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