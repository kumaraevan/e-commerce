<?php
session_start();
require_once 'config.php';

// Initialize search results array
$search_results = [];

if (isset($_GET['query'])) {
    // Escape the search term to prevent SQL Injection
    $search_term = $conn->real_escape_string($_GET['query']);

    // Build the query
    $search_sql = "
        SELECT 
            p.ProductID, p.Name, p.Description, p.Price, p.StockQuantity, p.ImageURLs,
            c.CategoryName,
            s.Name AS SellerName
        FROM 
            products p
        LEFT JOIN 
            categories c ON p.CategoryID = c.CategoryID
        LEFT JOIN 
            users s ON p.SellerID = s.UserID
        WHERE 
            p.Name LIKE '%$search_term%' 
            OR p.Description LIKE '%$search_term%'
        ORDER BY 
            p.ProductID DESC";

    // Execute the query
    $result = $conn->query($search_sql);

    // Fetch the results
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $search_results[] = $row;
        }
    } else {
        $search_results['error'] = "No results found for '$search_term'.";
    }

    // Close the connection
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Search Results</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<?php include 'C:\xampp\htdocs\eCommerce\dsgn\navbar.php'; ?>
    <div class="container mx-auto px-4 my-8">
        <h2 class="text-xl mb-4">Search Results for: <?php echo htmlspecialchars($_GET['query']); ?></h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php if (!empty($search_results) && empty($search_results['error'])): ?>
                <?php foreach ($search_results as $product): ?>
                    <a href="product_detail.php?ProductID=<?php echo $product['ProductID']; ?>" class="block bg-white rounded-lg shadow hover:shadow-md overflow-hidden">
                        <img src="<?php echo htmlspecialchars($product['ImageURLs']); ?>" alt="Product Image" class="w-full h-48 object-cover">
                        <div class="p-4">
                            <h3 class="font-bold truncate"><?php echo htmlspecialchars($product['Name']); ?></h3>
                            <p class="text-gray-800"><?php echo htmlspecialchars($product['CategoryName']); ?></p>
                            <p class="text-gray-600"><?php echo htmlspecialchars($product['Description']); ?></p>
                            <p class="text-lg font-semibold">Rp<?php echo htmlspecialchars($product['Price']); ?></p>
                            <p class="text-gray-600">Stock: <?php echo htmlspecialchars($product['StockQuantity']); ?></p>
                            <p class="text-gray-600">Seller: <?php echo htmlspecialchars($product['SellerName']); ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center col-span-full"><?php echo $search_results['error']; ?></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>