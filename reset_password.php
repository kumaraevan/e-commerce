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
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-gray-900 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="hover:bg-gray-700 px-3 py-2 rounded">Home</a>
            <a href="#products" class="hover:bg-gray-700 px-3 py-2 rounded">Products</a>
            <a href="#search" class="hover:bg-gray-700 px-3 py-2 rounded">Search</a>
            <a href="#about" class="hover:bg-gray-700 px-3 py-2 rounded">About</a>
            <div class="flex">
                <a href="account.php" class="hover:bg-gray-700 px-3 py-2 rounded">My Account</a>
                <a href="cart.php" class="hover:bg-gray-700 px-3 py-2 rounded">Cart (0)</a>
                <a href="logout.php" class="hover:bg-gray-700 px-3 py-2 rounded">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto w-full max-w-xs mt-20">
        <div class="bg-white p-8 rounded-lg shadow-md">
            <p class="text-xl font-semibold mb-4">Reset your password below</p>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="mb-4">
                    <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                    <input type="password" id="new_password" name="new_password" value="<?php echo $new_password; ?>" class="mt-1 p-2 w-full border border-gray-300 rounded-md shadow-sm" required>
                    <span class="text-sm text-red-500"><?php echo $new_password_err; ?></span>
                </div>
                <div class="mb-4">
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="mt-1 p-2 w-full border border-gray-300 rounded-md shadow-sm" required>
                    <span class="text-sm text-red-500"><?php echo $confirm_password_err; ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <input type="submit" value="Confirm" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded cursor-pointer">
                    <a href="index.php" class="text-blue-500 hover:text-blue-800">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>