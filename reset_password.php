<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

$new_password = $confirm_password = "";
$new_password_err = $confirm_password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["new_password"]))) {
        $new_password_err = "Please enter new password";
    } elseif (strlen(trim($_POST["new_password"])) < 6) {
        $new_password_err = "Password must have at least 6 characters!";
    } else {
        $new_password = trim($_POST["new_password"]);
    }

    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm the password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($new_password_err) && ($new_password != $confirm_password)) {
            $confirm_password_err = "Password did not match!";
        }
    }

    if (empty($new_password_err) && empty($confirm_password_err)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET Password = ? WHERE UserID = ?";
    
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "si", $hashed_password, $_SESSION['user_id']);
            
            if (mysqli_stmt_execute($stmt)) {
                session_destroy();
                header("location: login.php");
                exit();
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Reset Password</title>
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
            <a href="account.php">My Account</a>
            <a href="cart.php">Cart (0)</a> <!-- Update '0' with dynamic cart count -->
            <a href="logout.php">Logout</a>
        </div>
        </div>

        <div>
            <p><b>Reset your password below</b></p>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div>
                    <label>New Password</label>
                    <input type="password" name="new_password" value="<?php echo $new_password; ?>">
                    <span><?php echo $new_password_err; ?></span>
                </div>

                <div>
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password">
                    <span><?php echo $confirm_password_err; ?></span>
                </div>
                
                <div>
                    <input type="submit" value="Confrim">
                    <a href="index.php">Cancel</a>
                </div>
            </form>
        </div>
    </body>
</html>