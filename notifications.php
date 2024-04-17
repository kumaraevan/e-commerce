<?php
session_start();
require_once 'config.php'; // Ensure this file contains the correct database connection setup

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$orders = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['arrived_order_id'])) {
    $arrived_order_id = $_POST['arrived_order_id'];

    $update_order_status_query = "
        UPDATE Orders 
        SET OrderStatus = 'Arrived' 
        WHERE OrderID = ? AND BuyerID = ?
    ";

    if ($stmt = mysqli_prepare($conn, $update_order_status_query)) {
        mysqli_stmt_bind_param($stmt, "ii", $arrived_order_id, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Optional: Set a session message to notify the user of the success
            $_SESSION["message"] = "Order #{$arrived_order_id} has been marked as arrived.";
            header("Location: " . $_SERVER["PHP_SELF"]); // Redirect to the same page to show the updated status
            exit;
        } else {
            // Optional: Set an error message
            $_SESSION["error"] = "Unable to update the order status.";
        }
        mysqli_stmt_close($stmt);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['review_order_id'])) {
    $review_order_id = $_POST['review_order_id'];
    header("Location: review.php?order_id=" . $review_order_id);
    exit;
}

$orders_query = "
    SELECT o.OrderID, o.OrderStatus, o.DateOrdered, p.Name AS ProductName
    FROM Orders o
    JOIN OrderDetails od ON o.OrderID = od.OrderID
    JOIN Products p ON od.ProductID = p.ProductID
    WHERE o.BuyerID = ? AND o.OrderStatus IN ('OrderProcessing', 'InTransit', 'Arrived')
    GROUP BY o.OrderID
    ORDER BY o.DateOrdered DESC
";

if ($stmt = mysqli_prepare($conn, $orders_query)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);

    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['finish_order'])) {
    $order_id_to_finish = $_POST['finish_order_id'];

    $update_order_to_finished_query = "
        UPDATE Orders 
        SET OrderStatus = 'Finished' 
        WHERE OrderID = ?
    ";

    if ($stmt = mysqli_prepare($conn, $update_order_to_finished_query)) {
        mysqli_stmt_bind_param($stmt, "i", $order_id_to_finish);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION["message"] = "Order #{$order_id_to_finish} has been marked as finished.";
            header("Location: notifications.php");
            exit;
        } else {
            $_SESSION["error"] = "Unable to mark the order as finished.";
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .button-container {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        .button-container > form {
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include 'navbar.php'; ?>
    <div class="container mx-auto mt-8">
        <h2 class="text-xl font-bold mb-4">Order Notifications</h2>
        <div class="bg-white p-6 rounded-lg shadow overflow-hidden">
            <?php if (!empty($orders)): ?>
                <div class="space-y-4">
                    <?php foreach ($orders as $order): ?>
                        <div class="flex justify-between items-start border-b border-gray-200 pb-4">
                            <div>
                                <p class="text-lg font-bold">Order #<?php echo htmlspecialchars($order['OrderID']); ?></p>
                                <p>Status: <span class="<?php echo htmlspecialchars($order['OrderStatus']) == 'Arrived' ? 'text-green-600' : 'text-orange-600'; ?>">
                                    <?php echo htmlspecialchars($order['OrderStatus']); ?>
                                </span></p>
                                <p><?php echo htmlspecialchars($order['ProductName']); ?></p>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($order['DateOrdered']); ?></p>
                            </div>
                            <div class="button-container">
                                <?php if ($order['OrderStatus'] == 'InTransit'): ?>
                                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                        <input type="hidden" name="arrived_order_id" value="<?php echo $order['OrderID']; ?>">
                                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                            Mark as Arrived
                                        </button>
                                    </form>
                                <?php elseif ($order['OrderStatus'] == 'Arrived'): ?>
                                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                        <input type="hidden" name="review_order_id" value="<?php echo $order['OrderID']; ?>">
                                        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                            Write Review
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-600">You have no notifications regarding order status.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>