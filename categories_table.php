<?php
session_start();
require_once 'config.php';

// Redirect non-logged in users or non-admins
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("Location: login.php");
    exit;
}

$category_name_error = ""; // Initialize error variable
$feedback_message = "";  // Initialize success or error feedback message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and add new category
    $categoryName = trim($_POST["categoryName"]);

    // Basic validation for category name
    if (empty($categoryName)) {
        $category_name_error = "Category name is required";
    } else {
        $sql = "INSERT INTO categories (CategoryName) VALUES (?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $categoryName);

        if (mysqli_stmt_execute($stmt)) {
            $feedback_message = "<p class='text-green-500'>Category added successfully!</p>";
        } else {
            $feedback_message = "<p class='text-red-500'>Error adding category: " . htmlspecialchars(mysqli_error($conn)) . "</p>";
        }

        mysqli_stmt_close($stmt);
    }
}

// Query to retrieve existing categories
$sql = "SELECT * FROM categories";
$result = $conn->query($sql);

// Check for query errors
if (!$result) {
    die("Error retrieving categories: " . htmlspecialchars(mysqli_error($conn)));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories Table</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'sidebar.php'; ?>

    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-semibold my-4">Manage Categories</h2>

        <!-- Display feedback message -->
        <?php echo $feedback_message; ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="mb-4">
            <div class="flex items-center">
                <input type="text" name="categoryName" id="categoryName" placeholder="Add Category" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline ml-2">Add</button>
            </div>
            <p class="text-red-500 text-xs italic"><?php echo $category_name_error; ?></p>
        </form>

        <table class="table-auto w-full mb-4">
            <thead>
                <tr class="bg-gray-200">
                    <th class="px-4 py-2">Category ID</th>
                    <th class="px-4 py-2">Category Name</th>
                    <th class="px-4 py-2">Edit</th>
                    <th class="px-4 py-2">Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                <tr class="bg-white">
                    <td class="border px-4 py-2"><?php echo htmlspecialchars($row["CategoryID"]); ?></td>
                    <td class="border px-4 py-2"><?php echo htmlspecialchars($row["CategoryName"]); ?></td>
                    <td class="border px-4 py-2"><a href="edit_category.php?id=<?php echo $row["CategoryID"]; ?>" class="text-blue-500 hover:text-blue-800">Edit</a></td>
                    <td class="border px-4 py-2"><a href="delete_category.php?id=<?php echo $row["CategoryID"]; ?>" onclick="return confirm('Are you sure you want to delete this category?')" class="text-red-500 hover:text-red-800">Delete</a></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <?php $conn->close(); ?>
</body>
</html>