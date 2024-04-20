<?php
session_start();
require_once 'config.php';

// Redirect if not logged in or if the user is not a seller or admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || ($_SESSION["role"] !== 'seller' && $_SESSION["role"] !== 'admin')) {
    header("Location: login.php");
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable exception error mode
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id_to_update = $_POST['order_id'];
    $seller_id = $_SESSION['user_id'];
    
    // First, get the current order status from the database
    $current_status = '';
    $get_status_sql = "SELECT OrderStatus FROM orders WHERE OrderID = ?";
    if ($status_stmt = mysqli_prepare($conn, $get_status_sql)) {
        mysqli_stmt_bind_param($status_stmt, "i", $order_id_to_update);
        mysqli_stmt_execute($status_stmt);
        mysqli_stmt_bind_result($status_stmt, $current_status);
        mysqli_stmt_fetch($status_stmt);
        mysqli_stmt_close($status_stmt);
    }

    $new_status = '';
    if (isset($_POST['cancel_order']) && $current_status == 'PaymentConfirmed') {
        $new_status = 'Cancelled';
    } elseif (isset($_POST['process_order']) && $current_status == 'PaymentConfirmed') {
        $new_status = 'OrderProcessing';
    } elseif (isset($_POST['ship_order'])) {
        $new_status = 'InTransit';
    } else {
        $_SESSION['message'] = 'Order cannot be modified. Only orders with status "PaymentConfirmed" can be cancelled or processed.';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if ($new_status !== '') {
        $update_order_sql = "UPDATE orders AS o
                                JOIN orderdetails AS od ON o.OrderID = od.OrderID
                                JOIN products AS p ON od.ProductID = p.ProductID
                                SET o.OrderStatus = ?
                                WHERE o.OrderID = ? AND p.SellerID = ?";
        
        mysqli_begin_transaction($conn);
        
        try {
            if ($stmt = mysqli_prepare($conn, $update_order_sql)) {
                mysqli_stmt_bind_param($stmt, "sii", $new_status, $order_id_to_update, $seller_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_commit($conn);
                    $_SESSION['message'] = "Order updated to '$new_status' successfully.";
                } else {
                    mysqli_rollback($conn);
                    $_SESSION['message'] = "Failed to update order to '$new_status'.";
                }
                mysqli_stmt_close($stmt);
            }
        } catch (mysqli_sql_exception $exception) {
            mysqli_rollback($conn);
            $_SESSION['message'] = "An error occurred: " . $exception->getMessage();
        }
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

$seller_id = $_SESSION['user_id'];
$orders = [];

$orders_sql = "SELECT o.OrderID, o.TotalPrice, o.DateOrdered, o.OrderStatus, GROUP_CONCAT(p.Name ORDER BY p.Name ASC SEPARATOR ', ') AS Products 
        FROM orders AS o
        JOIN orderdetails AS od ON o.OrderID = od.OrderID
        JOIN products AS p ON od.ProductID = p.ProductID
        WHERE p.SellerID = ?
        GROUP BY o.OrderID
        ORDER BY o.DateOrdered DESC";
if ($stmt = mysqli_prepare($conn, $orders_sql)) {
    mysqli_stmt_bind_param($stmt, "i", $seller_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View & Process Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
</head>
<body class="bg-gray-100 flex">

    <?php include 'sidebar_seller.php'; ?> <!-- Include the sidebar -->
    <div class="pl-64"> <!-- Add padding to accommodate the sidebar -->
    <div class="container mx-auto mt-10 pl-28">
    <?php if (!empty($_SESSION['message'])): ?>
        <div class="mt-5">
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                <p class="font-bold">Notice</p>
                <p><?php echo htmlspecialchars($_SESSION['message']); ?></p>
            </div>
        </div>
    <?php unset($_SESSION['message']); endif; ?>
    <div class="container mx-auto mt-10">
        <h2 class="text-2xl font-bold mb-5">View & Process Orders</h2>
        <?php if (!empty($orders)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 bg-gray-600 text-left text-xs font-semibold text-gray-100 uppercase tracking-wider">No.</th>
                            <th class="px-5 py-3 bg-gray-600 text-left text-xs font-semibold text-gray-100 uppercase tracking-wider">Order ID</th>
                            <th class="px-5 py-3 bg-gray-600 text-left text-xs font-semibold text-gray-100 uppercase tracking-wider">Products</th>
                            <th class="px-5 py-3 bg-gray-600 text-left text-xs font-semibold text-gray-100 uppercase tracking-wider">Total Price</th>
                            <th class="px-5 py-3 bg-gray-600 text-left text-xs font-semibold text-gray-100 uppercase tracking-wider">Date Ordered</th>
                            <th class="px-5 py-3 bg-gray-600 text-left text-xs font-semibold text-gray-100 uppercase tracking-wider">Order Status</th>
                            <th class="px-5 py-3 bg-gray-600 text-left text-xs font-semibold text-gray-100 uppercase tracking-wider">Manage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $counter = 1; ?>
                        <?php foreach ($orders as $order): ?>
                            <tr class="bg-white border-b border-gray-200">
                                <td class="px-5 py-5 text-sm bg-white"><?php echo $counter++; ?></td>
                                <td class="px-5 py-5 text-sm bg-white"><?php echo htmlspecialchars($order['OrderID']); ?></td>
                                <td class="px-5 py-5 text-sm bg-white"><?php echo htmlspecialchars($order['Products']); ?></td>
                                <td class="px-5 py-5 text-sm bg-white">Rp.<?php echo number_format($order['TotalPrice'], 2); ?></td>
                                <td class="px-5 py-5 text-sm bg-white"><?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($order['DateOrdered']))); ?></td>
                                <td class="px-5 py-5 text-sm bg-white"><?php echo htmlspecialchars($order['OrderStatus']); ?></td>
                                <td class="px-5 py-5 text-sm bg-white">
                                    <?php if ($order['OrderStatus'] == 'PaymentConfirmed'): ?>
                                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="inline">
                                            <input type="hidden" name="order_id" value="<?php echo $order['OrderID']; ?>">
                                            <input type="submit" name="cancel_order" value="Cancel Order" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded m-1">
                                        </form>
                                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="inline">
                                            <input type="hidden" name="order_id" value="<?php echo $order['OrderID']; ?>">
                                            <input type="submit" name="process_order" value="Process Now" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-2 rounded m-1">
                                        </form>
                                    <?php elseif ($order['OrderStatus'] == 'OrderProcessing'): ?>
                                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="inline">
                                            <input type="hidden" name="order_id" value="<?php echo $order['OrderID']; ?>">
                                            <input type="submit" name="cancel_order" value="Cancel Order" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded m-1">
                                        </form>
                                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="inline">
                                            <input type="hidden" name="order_id" value="<?php echo $order['OrderID']; ?>">
                                            <input type="submit" name="ship_order" value="Ship Now" class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded m-1">
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="px-5 py-5 bg-white text-sm text-gray-900">No recent orders found.</div>
        <?php endif; ?>        
    </div>
    </div>
</body>
</html>