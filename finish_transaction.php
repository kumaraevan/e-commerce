<?php
session_start();
require_once 'config.php'; // Ensure this file contains the correct database connection setup

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['finish_order_id'])) {
    $order_id_to_finish = $_POST['finish_order_id'];
    $customer_id = $_SESSION["user_id"];

    // Initialize variables for ProductID and PaymentID
    $product_id = null;
    $payment_id = null;

    // Fetch ProductID and PaymentID associated with the OrderID
    // Error handling is crucial here; consider what should happen if these IDs are not found

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // Check if a transaction report already exists
        $transaction_exist_query = "SELECT 1 FROM transaction_reports WHERE OrderID = ? AND CustomerID = ?";
        if ($stmt = mysqli_prepare($conn, $transaction_exist_query)) {
            mysqli_stmt_bind_param($stmt, "ii", $order_id_to_finish, $customer_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {
                // Set a warning message if a transaction report already exists
                $_SESSION["warning"] = "A transaction report for Order #{$order_id_to_finish} has already been submitted.";
                mysqli_stmt_close($stmt);
                header("Location: notifications.php");
                exit;
            }
            mysqli_stmt_close($stmt);
        }

        // Prepare to get ProductID and PaymentID
        // Assuming there could be multiple products, you may need to adjust this logic
        // If only one product per order, you can simply fetch the first/only record
        $product_id_query = "SELECT ProductID FROM orderdetails WHERE OrderID = ?";
        if ($stmt = mysqli_prepare($conn, $product_id_query)) {
            mysqli_stmt_bind_param($stmt, "i", $order_id_to_finish);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($row = $result->fetch_assoc()) {
                $product_id = $row['ProductID'];
            }
            mysqli_stmt_close($stmt);
        }

        $payment_id_query = "SELECT PaymentID FROM payment WHERE OrderID = ?";
        if ($stmt = mysqli_prepare($conn, $payment_id_query)) {
            mysqli_stmt_bind_param($stmt, "i", $order_id_to_finish);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($row = $result->fetch_assoc()) {
                $payment_id = $row['PaymentID'];
            }
            mysqli_stmt_close($stmt);
        }

        // Insert into transaction_reports table
        $insert_transaction_report_query = "
            INSERT INTO transaction_reports (CustomerID, OrderID, ProductID, PaymentID)
            VALUES (?, ?, ?, ?)
        ";
        if ($stmt = mysqli_prepare($conn, $insert_transaction_report_query)) {
            mysqli_stmt_bind_param($stmt, "iiii", $customer_id, $order_id_to_finish, $product_id, $payment_id);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Unable to insert transaction report.");
            }
            mysqli_stmt_close($stmt);
        }

        // Commit the transaction
        mysqli_commit($conn);
        $_SESSION["message"] = "Transaction report for Order #{$order_id_to_finish} has been recorded.";

    } catch (Exception $e) {
        // Rollback the transaction if anything goes wrong
        mysqli_rollback($conn);
        $_SESSION["error"] = $e->getMessage();
    }

    // Close the connection
    mysqli_close($conn);

    // Redirect to a confirmation page or back to the notifications page
    header("Location: notifications.php");
    exit;
}
?>