<?php

$host = 'localhost';
$dbUsername = 'ghiegz';
$dbPassword = 'kUm@ra06';
$dbName = 'ecommerce';

$conn = new mysqli($host, $dbUsername, $dbPassword, $dbName);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);
    
    $checkUser = $conn->query("SELECT * FROM users WHERE username='$username' OR email='$email'");
    
    if($checkUser->num_rows > 0){
        echo "Username or Email already taken";
    } else {
        // User doesn't exist, hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, email, password, role, created_at) VALUES ('$username', '$email', '$hashedPassword', 'customer', NOW())";
        
        if ($conn->query($sql) === TRUE) {
            echo "Thank You For Registering"; 
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error; 
        }
    }
    $conn->close();
}
?>
