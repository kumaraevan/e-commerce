<?php
session_start();
require_once 'config.php';

// Redirect non-logged in users or non-admins
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Define roles array
$roles = ['buyer', 'seller', 'admin'];

// Query to retrieve users based on their roles
$sql = "SELECT * FROM users WHERE Role IN ('buyer', 'seller', 'admin') ORDER BY Role, UserID";
$result = $conn->query($sql);

// Fetch all users
$allUsers = [];
while ($row = $result->fetch_assoc()) {
    $allUsers[] = $row;
}

// Create tables for each role
foreach ($roles as $role) {
    echo "<h2>$role Users</h2>";
    echo "<table border='1'>
            <tr>
                <th>User ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Registration Date</th>
            </tr>";

    // Display users of current role
    foreach ($allUsers as $user) {
        if ($user['Role'] == $role) {
            echo "<tr>
                    <td>" . $user['UserID'] . "</td>
                    <td>" . $user['Name'] . "</td>
                    <td>" . $user['Email'] . "</td>
                    <td>" . $user['Phone'] . "</td>
                    <td>" . $user['Address'] . "</td>
                    <td>" . $user['RegistrationDate'] . "</td>
                </tr>";
        }
    }

    echo "</table>";
}

$conn->close();
?>


<html>
<head>
</head>
<body>
<a href="register.php">Register Buyer</a>
<a href="register_seller.php">Register Seller</a>
<a href="register_admin.php">Register Admin</a>
</body>
</html>