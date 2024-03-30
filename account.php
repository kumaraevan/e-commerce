<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

$name = $email = $phone = "";
$user_id = $_SESSION["user_id"];
$orders = [];

if (isset($_SESSION["user_id"])){
    $account_sql = "SELECT Name, Email, Phone FROM users WHERE userID = ?";
    
    if ($stmt = mysqli_prepare($conn, $account_sql)){
        mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
        
        if (mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            
            if (mysqli_stmt_num_rows($stmt) == 1){                    
                // Bind result variables
                mysqli_stmt_bind_result($stmt, $name, $email, $phone);
                mysqli_stmt_fetch($stmt);
            }
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Account Settings</title>
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
        </style>
    </head>
    <body>
        <div class="navbar">
            <a href="index.php">Home</a>
            <a href="#products">Products</a>
            <a href="#search">Search</a>
            <a href="#about">About</a>

        <div class="navbar-right">
            <a href="register_seller.php">Open Shop!</a>
            <a href="account.php">My Account</a>
            <a href="cart.php">Cart (0)</a> <!-- Update '0' with dynamic cart count -->
        </div>
        </div>
        
        <div>
            <h2>Account Settings</h2>
            <p>Account Details</p>
            <p><b>Name:</b> <?php echo htmlspecialchars($name); ?></p>
            <p><b>Email:</b> <?php echo htmlspecialchars($email); ?></p>
            <p><b>Phone:</b> <?php echo htmlspecialchars($phone); ?></p>
            <a href="reset_password.php">Reset Your Password</a><br><br>
            <a href="logout.php">Logout</a>
        </div>
    </body>
</html>