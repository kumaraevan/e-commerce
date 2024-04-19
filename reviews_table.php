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
                    OR ProductID LIKE CONCAT('%', ?, '%') 
                    OR BuyerID LIKE CONCAT('%', ?, '%')";
    $params = array_fill(0, 3, $search);
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
    $feedback_message = "Error retrieving reviews: " . htmlspecialchars($conn->error);
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
<body class="bg-gray-100 font-sans">
    <?php include 'sidebar.php'; ?>

    <div class="container mx-auto px-4 pt-5">
        <h2 class="text-3xl font-semibold text-gray-800 mb-6">Manage Reviews</h2>

        <!-- Search bar -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="GET" class="mb-4">
            <div class="flex items-center">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search reviews" class="px-4 py-2 rounded-l-md focus:outline-none focus:ring focus:border-blue-300 w-full">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-r-md">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>

        <!-- Display feedback message -->
        <?php echo $feedback_message; ?>

        <div class="bg-white shadow overflow-hidden rounded-lg">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">Review ID</th>
                        <th class="py-3 px-6 text-left">Product ID</th>
                        <th class="py-3 px-6 text-left">Buyer ID</th>
                        <th class="py-3 px-6 text-left">Rating</th>
                        <th class="py-3 px-6 text-left">Comment</th>
                        <th class="py-3 px-6 text-left">Date Posted</th>
                        <th class="py-3 px-6 text-left">Edit</th>
                        <th class="py-3 px-6 text-left">Delete</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row["ReviewID"]); ?></td>
                        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row["ProductID"]); ?></td>
                        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row["BuyerID"]); ?></td>
                        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row["Rating"]); ?></td>
                        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row["Comment"]); ?></td>
                        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row["DatePosted"]); ?></td>
                        <td class="py-3 px-6 text-left"><a href="admin_edit_review.php?id=<?php echo $row["ReviewID"]; ?>" class="text-blue-500 hover:text-blue-800"><i class="fas fa-edit"></i></a></td>
                        <td class="py-3 px-6 text-left"><a href="admin_delete_review.php?id=<?php echo $row["ReviewID"]; ?>" onclick="return confirm('Are you sure you want to delete this review?')" class="text-red-500 hover:text-red-800"><i class="fas fa-trash-alt"></i></a></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php $conn->close(); ?>
</body>
</html>