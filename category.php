<?php
session_start();
require_once 'config.php';

// Fetch categories for dropdown menu
$categories_stmt = $conn->prepare("SELECT CategoryID, CategoryName FROM categories");
$categories_stmt->execute();
$categories_result = $categories_stmt->get_result();
$categories = $categories_result->fetch_all(MYSQLI_ASSOC);
$categories_stmt->close();

// Handle category selection
$products = [];
if (isset($_GET['category'])) {
    $category_id = $_GET['category'];
    $products_stmt = $conn->prepare("SELECT * FROM products WHERE CategoryID = ? ORDER BY Name");
    $products_stmt->bind_param("i", $category_id);
} else {
    $products_stmt = $conn->prepare("SELECT * FROM products ORDER BY RAND() LIMIT 10"); // Adjust limit as needed
}

$products_stmt->execute();
$products_result = $products_stmt->get_result();
while ($row = $products_result->fetch_assoc()) {
    $products[] = $row;
}
$products_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Category Selection - eCommerce</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include 'navbar.php'; ?>

    <div class="container mx-auto mt-6 p-4 bg-white rounded-lg shadow-md">
    <form action="category.php" method="get" class="mb-4">
        <select name="category" onchange="this.form.submit()" class="border-2 border-blue-500 text-blue-500 text-sm rounded-md bg-white hover:border-blue-600 focus:outline-none focus:border-blue-600 p-2">
            <option value="">Select Category</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['CategoryID']; ?>" <?php if (isset($_GET['category']) && $_GET['category'] == $category['CategoryID']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($category['CategoryName']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
        <div class="flex flex-wrap -mx-4">
            <?php foreach ($products as $product): ?>
                <div class="w-full sm:w-1/2 md:w-1/3 lg:w-1/4 p-4">
                    <a href="product_detail.php?ProductID=<?php echo $product['ProductID']; ?>" class="block bg-white rounded-lg shadow hover:shadow-md overflow-hidden">
                        <div class="p-4">
                            <h3 class="font-bold truncate"><?php echo htmlspecialchars($product['Name']); ?></h3>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($product['Description']); ?></p>
                            <p class="text-lg font-semibold">Rp.<?php echo htmlspecialchars($product['Price']); ?></p>
                        </div>
                        <?php if (!empty($product['ImageURLs'])): ?>
                            <img src="<?php echo htmlspecialchars($product['ImageURLs']); ?>" alt="Product Image" class="w-full h-48 object-cover">
                        <?php endif; ?>
                        <div class="p-4">
                            <p class="text-sm">Stock: <?php echo htmlspecialchars($product['StockQuantity']); ?></p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
            <?php if (empty($products)): ?>
                <p class="px-4">No Products Found</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>