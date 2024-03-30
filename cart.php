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

// Prepare a statement to fetch order details along with product information
$order_sql = "SELECT o.OrderID, o.DateOrdered, p.Name as ProductName, p.Price, od.Quantity, (p.Price * od.Quantity) as ItemTotal 
              FROM orders o
              INNER JOIN orderdetails od ON o.OrderID = od.OrderID
              INNER JOIN products p ON od.ProductID = p.ProductID
              WHERE o.BuyerID = ?
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

// Close the connection
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

        <h3>Your Order Details</h3>
        <?php if (!empty($orders)): ?>
            <form action="payment.php" method="post">
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
                    <?php 
                    // Initialize grand total
                    $grandTotal = 0;
                    foreach ($orders as $orderId => $order):
                        foreach ($order['Items'] as $index => $item):
                            $grandTotal += $item['ItemTotal'];
                    ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="selectedItems[]" value="<?php echo htmlspecialchars($item['ProductName']); ?>-<?php echo htmlspecialchars($orderId); ?>">
                            </td>
                            <?php if ($index === 0): ?>
                                <td rowspan="<?php echo count($order['Items']); ?>"><?php echo htmlspecialchars($orderId); ?></td>
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
                    <tr>
                        <td colspan="6" style="text-align: right;"><strong>Grand Total</strong></td>
                        <td>Rp.<?php echo number_format($grandTotal, 2); ?></td>
                    </tr>
                </table>
                <br>
                <input type="submit" value="Proceed to Checkout">
                <input type="reset" value="Clear Selections">
            </form>
        <?php else: ?>
            <p>You have no items in your cart.</p>
        <?php endif; ?>
    </body>
</html>