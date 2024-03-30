<?php
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $role = 'seller';

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO Users (Name, Email, Phone, Password, Role, RegistrationDate) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssss", $name, $email, $phone, $hashed_password, $role);

    if ($stmt->execute()) {
        header("Location: registration_success.php");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Seller Registration</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h2 { text-align: center;}
        .container { width: 300px; margin: auto; padding-top: 50px; }
        form { display: flex; flex-direction: column; }
        input[type="text"], input[type="email"], input[type="password"] { margin-bottom: 10px; }
        button { cursor: pointer; }
    </style>
</head>
<body>
<div class="container">
    <h2 aa>Seller Registration</h2>
    <form action="register_seller.php" method="post">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="phone" placeholder="Phone Number" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="register">Register</button>
    </form>
</div>
</body>
</html>