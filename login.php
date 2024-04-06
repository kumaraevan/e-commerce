<?php
session_start();
require 'config.php';

$logout_msg = "";
$error_msg = "";

if(isset($_GET['logout']) && $_GET['logout'] == 'success') {
    $logout_msg = "You have been logged out succesfully!";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $conn->real_escape_string($_POST['email']);
        $password = $conn->real_escape_string($_POST['password']);

        $stmt = $conn->prepare("SELECT UserID, Name, Email, Password, Role FROM Users WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['role'] = $user['Role'];
            $_SESSION['loggedin'] = true;
            $_SESSION['name'] = $user['Name'];

            if($user['Role'] == 'seller') {
                header("Location: seller_dashboard.php");
                exit();
            } else {
                header("Location: index.php");
                exit();
            }
        } else {
            $error_msg = "Invalid Email or Password!";
        }

        $stmt->close();
    } else {
        $error_msg = "Please fill in both email and password.";
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="container mx-auto w-full max-w-xs mt-20">
    <h2 class="text-center text-2xl font-extrabold text-gray-900">Login</h2>
    <?php if (!empty($logout_msg)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <?php echo htmlspecialchars($logout_msg); ?>
        </div>
    <?php endif; ?>

    <?php if ($error_msg != ""): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <?php echo htmlspecialchars($error_msg); ?>
        </div>
    <?php endif; ?>
    <form action="login.php" method="post" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <div class="mb-4">
            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="email" name="email" placeholder="Email" required>
        </div>
        <div class="mb-6">
            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" type="password" name="password" placeholder="Password" required>
        </div>
        <div class="flex items-center justify-between">
            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit" name="login">Login</button>
        </div>
    </form>

    <p class="text-center text-gray-500 text-xs">
        No Account? <a href="register.php" class="text-blue-500 hover:text-blue-800">Register Now!</a>
    </p>
</div>
</body>
</html>