<?php
session_start();
require_once 'config.php';

// Check if the user is logged in and has an order to cancel
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_POST['orderID'])) {
    // Redirect to the login page or error page if not logged in or no order is provided
    header("Location: error_page.php");
    exit;
}

// Extract the order ID from the POST data
$orderId = $_POST['orderID'];
$userID = $_SESSION["user_id"];

// Connect to the database and set the charset
$conn->set_charset('utf8mb4');

// Start a database transaction
$conn->begin_transaction();

try {
    // Check if the order is eligible for cancellation
    $checkStmt = $conn->prepare("SELECT OrderStatus FROM orders WHERE OrderID = ? AND BuyerID = ?");
    $checkStmt->bind_param("ii", $orderID, $userId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $checkStmt->close();

    // Fetch the order status
    if ($checkResult->num_rows === 1) {
        $order = $checkResult->fetch_assoc();

        // Proceed with cancellation if the order status allows for it
        if ($order['OrderStatus'] === 'AwaitingPayment') {
            // Update the order status to 'canceled'
            $updateStmt = $conn->prepare("UPDATE orders SET OrderStatus = 'Cancelled' WHERE OrderID = ? AND BuyerID = ?");
            $updateStmt->bind_param("ii", $orderID, $userID);
            $updateStmt->execute();
            $updateStmt->close();

            // Commit the transaction
            $conn->commit();

            // Set a session message to notify the user of the successful cancellation
            $_SESSION["message"] = "Order #{$orderID} has been successfully cancelled.";
        } else {
            // Set a session message to notify the user that the order cannot be canceled
            $_SESSION["message"] = "Order #{$orderID} is not eligible for cancellation.";
        }
    } else {
        // Set a session message to notify the user that the order was not found or doesn't belong to them
        $_SESSION["message"] = "Order not found or you do not have permission to cancel this order.";
    }

} catch (mysqli_sql_exception $exception) {
    // Rollback the transaction if anything goes wrong
    $conn->rollback();

    // Set a session message to notify the user of the failure
    $_SESSION["message"] = "An error occurred while attempting to cancel the order: " . $exception->getMessage();
}

// Close the database connection
$conn->close();

// Redirect to the page that shows the orders or cart
header("Location: cart.php");
exit;
?>