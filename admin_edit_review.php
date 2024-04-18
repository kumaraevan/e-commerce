<?php
session_start();
require_once 'config.php';

// Redirect non-logged in users or non-admins
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("Location: login.php");
    exit;
}

$feedback_message = "";

// Check if review ID is provided
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $review_id = $_GET['id'];

    // Fetch review details based on review ID
    $sql = "SELECT * FROM reviews WHERE ReviewID = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $review_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            // You can use $row to access review details
        } else {
            $feedback_message = "Review not found.";
        }

        $stmt->close();
    } else {
        $feedback_message = "Error fetching review: " . htmlspecialchars($conn->error);
    }
} else {
    $feedback_message = "Review ID not provided.";
}

// Handle form submission for updating review
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $new_rating = $_POST['rating'];
    $new_comment = $_POST['comment'];

    // Update review details in the database
    $sql_update = "UPDATE reviews SET Rating = ?, Comment = ? WHERE ReviewID = ?";
    $stmt_update = $conn->prepare($sql_update);

    if ($stmt_update) {
        $stmt_update->bind_param("isi", $new_rating, $new_comment, $review_id);
        if ($stmt_update->execute()) {
            $feedback_message = "<p class='text-green-500'>Review updated successfully!</p>";
        } else {
            $feedback_message = "<p class='text-red-500'>Error updating review: " . htmlspecialchars($stmt_update->error) . "</p>";
        }
        $stmt_update->close();
    } else {
        $feedback_message = "Error preparing update statement: " . htmlspecialchars($conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Review</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">
    <?php include 'sidebar.php'; ?>

    <div class="container mx-auto px-4 pt-5">
        <h2 class="text-3xl font-semibold text-gray-800 mb-6">Edit Review</h2>

        <!-- Display feedback message -->
        <?php echo $feedback_message; ?>

        <!-- Review Form -->
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $review_id; ?>" class="mb-4">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="rating">Rating</label>
                <input type="number" name="rating" id="rating" value="<?php echo htmlspecialchars($row["Rating"]); ?>" min="1" max="5" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="comment">Comment</label>
                <textarea name="comment" id="comment" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required><?php echo htmlspecialchars($row["Comment"]); ?></textarea>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update Review</button>
                <a href="admin_reviews.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>