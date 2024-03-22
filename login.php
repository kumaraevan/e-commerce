<?php

$host = "localhost";
$dbUsername = "ghiegz";
$dbPassword = "kUm@ra06";
$dbname = "your_database_name";

$conn = new mysqli($host, $dbUsername, $dbPassword, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);
    
    $result = $conn->query("SELECT * FROM ecommerce_users WHERE username='$username'");
    
    if($result->num_rows == 1){
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            echo "Logged in successfully";
        } else {
            echo "Invalid password";
        }
    } else {
        echo "Username does not exist";
    }
    $conn->close();
}
?>
