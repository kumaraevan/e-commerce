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
$sql = "SELECT * FROM categories ORDER BY CategoryID";
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
    <title>Categories Table</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">
    <?php include 'sidebar.php'; ?>

    <div class="container mx-auto px-4 pt-5">
        <h2 class="text-3xl font-semibold text-gray-800 mb-6">Manage Categories</h2>

        <!-- Display feedback message -->
        <?php echo $feedback_message; ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="mb-4 flex">
            <input type="text" name="categoryName" id="categoryName" placeholder="Add category" required class="px-4 py-2 rounded-l-md focus:outline-none focus:ring focus:border-blue-300 w-full">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-r-md">
                        <i class="fas fa-plus-circle"></i>
                    </button>
            <p class="text-red-500 text-xs italic ml-4 self-center"><?php echo $category_name_error; ?></p>
        </form>

        <div class="bg-white shadow overflow-hidden rounded-lg">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">Category ID</th>
                        <th class="py-3 px-6 text-left">Category Name</th>
                        <th class="py-3 px-6 text-left">Edit</th>
                        <th class="py-3 px-6 text-left">Delete</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="py-3 px-6 text-left whitespace-nowrap"><?= htmlspecialchars($row["CategoryID"]) ?></td>
                            <td class="py-3 px-6 text-left"><?= htmlspecialchars($row["CategoryName"]) ?></td>
                            <td class="py-3 px-6 text-left"><a href="admin_edit_category.php?id=<?= $row["CategoryID"]; ?>" class="text-blue-500 hover:text-blue-800"><i class="fas fa-edit"></i></a></td>
                            <td class="py-3 px-6 text-left"><a href="admin_delete_category.php?id=<?= $row["CategoryID"]; ?>" onclick="return confirm('Are you sure you want to delete this category?')" class="text-red-500 hover:text-red-800"><i class="fas fa-trash-alt"></i></a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php $conn->close(); ?>
</body>
</html>