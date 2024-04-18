<?php
session_start();
require_once 'config.php';

// Redirect non-logged in users or non-admins
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("Location: login.php");
    exit;
}

$product_id = $_GET['id'];

// Retrieve product details based on product ID
$sql = "SELECT * FROM products WHERE ProductID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    echo "Product not found.";
    exit;
}

mysqli_stmt_close($stmt);

$feedback_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle product update
    // Retrieve form data
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    // Perform update query
    $sql = "UPDATE products SET Name = ?, Description = ?, Price = ? WHERE ProductID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssdi", $name, $description, $price, $product_id);
    if (mysqli_stmt_execute($stmt)) {
        $feedback_message = "<p class='text-green-500'>Product updated successfully!</p>";
    } else {
        $feedback_message = "<p class='text-red-500'>Error updating product: " . mysqli_error($conn) . "</p>";
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">
    <?php include 'sidebar.php'; ?>

    <div class="container mx-auto px-4 pt-5">
        <h2 class="text-3xl font-semibold text-gray-800 mb-6">Edit Product</h2>

        <!-- Display feedback message -->
        <?php echo $feedback_message; ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $product_id; ?>" class="mb-4">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="name">Name</label>
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($product["Name"]); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="description">Description</label>
                <textarea name="description" id="description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required><?php echo htmlspecialchars($product["Description"]); ?></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="price">Price</label>
                <input type="number" name="price" id="price" value="<?php echo htmlspecialchars($product["Price"]); ?>" step="0.01" min="0" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update</button>
                <a href="products_table.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>