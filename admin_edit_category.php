<?php
session_start();
require_once 'config.php';

// Redirect non-logged in users or non-admins
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("Location: login.php");
    exit;
}

$category_id = $_GET['id'];

// Retrieve category details based on category ID
$sql = "SELECT * FROM categories WHERE CategoryID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $category_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$category = mysqli_fetch_assoc($result);

if (!$category) {
    echo "Category not found.";
    exit;
}

mysqli_stmt_close($stmt);

$category_name_error = "";
$feedback_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and update category
    $categoryName = trim($_POST["categoryName"]);

    // Basic validation for category name
    if (empty($categoryName)) {
        $category_name_error = "Category name is required";
    } else {
        $sql = "UPDATE categories SET CategoryName = ? WHERE CategoryID = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $categoryName, $category_id);

        if (mysqli_stmt_execute($stmt)) {
            $feedback_message = "<p class='text-green-500'>Category updated successfully!</p>";
        } else {
            $feedback_message = "<p class='text-red-500'>Error updating category: " . htmlspecialchars(mysqli_error($conn)) . "</p>";
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Categories Table</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">
    <?php include 'sidebar.php'; ?>

    <div class="container mx-auto px-4 pt-5">
        <h2 class="text-3xl font-semibold text-gray-800 mb-6">Edit Category</h2>

        <!-- Display feedback message -->
        <?php echo $feedback_message; ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $category_id; ?>" class="mb-4 flex">
            <input type="text" name="categoryName" id="categoryName" value="<?= htmlspecialchars($category["CategoryName"]) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <button type="submit" class="ml-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update</button>
            <p class="text-red-500 text-xs italic ml-4 self-center"><?php echo $category_name_error; ?></p>
        </form>
    </div>

    <?php $conn->close(); ?>
</body>
</html>