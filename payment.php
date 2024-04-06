<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$selected_items = $_SESSION['selected_items'] ?? [];
$address_query = "SELECT Address FROM Users WHERE UserID = ?";
$address = '';

// Prepare and execute the statement for the user's address
if ($address_stmt = mysqli_prepare($conn, $address_query)) {
    mysqli_stmt_bind_param($address_stmt, "i", $user_id);
    if (mysqli_stmt_execute($address_stmt)) {
        $address_result = mysqli_stmt_get_result($address_stmt);
        $address_data = $address_result->fetch_assoc();
        $address = $address_data['Address'];
    }
    mysqli_stmt_close($address_stmt);
}

// Array to hold product details
$products = [];

// Loop over each selected item to fetch product details
foreach ($selected_items as $item) {
    // Parse the order ID and product name from the selected item
    list($OrderID, $productName) = explode('-', $item);

    // Query to get product details based on OrderID and ProductName
    $product_query = "SELECT p.ProductID, p.Name, od.Quantity, od.PriceAtPurchase 
                      FROM OrderDetails od 
                      INNER JOIN Products p ON od.ProductID = p.ProductID 
                      INNER JOIN Orders o ON o.OrderID = od.OrderID
                      WHERE o.OrderID = ? AND p.Name = ? AND o.BuyerID = ?";

    // Prepare and execute the statement to fetch product details
    if ($product_stmt = mysqli_prepare($conn, $product_query)) {
        mysqli_stmt_bind_param($product_stmt, "isi", $OrderID, $productName, $user_id);
        if (mysqli_stmt_execute($product_stmt)) {
            $product_result = mysqli_stmt_get_result($product_stmt);
            while ($product_row = $product_result->fetch_assoc()) {
                $products[] = $product_row;
            }
        }
        mysqli_stmt_close($product_stmt);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $update_order_query = "UPDATE Orders SET OrderStatus = 'PaymentConfirmed' WHERE BuyerID = ?";

if ($update_stmt = mysqli_prepare($conn, $update_order_query)) {
    mysqli_stmt_bind_param($update_stmt, "i", $user_id);

    if (mysqli_stmt_execute($update_stmt)) {
        // Redirect to payment success page
        header("Location: payment_success.php");
        exit();
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }

    mysqli_stmt_close($update_stmt);
}

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment</title>
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

    <h2>Payment Details</h2>
    
    <section>
        <h3>Shipping Address</h3>
        <address>
            <?php echo htmlspecialchars($address); ?>
        </address>
    </section>
    
    <section>
        <h3>Selected Products</h3>
        <?php if (!empty($products)): ?>
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total Price</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $mergedProducts = [];
                foreach ($products as $product) {
                    $key = $product['ProductID']; // Assuming Name is unique per product, change this if you have a ProductID or similar
                    if (!isset($mergedProducts[$key])) {
                        $mergedProducts[$key] = $product;
                    } else {
                        $mergedProducts[$key]['Quantity'] += $product['Quantity'];
                        $mergedProducts[$key]['PriceAtPurchase'] = max($mergedProducts[$key]['PriceAtPurchase'], $product['PriceAtPurchase']); // Adjust if needed
                    }
                }

                $grandTotal = 0;
                foreach ($mergedProducts as $product):
                    $totalPrice = $product['Quantity'] * $product['PriceAtPurchase'];
                    $grandTotal += $totalPrice;
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['Name']); ?></td>
                        <td><?php echo htmlspecialchars($product['Quantity']); ?></td>
                        <td>Rp.<?php echo number_format($product['PriceAtPurchase'], 2); ?></td>
                        <td>Rp.<?php echo number_format($totalPrice, 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" style="text-align: right;"><strong>Grand Total:</strong></td>
                    <td>Rp.<?php echo number_format($grandTotal, 2); ?></td>
                </tr>
            </tbody>
        </table>
        <?php else: ?>
            <p>No products selected. <a href="cart.php">Return to cart</a>.</p>
        <?php endif; ?>
    </section><br>
    
    <?php if (!empty($products)): ?>
        <form action="payment_success.php" method="POST">
            <button type="submit">Confirm Payment</button>
        </form>
    <?php endif; ?>

</body>
</html>

<?php
mysqli_close($conn);
?>