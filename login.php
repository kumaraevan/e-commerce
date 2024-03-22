<?php

$host = "localhost";
$dbUsername = "ghiegz";
$dbPassword = "kUm@ra06";
$dbname = "ecommerce";

$conn = new mysqli($host, $dbUsername, $dbPassword, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);
    
    $result = $conn->query("SELECT * FROM users WHERE username='$username'");
    
    if($result->num_rows == 1){
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            header('Location: home.html');
            exit();
        } else {
            echo "Invalid Password";
        }
    } else {
        echo "Invalid Username";
    }
    $conn->close();
}
?>