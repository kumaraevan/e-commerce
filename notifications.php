<?php
session_start();
require_once 'config.php'; // Ensure this file contains the correct database connection setup

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$orders = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['arrived_order_id'])) {
        $arrived_order_id = $_POST['arrived_order_id'];

        $update_order_status_query = "
            UPDATE Orders 
            SET OrderStatus = 'Arrived' 
            WHERE OrderID = ? AND BuyerID = ?
        ";

        if ($stmt = mysqli_prepare($conn, $update_order_status_query)) {
            mysqli_stmt_bind_param($stmt, "ii", $arrived_order_id, $user_id);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION["message"] = "Order #{$arrived_order_id} has been marked as arrived.";
            } else {
                $_SESSION["error"] = "Unable to update the order status.";
            }
            mysqli_stmt_close($stmt);
        }
        header("Location: " . $_SERVER["PHP_SELF"]);
        exit;
    } elseif (isset($_POST['review_order_id'])) {
        $review_order_id = $_POST['review_order_id'];

        $review_exist_query = "SELECT 1 FROM Reviews WHERE OrderID = ? AND BuyerID = ?";
        if ($stmt = mysqli_prepare($conn, $review_exist_query)) {
            mysqli_stmt_bind_param($stmt, "ii", $review_order_id, $user_id);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 0) {
                    header("Location: review.php?order_id=" . $review_order_id);
                    mysqli_stmt_close($stmt);
                    exit;
                } else {
                    $_SESSION["error"] = "You have already submitted a review for this order.";
                }
            } else {
                $_SESSION["error"] = "Database error: Unable to check for existing review.";
            }
            mysqli_stmt_close($stmt);
        }
        header("Location: " . $_SERVER["PHP_SELF"]);
        exit;
    } elseif (isset($_POST['finish_order'])) {
        $order_id_to_finish = $_POST['finish_order_id'];

        $update_order_to_finished_query = "
            UPDATE Orders 
            SET OrderStatus = 'Finished' 
            WHERE OrderID = ? AND BuyerID = ?
        ";

        if ($stmt = mysqli_prepare($conn, $update_order_to_finished_query)) {
            mysqli_stmt_bind_param($stmt, "ii", $order_id_to_finish, $user_id);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION["message"] = "Order #{$order_id_to_finish} has been marked as finished.";
            } else {
                $_SESSION["error"] = "Unable to mark the order as finished.";
            }
            mysqli_stmt_close($stmt);
        }
        header("Location: notifications.php");
        exit;
    }
}

$orders_query = "
    SELECT o.OrderID, o.OrderStatus, o.DateOrdered, p.Name AS ProductName
    FROM Orders o
    JOIN OrderDetails od ON o.OrderID = od.OrderID
    JOIN Products p ON od.ProductID = p.ProductID
    WHERE o.BuyerID = ? AND o.OrderStatus IN ('PaymentConfirmed', 'OrderProcessing', 'InTransit', 'Arrived')
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
        mysqli_stmt_close($stmt);
    }
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
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        .alert-warning {
            color: #856404;
            background-color: #fff3cd;
            border-color: #ffeeba;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>
</head>
<body class="bg-gray-100">
<?php include 'C:\xampp\htdocs\eCommerce\dsgn\navbar.php'; ?>

<?php
    if (isset($_SESSION["warning"])) {
        echo "<div class='alert alert-warning'>" . $_SESSION["warning"] . "</div>";
        unset($_SESSION["warning"]); // Clear the message after displaying it
    }

    if (isset($_SESSION["message"])) {
        echo "<div class='alert alert-success'>" . $_SESSION["message"] . "</div>";
        unset($_SESSION["message"]); // Clear the message after displaying it
    }

    if (isset($_SESSION["error"])) {
        echo "<div class='alert alert-danger'>" . $_SESSION["error"] . "</div>";
        unset($_SESSION["error"]); // Clear the message after displaying it
    }
?>

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
                                    <!-- Check if a review is already made for this order -->
                                    <?php
                                    $review_check_query = "SELECT 1 FROM Reviews WHERE OrderID = ? AND BuyerID = ?";
                                    if ($stmt = mysqli_prepare($conn, $review_check_query)) {
                                        mysqli_stmt_bind_param($stmt, "ii", $order['OrderID'], $user_id);
                                        mysqli_stmt_execute($stmt);
                                        mysqli_stmt_store_result($stmt);

                                        if (mysqli_stmt_num_rows($stmt) == 0) {
                                            mysqli_stmt_close($stmt);
                                            ?>
                                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                                <input type="hidden" name="review_order_id" value="<?php echo $order['OrderID']; ?>">
                                                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                                    Write Review
                                                </button>
                                            </form>
                                            <?php
                                        } else {
                                            ?>
                                            <form method="POST" action="finish_transaction.php">
                                                <input type="hidden" name="finish_order_id" value="<?php echo $order['OrderID']; ?>">
                                                <button type="submit" class="bg-blue-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                                    Done
                                                </button>
                                            </form>
                                            <?php
                                        }
                                    }
                                    ?>
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
<?php mysqli_close($conn); ?>
</html>