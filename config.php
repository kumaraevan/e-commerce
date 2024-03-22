<?php
// Database Config
$host = 'localhost';
$username = 'ghiegz';
$password = 'kUm@ra06';
$dbname = 'ecommerce_db';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}
?>
