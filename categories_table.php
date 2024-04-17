<?php
session_start();
require_once 'config.php';

// Redirect non-logged in users or non-admins
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
  header("Location: login.php");
  exit;
}

$categoryNameError = ""; // Initialize error variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Validate and add new category
  $categoryName = trim($_POST["categoryName"]);

  // Basic validation for category name (can be enhanced)
  if (empty($categoryName)) {
    $categoryNameError = "Category name is required";
  } else {
    $sql = "INSERT INTO categories (CategoryName) VALUES (?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $categoryName);

    if (mysqli_stmt_execute($stmt)) {
      echo "<p style='color:green;'>Category added successfully!</p>";
    } else {
      echo "<p style='color:red;'>Error adding category: " . mysqli_error($conn) . "</p>";
    }

    mysqli_stmt_close($stmt);
  }
}

// Query to retrieve existing categories
$sql = "SELECT * FROM categories";
$result = $conn->query($sql);

// Check for query errors
if (!$result) {
  die("Error retrieving categories: " . mysqli_error($conn));
}

?>

<html>
<head>
  <title>Categories Table</title>
</head>
<body>
  <h2>Categories</h2>

  <form method="post" action="<?php echo htmlspecialchars($_SERVER["SCRIPT_NAME"]); ?>">
    <label for="categoryName">Category Name:</label>
    <input type="text" name="categoryName" id="categoryName" required>
    <br>
    <span class="error"><?php echo $categoryNameError; ?></span>
    <br>
    <button type="submit">Add Category</button>
  </form>

  <table border="1">
    <tr>
      <th>Category ID</th>
      <th>Category Name</th>
      <th>Edit</th>
      <th>Delete</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
      <tr>
        <td><?php echo $row["CategoryID"]; ?></td>
        <td><?php echo $row["CategoryName"]; ?></td>
        <td><a href="edit_category.php?id=<?php echo $row["CategoryID"]; ?>">Edit</a></td>
        <td><a href="delete_category.php?id=<?php echo $row["CategoryID"]; ?>" onclick="return confirm('Are you sure you want to delete this category?')">Delete</a></td>
      </tr>
    <?php } ?>
  </table>

  <?php
  // Display success message below the table
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Only show the message if the form was submitted
    echo "<p style='color:green;'>Category added successfully!</p>";
  }
  ?>

  <?php
  $conn->close();
  ?>
</body>
</html>