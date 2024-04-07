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
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include 'navbar.php'; ?>
    <div class="container mx-auto mt-10">
        <h2 class="text-2xl font-bold mb-5 text-center">Payment Details</h2>

        <section class="mb-8">
            <h3 class="text-xl font-semibold mb-3">Shipping Address</h3>
            <p class="bg-white p-4 rounded-lg shadow">
                <?php echo htmlspecialchars($address); ?>
            </p>
        </section>

        <section>
            <h3 class="text-xl font-semibold mb-3">Selected Products</h3>
            <?php if (!empty($products)): ?>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Price</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <!-- Products List -->
                            <?php $grandTotal = 0; ?>
                            <?php foreach ($products as $product): ?>
                            <?php
                                $totalPrice = $product['Quantity'] * $product['PriceAtPurchase'];
                                $grandTotal += $totalPrice;
                            ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($product['Name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($product['Quantity']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp.<?php echo number_format($product['PriceAtPurchase'], 2); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp.<?php echo number_format($totalPrice, 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="bg-gray-50">
                                <td colspan="3" class="text-right px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-500">Grand Total:</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-500">Rp.<?php echo number_format($grandTotal, 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php else: ?>
                <p class="text-center text-gray-600">No products selected. <a href="cart.php" class="text-blue-600 hover:underline">Return to cart</a>.</p>
            <?php endif; ?>
        </section>

        <?php if (!empty($products)): ?>
            <div class="mt-6 text-left ml-4 lg:ml-0">
                <form action="payment_success.php" method="POST">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Confirm Payment</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
mysqli_close($conn);
?>