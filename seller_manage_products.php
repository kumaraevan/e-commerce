<?php
session_start();
require_once 'config.php';

// Redirect if not logged in or if the user is not a seller or admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || ($_SESSION["role"] !== 'seller' && $_SESSION["role"] !== 'admin')) {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];
$products = [];

// Fetch seller's products, adjust SELECT query based on your actual database schema
$sql = "SELECT p.ProductID, p.Name, p.Price, p.Description, p.StockQuantity, c.CategoryName, p.ImageURLs 
        FROM products AS p 
        LEFT JOIN categories AS c ON p.CategoryID = c.CategoryID 
        WHERE p.SellerID = ? 
        ORDER BY p.ProductID DESC";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-gray-900 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="seller_dashboard.php" class="hover:bg-gray-700 px-3 py-2 rounded">Dashboard</a>
            <a href="seller_add_new_products.php" class="hover:bg-gray-700 px-3 py-2 rounded">Add New Products</a>
            <a href="seller_manage_products.php" class="hover:bg-gray-700 px-3 py-2 rounded">Manage Products</a>
            <a href="seller_orders.php" class="hover:bg-gray-700 px-3 py-2 rounded">View Orders</a>
            <div class="flex space-x-4">
                <a href="logout.php" class="hover:bg-gray-700 px-3 py-2 rounded">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto mt-10">
        <h2 class="text-2xl font-bold mb-5 text-center">Manage Your Products</h2>
        <div class="flex justify-start mb-5">
            <a href="seller_add_new_products.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Add New Product</a>
        </div>  
        <div class="flex flex-col">
            <div class="overflow-x-auto">
                <div class="align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-600 text-white">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-100 uppercase tracking-wider">No.</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-100 uppercase tracking-wider">Product Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-100 uppercase tracking-wider">Price</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-100 uppercase tracking-wider">Description</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-100 uppercase tracking-wider">Stock Quantity</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-100 uppercase tracking-wider">Category</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-100 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php $counter = 1; ?>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $counter++; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($product['Name']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp.<?php echo htmlspecialchars($product['Price']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($product['Description']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($product['StockQuantity']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($product['CategoryName']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="edit_product.php?ProductID=<?php echo $product['ProductID']; ?>" class="text-blue-400 hover:text-blue-600">Edit</a> |
                                            <form action="delete_product.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                <input type="hidden" name="product_id" value="<?php echo $product['ProductID']; ?>">
                                                <button type="submit" class="text-red-400 hover:text-red-600">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>