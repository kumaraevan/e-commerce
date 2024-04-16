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

    if (isset($_POST['payment_method'])) {
        $address = $_SESSION['user_address'];
        $payment_method = $_POST['payment_method'];
        $update_order_query = "UPDATE Orders 
        SET OrderStatus = 'PaymentConfirmed', ShippingAddress = ?, PaymentMethod = ?
        WHERE BuyerID = ? 
        AND OrderStatus = 'AwaitingPayment'";
    
        if ($update_stmt = mysqli_prepare($conn, $update_order_query)) {
            mysqli_stmt_bind_param($update_stmt, "ssi", $address, $payment_method, $user_id);
            if (mysqli_stmt_execute($update_stmt)) {
                mysqli_begin_transaction($conn);

                try {
                    $current_date = date('Y-m-d H:i:s'); // Current date and time
                    foreach ($selected_items as $item) {
                        list($orderID) = explode('-', $item); // Extract the order ID
                        
                        // Insert into Payment table
                        $insert_payment_query = "INSERT INTO Payment (OrderID, Date) VALUES (?, ?)";
                        $payment_stmt = mysqli_prepare($conn, $insert_payment_query);
                        if ($payment_stmt) {
                            mysqli_stmt_bind_param($payment_stmt, "is", $orderID, $current_date);
                            mysqli_stmt_execute($payment_stmt);
                            mysqli_stmt_close($payment_stmt);
                        } else {
                            throw new Exception("Prepare failed for Payment: " . mysqli_error($conn));
                        }

                        // Insert into Delivery table
                        $insert_delivery_query = "INSERT INTO Deliveries (OrderID, Date) VALUES (?, ?)";
                        $delivery_stmt = mysqli_prepare($conn, $insert_delivery_query);
                        if ($delivery_stmt) {
                            mysqli_stmt_bind_param($delivery_stmt, "is", $orderID, $current_date);
                            mysqli_stmt_execute($delivery_stmt);
                            mysqli_stmt_close($delivery_stmt);
                        } else {
                            throw new Exception("Prepare failed for Delivery: " . mysqli_error($conn));
                        }
                    }
                    
                    mysqli_commit($conn); // Commit the transaction
                    $payment_success_message = "Payment confirmed. Your order is being processed.";

                } catch (Exception $e) {
                    mysqli_rollback($conn); // Something went wrong, rollback the transaction
                    // Log error message
                    error_log("Transaction failed: " . $e->getMessage());
                    $payment_success_message = "Error processing payment. Please try again.";
                } finally {
                    mysqli_close($conn); // Close the connection
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
    } else {
        header("Location: payment.php");
        exit;
    }
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