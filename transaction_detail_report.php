<?php
session_start();
require_once 'config.php';

// Redirect non-logged in users or non-admins
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("Location: login.php");
    exit;
}

$report_id = $_GET['id'] ?? '';
$transaction_details = [];
$feedback_message = "";

if (!empty($report_id)) {
    // Prepare a SQL statement to fetch the transaction report details
    $sql = "SELECT tr.ReportID, tr.OrderID, tr.ProductID, tr.PaymentID, 
               u.Name as UserName, u.Address, u.Phone, u.Email,
               o.TotalPrice, o.PaymentMethod, o.OrderStatus,
               p.Name as ProductName, od.Quantity, od.PriceAtPurchase
        FROM transaction_reports tr
        JOIN orders o ON tr.OrderID = o.OrderID
        JOIN users u ON o.BuyerID = u.UserID
        JOIN orderdetails od ON o.OrderID = od.OrderID
        JOIN products p ON od.ProductID = p.ProductID
        WHERE tr.ReportID = ?";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $report_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            $transaction_details = $result->fetch_assoc();
        } else {
            $feedback_message = "Error retrieving transaction report details: " . htmlspecialchars($conn->error);
        }

        $stmt->close();
    } else {
        $feedback_message = "Error preparing query: " . htmlspecialchars($conn->error);
    }
} else {
    $feedback_message = "No report ID provided.";
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Report Detail</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">
    <?php include 'sidebar.php'; ?>

    <div class="container mx-auto px-4 pt-5">
        <?php if (!empty($feedback_message)): ?>
            <div class="mb-4 p-2 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"><?php echo $feedback_message; ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($transaction_details)): ?>
            <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 grid grid-cols-2 gap-4">
                <div> <!-- Left Column -->
                    <h2 class="text-2xl mb-6 text-gray-700">Transaction Report Detail for Report ID: <?php echo htmlspecialchars($transaction_details['ReportID']); ?></h2>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">User Name</label>
                        <p class="text-gray-600"><?php echo htmlspecialchars($transaction_details['UserName']); ?></p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Address</label>
                        <p class="text-gray-600"><?php echo htmlspecialchars($transaction_details['Address']); ?></p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Phone Number</label>
                        <p class="text-gray-600"><?php echo htmlspecialchars($transaction_details['Phone']); ?></p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                        <p class="text-gray-600"><?php echo htmlspecialchars($transaction_details['Email']); ?></p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Order ID</label>
                        <p class="text-gray-600"><?php echo htmlspecialchars($transaction_details['OrderID']); ?></p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Product Name</label>
                        <p class="text-gray-600"><?php echo htmlspecialchars($transaction_details['ProductName']); ?></p>
                    </div>
                </div>

                <div> <!-- Right Column -->
                    <h2 class="text-2xl mb-6 text-gray-700">&nbsp;</h2> <!-- Placeholder for alignment -->
                    <div class="flex mb-4">
                        <div class="w-1/2 mr-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Quantity</label>
                            <p class="text-gray-600"><?php echo htmlspecialchars($transaction_details['Quantity']); ?></p>
                        </div>
                        <div class="w-1/2 ml-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Price at Purchase</label>
                            <p class="text-gray-600"><?php echo htmlspecialchars($transaction_details['PriceAtPurchase']); ?></p>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Total Price</label>
                        <p class="text-gray-600"><?php echo htmlspecialchars($transaction_details['TotalPrice']); ?></p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Payment Method</label>
                        <p class="text-gray-600"><?php echo htmlspecialchars($transaction_details['PaymentMethod']); ?></p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Order Status</label>
                        <p class="text-gray-600"><?php echo htmlspecialchars($transaction_details['OrderStatus']); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>