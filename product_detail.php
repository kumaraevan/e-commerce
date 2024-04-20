<?php
session_start();
require_once 'config.php';

// Initialize an array to store product details and reviews
$product_details = [];
$reviews = [];

if (isset($_GET['ProductID']) && is_numeric($_GET['ProductID'])) {
    $product_id = $_GET['ProductID'];
    
    // Fetch the product's details from the database
    $stmt_product = $conn->prepare("SELECT ProductID, Name, Description, Price, StockQuantity, ImageURLs FROM products WHERE ProductID = ?");
    $stmt_product->bind_param("i", $product_id);
    $stmt_product->execute();
    $result_product = $stmt_product->get_result();
    
    if ($product_details = $result_product->fetch_assoc()) {
        // Fetch reviews for the product
        $stmt_reviews = $conn->prepare("SELECT Rating, Comment, DatePosted FROM reviews WHERE ProductID = ?");
        $stmt_reviews->bind_param("i", $product_id);
        $stmt_reviews->execute();
        $result_reviews = $stmt_reviews->get_result();
        
        while ($review = $result_reviews->fetch_assoc()) {
            $reviews[] = $review;
        }
        $stmt_reviews->close();
    } else {
        echo "Product not found.";
        exit;
    }
    
    $stmt_product->close();
} else {
    echo "No product ID provided.";
    exit;
}

// Close the database connection
$conn->close();
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
                <img class="w-full h-auto max-w-xs mx-auto" src="<?php echo htmlspecialchars($product_details['ImageURLs']); ?>" alt="<?php echo htmlspecialchars($product_details['Name']); ?>">
            </div>
            <div class="md:w-2/3 md:pl-6">
                <h1 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($product_details['Name']); ?></h1>
                <p class="mb-4"><?php echo htmlspecialchars($product_details['Description']); ?></p>
                <p class="font-semibold mb-1">Price: Rp.<?php echo htmlspecialchars($product_details['Price']); ?></p>
                <p class="mb-4">Stocks: <?php echo htmlspecialchars($product_details['StockQuantity']); ?></p>
                <div class="flex">
                    <form action="buy_product.php" method="post" class="mr-2">
                        <input type="hidden" name="product_id" value="<?php echo $product_details['ProductID']; ?>">
                        <input type="number" name="quantity" min="1" value="1" class="border-gray-300 rounded-md mr-2 bg-gray-50" style="border: 1px solid #cbd5e1;">
                        <input type="submit" value="Add to Cart" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded cursor-pointer">
                    </form>
                </div>
            </div>
        </div>
        <!-- Reviews Section -->
        <div class="reviews-container mt-8">
            <h2 class="text-lg font-bold mb-4">Reviews:</h2>
            <?php foreach ($reviews as $review): ?>
                <div class="review bg-gray-50 p-4 rounded-lg shadow mb-4">
                    <p class="text-sm"><?php echo htmlspecialchars($review['Comment']); ?></p>
                    <p class="text-sm font-semibold">Rating: <?php echo htmlspecialchars($review['Rating']); ?>/5</p>
                    <p class="text-xs text-gray-600">Posted on: <?php echo htmlspecialchars($review['DatePosted']); ?></p>
                </div>
            <?php endforeach; ?>
            <?php if (empty($reviews)): ?>
                <p>No reviews yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>