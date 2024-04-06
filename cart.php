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
        <style>
            .navbar {
                overflow: hidden;
                background-color: #333;
            }

            .navbar a {
                float: left;
                display: block;
                color: white;
                text-align: center;
                padding: 14px 20px;
                text-decoration: none;
            }

            .navbar-right {
                float: right;
            }

            .navbar::after {
                content: "";
                display: table;
                clear: both;
            }

            .navbar a:hover {
                background-color: #ddd;
                color: black;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            table, th, td {
                border: 1px solid #ddd;
            }

            th, td {
                padding: 8px;
                text-align: left;
            }

            th {
                background-color: #f2f2f2;
            }
        </style>
    </head>
    <body>
        <div class="navbar">
            <a href="index.php">Home</a>
            <a href="#products">Products</a>
            <a href="#search">Search</a>
            <a href="#about">About</a>

        <div class="navbar-right">
            <a href="account.php">My Account</a>
            <a href="cart.php">Cart (0)</a> <!-- Update '0' with dynamic cart count -->
        </div>
        </div>
        
        <div class="container mx-auto mt-6 p-4 border border-gray-200 rounded-lg">
            <h2 class="text-lg font-semibold mb-4">Current Shipping Address</h2>
            <p><?php echo nl2br(htmlspecialchars($user_data['Address'] ?? 'Not available')); ?></p>
            <form method="post">
                <input type="submit" name="edit_address" value="Edit Address" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-4">
            </form>
        </div>

        <?php if(isset($_POST['edit_address'])): ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="container mx-auto mt-6 p-4 border border-gray-200 rounded-lg">
                <h3 class="text-lg font-semibold mb-4">Edit Shipping Address</h3>
                <textarea name="address" class="border border-gray-300 rounded-lg w-full p-2" rows="4"><?php echo htmlspecialchars($user_data['Address'] ?? ''); ?></textarea>
                <div class="mt-4">
                    <input type="submit" name="update_address" value="Update" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                </div>
            </div>
        </form>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <h3>Your Order Details</h3>
        <?php if (!empty($orders)): ?>
        <table>
            <tr>
                <th>Select</th>
                <th>Order ID</th>
                <th>Product Name</th>
                <th>Date Ordered</th>
                <th>Unit Price</th>
                <th>Quantity</th>
                <th>Total Price</th>
            </tr>
            <?php foreach ($orders as $OrderID => $order): ?>
                <?php foreach ($order['Items'] as $index => $item): ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="selectedItems[]" value="<?php echo htmlspecialchars($OrderID); ?>-<?php echo htmlspecialchars($item['ProductName']); ?>">
                        </td>
                        <?php if ($index === 0): ?>
                            <td rowspan="<?php echo count($order['Items']); ?>"><?php echo htmlspecialchars($OrderID); ?></td>
                            <td><?php echo htmlspecialchars($item['ProductName']); ?></td>
                            <td rowspan="<?php echo count($order['Items']); ?>"><?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($order['DateOrdered']))); ?></td>
                        <?php else: ?>
                            <td><?php echo htmlspecialchars($item['ProductName']); ?></td>
                        <?php endif; ?>
                        <td>Rp.<?php echo number_format($item['Price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($item['Quantity']); ?></td>
                        <td>Rp.<?php echo number_format($item['ItemTotal'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </table><br>
        <div class="mt-4">
            <input type="submit" name="proceed_to_payment" value="Proceed to Payment" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
        </div>
    <?php else: ?>
        <p>You have no items in your cart.</p>
    <?php endif; ?>
    </form>

    </body>
</html>