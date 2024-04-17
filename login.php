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
            } elseif ($user['Role'] == 'admin') {
                header("Location: admin.php");
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body class="bg-gray-100">
<div class="container mx-auto w-full max-w-xs mt-20">
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <h2 class="text-2xl font-semibold text-center text-gray-700 mb-6">Login</h2>
        <p class="text-center text-gray-500 text-xs mt-4">
            Please enter your credentials
        </p><br>
        <?php if (!empty($logout_msg)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <?php echo htmlspecialchars($logout_msg); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($error_msg)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>
        <!-- Login form -->
        <form action="login.php" method="post">
            <div class="mb-4">
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="email" name="email" placeholder="Email" required>
            </div>
            <div class="mb-6">
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" type="password" name="password" placeholder="Password" required>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" name="login" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Login
                </button>
            </div>
        </form>
        <p class="text-center text-gray-500 text-xs mt-2">
            No Account? <a href="register.php" class="text-blue-500 hover:text-blue-800">Register Now!</a>
        </p>
    </div>
</div>
</body>
</html>