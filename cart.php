<?php
session_start();
require_once 'config.php';

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['proceed_to_payment'])) {
    $_SESSION['selected_items'] = $_POST['selectedItems'] ?? [];
    header("Location: payment.php");
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

// Handle address update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_address'])) {
    $new_address = trim($_POST['address']);
    $update_query = "UPDATE Users SET Address = ? WHERE UserID = ?";
    if ($update_stmt = mysqli_prepare($conn, $update_query)) {
        mysqli_stmt_bind_param($update_stmt, "si", $new_address, $user_id);
        if (mysqli_stmt_execute($update_stmt)) {
            // Update successful
            $user_data['Address'] = $new_address; // Update address in the current session
        } else {
            // Update failed
            echo "Error updating record: " . $conn->error;
        }
        mysqli_stmt_close($update_stmt);
    }
}

// Check if the form is submitted to update the address
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_address'])) {
    $new_address = trim($_POST['address']);

    // Prepare an update statement
    $update_sql = "UPDATE Users SET Address = ? WHERE UserID = ?";
    if ($update_stmt = mysqli_prepare($conn, $update_sql)) {
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($update_stmt, "si", $new_address, $user_id);
        
        // Attempt to execute the prepared statement
        if (mysqli_stmt_execute($update_stmt)) {
            // Reload the user data to reflect the address change
            $user['Address'] = $new_address;
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        
        // Close statement
        mysqli_stmt_close($update_stmt);
    }
}


// Prepare a statement to fetch order details along with product information
$order_sql = "SELECT o.OrderID, o.DateOrdered, p.Name as ProductName, p.Price, od.Quantity, (p.Price * od.Quantity) as ItemTotal 
              FROM orders o
              INNER JOIN orderdetails od ON o.OrderID = od.OrderID
              INNER JOIN products p ON od.ProductID = p.ProductID
              WHERE o.BuyerID = ? AND o.OrderStatus <> 'PaymentConfirmed'
              ORDER BY o.DateOrdered DESC";
if ($stmt = mysqli_prepare($conn, $order_sql)) {
    // Bind variables to the prepared statement as parameters
    mysqli_stmt_bind_param($stmt, "i", $user_id);

    // Execute the statement
    if (mysqli_stmt_execute($stmt)) {
        // Store the result so we can check if the record exists
        $result = mysqli_stmt_get_result($stmt);

        // Fetch all the orders and product details and store them in the $orders array
        while ($row = $result->fetch_assoc()) {
            // Group orders by OrderID
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

    // Close the statement
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
    <nav class="bg-gray-900 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="hover:bg-gray-700 px-3 py-2 rounded">Home</a>
            <a href="#products" class="hover:bg-gray-700 px-3 py-2 rounded">Products</a>
            <a href="#search" class="hover:bg-gray-700 px-3 py-2 rounded">Search</a>
            <a href="#about" class="hover:bg-gray-700 px-3 py-2 rounded">About</a>
            <div class="flex space-x-4">
                <a href="account.php" class="hover:bg-gray-700 px-3 py-2 rounded">My Account</a>
                <a href="cart.php" class="hover:bg-gray-700 px-3 py-2 rounded">Cart (0)</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto mt-6 p-4">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <!-- Address Update Form -->
            <?php if(isset($_POST['edit_address'])): ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Edit Shipping Address</h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <textarea name="address" rows="4" class="mt-1 block w-full rounded-md border-gray-400 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-300 focus:ring-opacity-50 bg-gray-50" ><?php echo htmlspecialchars($user_data['Address'] ?? ''); ?></textarea>
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
                </div>
            </div>
        </form>
    </div>
</body>
</html>