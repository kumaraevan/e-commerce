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
<body class="bg-gray-100 font-sans">
    <?php include 'sidebar.php'; ?>

    <div class="container mx-auto px-4 pt-5">
        <h2 class="text-3xl font-semibold text-gray-800 mb-6">Manage Orders</h2>

        <!-- Search bar -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="GET" class="mb-4 flex">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search orders" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <button type="submit" class="ml-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Search</button>
        </form>

        <!-- Display feedback message -->
        <?php echo $feedback_message; ?>

        <div class="bg-white shadow overflow-hidden rounded-lg">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">Order ID</th>
                        <th class="py-3 px-6 text-left">Buyer ID</th>
                        <th class="py-3 px-6 text-left">Total Price</th>
                        <th class="py-3 px-6 text-left">Order Status</th>
                        <th class="py-3 px-6 text-left">Payment Method</th>
                        <th class="py-3 px-6 text-left">Shipping Address</th>
                        <th class="py-3 px-6 text-left">Date Ordered</th>
                        <th class="py-3 px-6 text-left">Edit</th>
                        <th class="py-3 px-6 text-left">Delete</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                        <td class="py-3 px-6 text-left"><?= htmlspecialchars($row["OrderID"]) ?></td>
                        <td class="py-3 px-6 text-left"><?= htmlspecialchars($row["BuyerID"]) ?></td>
                        <td class="py-3 px-6 text-left"><?= htmlspecialchars($row["TotalPrice"]) ?></td>
                        <td class="py-3 px-6 text-left"><?= htmlspecialchars($row["OrderStatus"]) ?></td>
                        <td class="py-3 px-6 text-left"><?= htmlspecialchars($row["PaymentMethod"]) ?></td>
                        <td class="py-3 px-6 text-left"><?= htmlspecialchars($row["ShippingAddress"]) ?></td>
                        <td class="py-3 px-6 text-left"><?= htmlspecialchars($row["DateOrdered"]) ?></td>
                        <td class="py-3 px-6 text-left"><a href="edit_order.php?id=<?= $row["OrderID"]; ?>" class="text-blue-500 hover:text-blue-800"><i class="fas fa-edit"></i></a></td>
                        <td class="py-3 px-6 text-left"><a href="delete_order.php?id=<?= $row["OrderID"]; ?>" onclick="return confirm('Are you sure you want to delete this order?')" class="text-red-500 hover:text-red-800"><i class="fas fa-trash-alt"></i></a></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php $conn->close(); ?>
</body>
</html>