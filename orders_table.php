<?php
session_start();
require_once 'config.php';

// Redirect non-logged in users or non-admins
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("Location: login.php");
    exit;
}

$feedback_message = "";
$search = $_GET["search"] ?? ''; // Simplified fetching of search term
$search = trim($search);
$whereClause = "";
$params = [];
$queryTypes = '';

if (!empty($search)) {
    $whereClause = "WHERE OrderID LIKE CONCAT('%', ?, '%') 
                    OR BuyerID LIKE CONCAT('%', ?, '%') 
                    OR orderstatus LIKE CONCAT('%', ?, '%') 
                    OR paymentmethod LIKE CONCAT('%', ?, '%') 
                    OR shippingaddress LIKE CONCAT('%', ?, '%')";
    $params = array_fill(0, 5, $search);
    $queryTypes = str_repeat('s', count($params)); // 's' denotes string type for all params
}

$sql = "SELECT * FROM orders $whereClause ORDER BY OrderID ASC";
$stmt = $conn->prepare($sql);

if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($queryTypes, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $feedback_message = "Error preparing query: " . htmlspecialchars($conn->error);
}

if (!$result) {
    $feedback_message = "Error retrieving orders: " . htmlspecialchars($conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Table</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'sidebar.php'; ?>

    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-semibold my-4">Manage Orders</h2>

        <!-- Search bar -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="GET" class="mb-4">
            <div class="flex items-center">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search orders" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 ml-2 rounded focus:outline-none focus:shadow-outline">Search</button>
            </div>
        </form>

        <!-- Display feedback message -->
        <?php echo $feedback_message; ?>

        <table class="table-auto w-full mb-4">
            <thead>
                <tr class="bg-gray-200">
                    <th class="px-4 py-2">Order ID</th>
                    <th class="px-4 py-2">Buyer ID</th>
                    <th class="px-4 py-2">Total Price</th>
                    <th class="px-4 py-2">Order Status</th>
                    <th class="px-4 py-2">Payment Method</th>
                    <th class="px-4 py-2">Shipping Address</th>
                    <th class="px-4 py-2">Date Ordered</th>
                    <th class="px-4 py-2">Edit</th>
                    <th class="px-4 py-2">Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                <tr class="bg-white">
                    <td class="border px-4 py-2"><?php echo htmlspecialchars($row["OrderID"]); ?></td>
                    <td class="border px-4 py-2"><?php echo htmlspecialchars($row["BuyerID"]); ?></td>
                    <td class="border px-4 py-2"><?php echo htmlspecialchars($row["TotalPrice"]); ?></td>
                    <td class="border px-4 py-2"><?php echo htmlspecialchars($row["OrderStatus"]); ?></td>
                    <td class="border px-4 py-2"><?php echo htmlspecialchars($row["PaymentMethod"]); ?></td>
                    <td class="border px-4 py-2"><?php echo htmlspecialchars($row["ShippingAddress"]); ?></td>
                    <td class="border px-4 py-2"><?php echo htmlspecialchars($row["DateOrdered"]); ?></td>
                    <td class="border px-4 py-2"><a href="edit_product.php?id=<?php echo $row["OrderID"]; ?>" class="text-blue-500 hover:text-blue-800">Edit</a></td>
                    <td class="border px-4 py-2"><a href="delete_product.php?id=<?php echo $row["OrderID"]; ?>" onclick="return confirm('Are you sure you want to delete this product?')" class="text-red-500 hover:text-red-800">Delete</a></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <?php $conn->close(); ?>
</body>
</html>