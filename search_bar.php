<?php
session_start();
require_once 'config.php';

if (isset($_GET['query'])) {
    $search_query = $conn->real_escape_string($_GET['query']);

    $sql = "SELECT ProductID, Name, Description, Price, StockQuantity, ImageURLs FROM products WHERE Name LIKE '%$search_query%' OR Description LIKE '%$search_query%' ORDER BY ProductID DESC";
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
} else {
    // Redirect to home page or show all products if query is not set
    header("Location: index.php");
}
?>
<!-- Search Bar Replacement -->
<div class="relative">
    <form action="search.php" method="get">
        <input type="text" name="query" class="bg-gray-800 text-white px-4 py-2 rounded-l" placeholder="Search...">
        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded-r">
            Search
        </button>
    </form>
</div>
