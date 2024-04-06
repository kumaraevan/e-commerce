<?php
session_start();
require_once 'config.php';

$payment_success_message = ''; // Initialize message variable

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION["user_id"];
    $selected_items = $_SESSION['selected_items'] ?? [];

    mysqli_begin_transaction($conn);

    try {
        foreach ($selected_items as $item) {
            list($orderID) = explode('-', $item); // Extract the order ID
            $update_query = "UPDATE Orders SET OrderStatus = 'PaymentConfirmed' WHERE OrderID = ? AND BuyerID = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            if ($update_stmt) {
                mysqli_stmt_bind_param($update_stmt, "ii", $orderID, $user_id);
                mysqli_stmt_execute($update_stmt);
                mysqli_stmt_close($update_stmt);
            } else {
                throw new Exception("Prepare failed: " . mysqli_error($conn));
            }
        }
        mysqli_commit($conn);
        $payment_success_message = "Payment confirmed. Your order is being proccessed.";
    } catch (Exception $e) {
        mysqli_rollback($conn); // Something went wrong, rollback the transaction
        // Log error message
        error_log("Transaction failed: " . $e->getMessage());
        $payment_success_message = "Error processing payment. Please try again.";
    } finally {
        mysqli_close($conn);
    }
} else {
    // Redirect if the page is accessed without POST
    header("Location: payment.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Payment Success</title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    </head>
    <body class="bg-gray-100 flex items-center justify-center h-screen">
        <div class="bg-white p-8 rounded-lg shadow-lg text-center">
            <h2 class="text-2xl font-bold mb-4">Payment Status</h2>
            <p class="mb-4"><?php echo $payment_success_message; ?></p>
            <a href="index.php" class="text-white bg-blue-500 hover:bg-blue-700 rounded-full px-4 py-2">Return Home</a>
        </div>
    </body>
</html>