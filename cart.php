<?php
session_start();
require_once 'config.php';

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$orders = [];

// Fetch user data
$user_query = "SELECT Address FROM Users WHERE UserID = ?";
if ($user_stmt = mysqli_prepare($conn, $user_query)) {
    mysqli_stmt_bind_param($user_stmt, "i", $user_id);
    if (mysqli_stmt_execute($user_stmt)) {
        $user_result = mysqli_stmt_get_result($user_stmt);
        $user_data = $user_result->fetch_assoc();
    }
    mysqli_stmt_close($user_stmt);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['proceed_to_payment'])) {
        // Check if address is empty
        if (empty($user_data['Address'])) {
            echo "<p>Please update your address before proceeding to payment.</p>";
        } else {
            $_SESSION['selected_items'] = $_POST['selectedItems'] ?? [];
            header("Location: payment.php");
            exit;
        }
    } elseif (isset($_POST['cancel_order'])) {
        $selected_items = $_POST['selectedItems'] ?? [];
        if (!empty($selected_items)) {
            foreach ($selected_items as $item) {
                $item_details = explode('-', $item);
                $order_id = $item_details[0];

                $cancel_query = "UPDATE orders SET orderstatus = 'Cancelled' WHERE OrderID = ? AND BuyerID = ?";
                if ($cancel_stmt = mysqli_prepare($conn, $cancel_query)) {
                    mysqli_stmt_bind_param($cancel_stmt, "ii", $order_id, $user_id);
                    mysqli_stmt_execute($cancel_stmt);
                    mysqli_stmt_close($cancel_stmt);
                }
            }
            header("Location: " . htmlspecialchars($_SERVER["PHP_SELF"]));
            exit();
        }
    } elseif (isset($_POST['update_address'])) {
        $new_address = trim($_POST['address']);
        $update_query = "UPDATE Users SET Address = ? WHERE UserID = ?";
        if ($update_stmt = mysqli_prepare($conn, $update_query)) {
            mysqli_stmt_bind_param($update_stmt, "si", $new_address, $user_id);
            if (mysqli_stmt_execute($update_stmt)) {
                $user_data['Address'] = $new_address; // Update address in the current session data
            } else {
                echo "Error updating record: " . $conn->error;
            }
            mysqli_stmt_close($update_stmt);
        }
    }
}

// Fetch orders awaiting payment
$order_sql = "SELECT o.OrderID, o.DateOrdered, p.Name 
              AS ProductName, p.Price, od.Quantity, (p.Price * od.Quantity)     
              AS ItemTotal 
              FROM orders o
              JOIN orderdetails od ON o.OrderID = od.OrderID
              JOIN products p ON od.ProductID = p.ProductID
              WHERE o.BuyerID = ? AND o.OrderStatus = 'AwaitingPayment'
              ORDER BY o.DateOrdered DESC";
if ($stmt = mysqli_prepare($conn, $order_sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        while ($row = $result->fetch_assoc()) {
            $orders[$row['OrderID']]['DateOrdered'] = $row['DateOrdered'];
            $orders[$row['OrderID']]['Items'][] = [
                'ProductName' => $row['ProductName'],
                'Price' => $row['Price'],
                'Quantity' => $row['Quantity'],
                'ItemTotal' => $row['ItemTotal']
            ];
        }
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<?php include 'C:\xampp\htdocs\eCommerce\dsgn\navbar.php'; ?>
    <div class="container mx-auto mt-6 p-4">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <!-- Address Update Form -->
            <?php if (isset($_POST['edit_address'])): ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Edit Shipping Address</h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <textarea name="address" rows="4" class="mt-1 block w-full rounded-md border-gray-400 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-300 focus:ring-opacity-50 bg-gray-50" required><?php echo htmlspecialchars($user_data['Address'] ?? ''); ?></textarea>
                        <button type="submit" name="update_address" class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Update</button>
                    </div>
                </form>
            <?php else: ?>
                <!-- Display Current Address -->
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Current Shipping Address</h3>
                    <p class="mt-1"><?php echo nl2br(htmlspecialchars($user_data['Address'] ?? 'Not available')); ?></p>
                    <form method="post">
                        <button type="submit" name="edit_address" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-4">Edit Address</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="mt-6">
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Your Order Details</h3>
                </div>
                <div class="p-4">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Select</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Ordered</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Price</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($orders as $OrderID => $order): ?>
                                <?php foreach ($order['Items'] as $item): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="checkbox" name="selectedItems[]" value="<?php echo htmlspecialchars($OrderID); ?>-<?php echo htmlspecialchars($item['ProductName']); ?>">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($OrderID); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($item['ProductName']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($order['DateOrdered']))); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">Rp.<?php echo number_format($item['Price'], 2); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($item['Quantity']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">Rp.<?php echo number_format($item['ItemTotal'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 sm:px-6">
                    <button type="submit" name="proceed_to_payment" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Proceed to Payment</button>
                    <button type="submit" name="cancel_order" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out ml-4">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
