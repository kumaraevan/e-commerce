<?php
session_start();
require_once 'config.php';

$logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'];

if (isset($_GET['query'])) {
    $search_query = $conn->real_escape_string($_GET['query']);

    $search_sql = "SELECT ProductID, Name, Description, Price, StockQuantity, ImageURLs FROM products 
            WHERE Name LIKE '%$search_query%' 
            OR Description 
            LIKE '%$search_query%' 
            ORDER BY ProductID DESC";

    $result = $conn->query($sql);

    $search_results = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $search_results[] = $row;
        }
    } else {
        echo "No results found.";
    }
    $conn->close();
} 

$products_query = "SELECT ProductID, Name, Description, Price, StockQuantity, ImageURLs FROM products ORDER BY ProductID DESC LIMIT 10";
$result = $conn->query($products_query);

$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
$conn->close();

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>eCommerce</title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    </head>
    <body class="bg-gray-100">
    <?php include 'navbar.php'; ?>
        <div class="text-center my-8">
            <h1 class="text-4xl font-bold">Welcome To Our eCommerce Website!</h1>
        </div>
        <div class="container mx-auto px-4">
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
                                <p class="text-sm">Stocks: <?php echo htmlspecialchars($product['StockQuantity']); ?></p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                    <p class="px-4">No Products Found</p>
                <?php endif; ?>
            </div>
        </div>

    <?php include 'footer.php'; ?>
    </body>
</html>