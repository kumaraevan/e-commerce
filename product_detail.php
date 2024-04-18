<?php
session_start();
require_once 'config.php';

if (isset($_GET['ProductID']) && is_numeric($_GET['ProductID'])) {
    $product_id = $_GET['ProductID'];
    
    // Fetch the product's details from the database
    $stmt = $conn->prepare("SELECT ProductID, Name, Description, Price, StockQuantity, ImageURLs FROM products WHERE ProductID = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($product = $result->fetch_assoc()) {
    } else {
        echo "Product not found.";
        exit;
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo "No product ID provided.";
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>eCommerce</title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    </head>
    <body class="bg-gray-100">
    <?php include 'C:\xampp\htdocs\eCommerce\dsgn\navbar.php'; ?>
        <div class="container mx-auto mt-6 p-4 bg-white rounded-lg shadow-md">
            <div class="flex flex-col md:flex-row">
                <div class="md:w-1/3">
                    <img class="w-full h-auto max-w-xs mx-auto" src="<?php echo htmlspecialchars($product['ImageURLs']); ?>" alt="<?php echo htmlspecialchars($product['Name']); ?>">
                </div>
                <div class="md:w-2/3 md:pl-6">
                    <h1 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($product['Name']); ?></h1>
                    <p class="mb-4"><?php echo htmlspecialchars($product['Description']); ?></p>
                    <p class="font-semibold mb-1">Price: Rp.<?php echo htmlspecialchars($product['Price']); ?></p>
                    <p class="mb-4">Stocks: <?php echo htmlspecialchars($product['StockQuantity']); ?></p>
                    <div class="flex">
                    <form action="buy_product.php" method="post" class="mr-2">
                        <input type="hidden" name="product_id" value="<?php echo isset($product['ProductID']) ? $product['ProductID'] : ''; ?>">
                        <input type="number" name="quantity" min="1" value="1" class="border-gray-300 rounded-md mr-2 bg-gray-50" style="border: 1px solid #cbd5e1;">
                        <input type="submit" value="Add to Cart" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded cursor-pointer">
                    </form>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>