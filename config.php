<?php
// Database Config
$host = 'localhost';
$username = 'root';
$password = '';
$db_name = 'ecommerce_db';

$conn = new mysqli($host, $username, $password, $db_name);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}
?>