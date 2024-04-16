<?php
session_start();
require_once 'config.php'; // Ensure this file has the correct database connection setup

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['review_submit'])) {
    $order_id = $_POST['order_id'];
    $product_id = $_POST['product_id']; // Assuming product_id is passed along with the form submission
    $buyer_id = $_SESSION["user_id"];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    $date_posted = date("Y-m-d H:i:s"); // Current date and time

    $insert_review_query = "
        INSERT INTO Reviews (ProductID, BuyerID, Rating, Comment, DatePosted) 
        VALUES (?, ?, ?, ?, ?)
    ";

    if ($stmt = mysqli_prepare($conn, $insert_review_query)) {
        mysqli_stmt_bind_param($stmt, "iiiss", $product_id, $buyer_id, $rating, $comment, $date_posted);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION["message"] = "Your review has been posted.";
            header("Location: notifications.php"); // Assuming there is a notifications page to return to
            exit;
        } else {
            $_SESSION["error"] = "Unable to post the review.";
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($conn);
} else {
    $order_id = $_GET['order_id'] ?? null; // Retrieve order_id from the query parameter
    $product_id = null; // This needs to be fetched based on the order_id

    if ($order_id) {
        $fetch_product_query = "
            SELECT ProductID 
            FROM OrderDetails 
            WHERE OrderID = ?
        ";

        if ($stmt = mysqli_prepare($conn, $fetch_product_query)) {
            mysqli_stmt_bind_param($stmt, "i", $order_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                if ($row = $result->fetch_assoc()) {
                    $product_id = $row['ProductID'];
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// If product_id is not set or null, redirect back with an error.
if (null === $product_id) {
    $_SESSION["error"] = "Product not found for the given order.";
    header("Location: notifications.php"); // Assuming there is a notifications page to return to
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Write a Review</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include 'navbar.php'; ?>
    <div class="container mx-auto mt-8">
        <h2 class="text-xl font-bold mb-4">Write a Review</h2>
        <div class="bg-white p-6 rounded-lg shadow">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">
                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product_id); ?>">
                <div class="mb-4">
                    <label for="rating" class="block text-gray-700 text-sm font-bold mb-2">Rating:</label>
                    <select id="rating" name="rating" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <option value="1">1 Star</option>
                        <option value="2">2 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="5" selected>5 Stars</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="comment" class="block text-gray-700 text-sm font-bold mb-2">Comment:</label>
                    <textarea id="comment" name="comment" rows="4" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Write your review here..."></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" name="review_submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Post Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>