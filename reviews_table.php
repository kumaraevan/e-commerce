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
    $whereClause = "WHERE ReviewID LIKE CONCAT('%', ?, '%') 
                    OR BuyerID LIKE CONCAT('%', ?, '%')";
    $params = array_fill(0, 2, $search);
    $queryTypes = str_repeat('s', count($params)); // 's' denotes string type for all params
}

$sql = "SELECT * FROM reviews $whereClause ORDER BY ReviewID ASC";
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
    <title>Reviews Table</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'sidebar.php'; ?>

    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-semibold my-4">Manage Reviews</h2>

        <!-- Search bar -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="GET" class="mb-4">
            <div class="flex items-center">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search review" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 ml-2 rounded focus:outline-none focus:shadow-outline">Search</button>
            </div>
        </form>

        <!-- Display feedback message -->
        <?php echo $feedback_message; ?>

        <table class="table-auto w-full mb-4">
            <thead>
                <tr class="bg-gray-200">
                    <th class="px-4 py-2">Review ID</th>
                    <th class="px-4 py-2">Product ID</th>
                    <th class="px-4 py-2">Buyer ID</th>
                    <th class="px-4 py-2">Rating</th>
                    <th class="px-4 py-2">Comment</th>
                    <th class="px-4 py-2">Date Posted</th>
                    <th class="px-4 py-2">Edit</th>
                    <th class="px-4 py-2">Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                <tr class="bg-white">
                    <td class="border px-4 py-2"><?php echo htmlspecialchars($row["ReviewID"]); ?></td>
                    <td class="border px-4 py-2"><?php echo htmlspecialchars($row["ProductID"]); ?></td>
                    <td class="border px-4 py-2"><?php echo htmlspecialchars($row["BuyerID"]); ?></td>
                    <td class="border px-4 py-2"><?php echo htmlspecialchars($row["Rating"]); ?></td>
                    <td class="border px-4 py-2"><?php echo htmlspecialchars($row["Comment"]); ?></td>
                    <td class="border px-4 py-2"><?php echo htmlspecialchars($row["DatePosted"]); ?></td>
                    <td class="border px-4 py-2"><a href="edit_product.php?id=<?php echo $row["ReviewID"]; ?>" class="text-blue-500 hover:text-blue-800">Edit</a></td>
                    <td class="border px-4 py-2"><a href="delete_product.php?id=<?php echo $row["ReviewID"]; ?>" onclick="return confirm('Are you sure you want to delete this product?')" class="text-red-500 hover:text-red-800">Delete</a></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <?php $conn->close(); ?>
</body>
</html>