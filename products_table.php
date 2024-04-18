<?php
session_start();
require_once 'config.php';

// Redirect non-logged in users or non-admins
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("Location: login.php");
    exit;
}

$feedback_message = "";  // Initialize success or error feedback message
$search = "";
$whereClause = "";

// Check if search query is provided
if (isset($_GET["search"]) && !empty(trim($_GET["search"]))) {
    $search = trim($_GET["search"]);
    // Construct the WHERE clause to search by product name or product ID
    $whereClause = "WHERE Name LIKE '%$search%' OR ProductID = '$search' OR SellerID = '$search'";
}

// Query to retrieve existing products with search filter
$sql = "SELECT * FROM products $whereClause";
$result = $conn->query($sql);

// Check for query errors
if (!$result) {
    $feedback_message = "Error retrieving products: " . htmlspecialchars($conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Table</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'sidebar.php'; ?>

    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-semibold my-4">Manage Products</h2>

        <!-- Search bar -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="GET" class="mb-4">
            <div class="flex items-center">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search products" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 ml-2 rounded focus:outline-none focus:shadow-outline">Search</button>
            </div>
        </form>

        <!-- Display feedback message -->
        <?php echo $feedback_message; ?>

        <table class="table-auto w-full mb-4">
            <thead>
                <tr class="bg-gray-200">
                    <th class="px-4 py-2">Product ID</th>
                    <th class="px-4 py-2">Seller ID</th>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Description</th>
                    <th class="px-4 py-2">Price</th>
                    <th class="px-4 py-2">Edit</th>
                    <th class="px-4 py-2">Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                <tr class="bg-white">
                    <td class="border px-4 py-2"><?php echo htmlspecialchars($row["ProductID"]); ?></td>
                    <td class="border px-4 py-2"><?php echo htmlspecialchars($row["SellerID"]); ?></td>
                    <td class="border px-4 py-2"><?php echo htmlspecialchars($row["Name"]); ?></td>
                    <td class="border px-4 py-2"><?php echo htmlspecialchars($row["Description"]); ?></td>
                    <td class="border px-4 py-2"><?php echo htmlspecialchars($row["Price"]); ?></td>
                    <td class="border px-4 py-2"><a href="edit_product.php?id=<?php echo $row["ProductID"]; ?>" class="text-blue-500 hover:text-blue-800">Edit</a></td>
                    <td class="border px-4 py-2"><a href="delete_product.php?id=<?php echo $row["ProductID"]; ?>" onclick="return confirm('Are you sure you want to delete this product?')" class="text-red-500 hover:text-red-800">Delete</a></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <?php $conn->close(); ?>
</body>
</html>