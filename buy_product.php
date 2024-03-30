<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] != 'buyer') {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    // Start transaction
    $conn->begin_transaction();

    try {
        // Validate if product exists and has enough stock
        $stmt = $conn->prepare("SELECT Price, StockQuantity FROM products WHERE ProductID = ? AND StockQuantity >= ?");
        $stmt->bind_param("ii", $product_id, $quantity);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $product_data = $result->fetch_assoc();
            $product_price = $product_data['Price'];

            // Create a new order
            $stmt = $conn->prepare("INSERT INTO orders (BuyerID, TotalPrice, DateOrdered) VALUES (?, ?, NOW())");
            $buyer_id = $_SESSION["user_id"];
            $total_price = $product_price * $quantity;
            $stmt->bind_param("id", $buyer_id, $total_price);
            $stmt->execute();

            // Retrieve the new order's ID
            $order_id = $conn->insert_id;

            // Add order details
            $stmt = $conn->prepare("INSERT INTO orderdetails (OrderID, ProductID, Quantity, PriceAtPurchase) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $product_price);
            $stmt->execute();

            // Update product stock
            $stmt = $conn->prepare("UPDATE products SET StockQuantity = StockQuantity - ? WHERE ProductID = ?");
            $stmt->bind_param("ii", $quantity, $product_id);
            $stmt->execute();

            // Commit transaction
            $conn->commit();

            // Redirect to order confirmation page or payment processing page
            header("Location: order_confirmation.php?order_id=" . $order_id);
            exit;
        } else {
            throw new Exception("Insufficient stock or product does not exist.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['message'] = $e->getMessage();
        header("Location: product_detail.php?ProductID=" . $product_id);
        exit;
    } finally {
        $stmt->close();
        $conn->close();
    }
} else {
    // Redirect to products page if not a POST request
    header("Location: index.php");
    exit;
}
?>