<?php
session_start();
require 'config.php';

$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $conn->real_escape_string($_POST['email']);
        $password = $conn->real_escape_string($_POST['password']);

        $stmt = $conn->prepare("SELECT UserID, Email, Password, Role FROM Users WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['role'] = $user['Role'];
            $_SESSION['loggedin'] = true;

            header("Location: index.php");
            exit();
        } else {
            $error_msg = "Invalid Email or Password!";
        }

        $stmt->close();
        $conn->close();
    } else {
        $error_msg = "Please fill in both email and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { width: 300px; margin: auto; padding-top: 50px; }
        form { display: flex; flex-direction: column; }
        input[type="email"], input[type="password"] { margin-bottom: 10px; }
        button { cursor: pointer; }
    </style>
</head>
<body>
<div class="container">
    <h2>Login</h2>
    <?php if ($error_msg != ""): ?>
        <p><?php echo htmlspecialchars($error_msg); ?></p>
    <?php endif; ?>
    <form action="login.php" method="post">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
    </form>
</div>
</body>
</html>