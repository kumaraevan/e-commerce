<?php
session_start();
require_once 'config.php';

// Redirect non-logged in users or non-admins
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Check if review ID is provided
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $review_id = $_GET['id'];

    // Delete review based on review ID
    $sql = "DELETE FROM reviews WHERE ReviewID = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $review_id);
        $stmt->execute();
        $stmt->close();

        // Redirect back to reviews page after deletion
        header("Location: reviews_table.php");
        exit;
    } else {
        echo "Error deleting review: " . htmlspecialchars($conn->error);
    }
} else {
    echo "Review ID not provided.";
}
?>