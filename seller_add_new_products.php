<?php
session_start();
require_once 'config.php';

// Redirect if not logged in or if the user is not a seller
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'seller') {
    header("Location: login.php");
    exit;
}

// Initialize variables
$message = "";
$categoryOptions = "";

// Fetch categories for the dropdown
$sql = "SELECT CategoryID, CategoryName FROM categories";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $categoryOptions .= "<option value='" . $row["CategoryID"] . "'>" . $row["CategoryName"] . "</option>";
    }
} else {
    $message = "No categories found. Please add categories before adding products.";
}

// Process the form when it is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    // Sanitize input
    $name = $conn->real_escape_string(trim($_POST['name']));
    $price = $conn->real_escape_string(trim($_POST['price']));
    $description = $conn->real_escape_string(trim($_POST['description']));
    $stockQuantity = $conn->real_escape_string(trim($_POST['stockQuantity']));
    $categoryID = intval($_POST['category']);

    // Handle file upload
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["productImage"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is an actual image or fake image
    $check = getimagesize($_FILES["productImage"]["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        $message = "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size - for example, limit to 5MB
    if ($_FILES["productImage"]["size"] > 5000000) {
        $message = "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $message = "Sorry, your file was not uploaded.";
    // If everything is ok, try to upload file
    } else {
        if (!move_uploaded_file($_FILES["productImage"]["tmp_name"], $target_file)) {
            $message = "Sorry, there was an error uploading your file.";
        }
    }

    // Insert product into the database if the file was successfully uploaded
    if ($uploadOk == 1) {
        $sql = "INSERT INTO products (SellerID, Name, Price, Description, StockQuantity, CategoryID, ImageURLs) VALUES (?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("isssiss", $_SESSION['user_id'], $name, $price, $description, $stockQuantity, $categoryID, $target_file);
            
            if ($stmt->execute()) {
                $message = "Product added successfully!";
            } else {
                $message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Product</title>
    <style>
            .navbar {
                overflow: hidden;
                background-color: #333;
            }

            .navbar a {
                float: left;
                display: block;
                color: white;
                text-align: center;
                padding: 14px 20px;
                text-decoration: none;
            }

            .navbar-right {
                float: right;
            }

            .navbar::after {
                content: "";
                display: table;
                clear: both;
            }

            .navbar a:hover {
                background-color: #ddd;
                color: black;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            table, th, td {
                border: 1px solid #ddd;
            }

            th, td {
                padding: 8px;
                text-align: left;
            }

            th {
                background-color: #f2f2f2;
            }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="seller_dashboard.php">Dashboard</a>
        <a href="seller_add_new_products.php">Add New Products</a>
        <a href="seller_manage_products.php">Manage Products</a>
        <a href="seller_orders.php">View Orders</a>
        <div class="navbar-right">
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <h2>Add New Product</h2>
    <?php echo $message ? "<p>$message</p>" : ""; ?>
    <form action="seller_add_new_products.php" method="post" enctype="multipart/form-data">
        <label for="name">Product Name:</label>
        <input type="text" name="name" required><br>
        
        <label for="price">Price:</label>
        <input type="text" name="price" required><br>
        
        <label for="description">Description:</label>
        <textarea name="description" required></textarea><br>
        
        <label for="stockQuantity">Stock Quantity:</label>
        <input type="number" name="stockQuantity" required><br>
        
        <label for="category">Category:</label>
        <select name="category" required>
            <?php echo $categoryOptions; ?>
        </select><br>
        
        <label for="productImage">Product Image:</label>
        <input type="file" name="productImage" required><br>
        
        <input type="submit" name="submit" value="Add Product">
    </form>
</body>
</html>