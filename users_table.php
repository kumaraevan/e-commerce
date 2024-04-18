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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Table</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">
    <?php include 'sidebar.php'; ?>

    <div class="container mx-auto px-4 pt-5">
        <h2 class="text-3xl font-semibold text-gray-800 mb-6">Manage Users</h2>
        <?php foreach ($roles as $role): ?>
            <div class="mb-10">
                <h3 class="text-xl font-semibold text-gray-700 mb-2"><?= ucfirst($role) ?> Users</h3>
                <div class="bg-white shadow overflow-hidden rounded-lg">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                                <th class="py-3 px-6 text-left">User ID</th>
                                <th class="py-3 px-6 text-left">Name</th>
                                <th class="py-3 px-6 text-left">Email</th>
                                <th class="py-3 px-6 text-left">Phone</th>
                                <th class="py-3 px-6 text-left">Address</th>
                                <th class="py-3 px-6 text-left">Registration Date</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm font-light">
                            <?php foreach ($allUsers as $user):
                                if ($user['Role'] == $role): ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                                        <td class="py-3 px-6 text-left whitespace-nowrap"><?= htmlspecialchars($user['UserID']) ?></td>
                                        <td class="py-3 px-6 text-left"><?= htmlspecialchars($user['Name']) ?></td>
                                        <td class="py-3 px-6 text-left"><?= htmlspecialchars($user['Email']) ?></td>
                                        <td class="py-3 px-6 text-left"><?= htmlspecialchars($user['Phone']) ?></td>
                                        <td class="py-3 px-6 text-left"><?= htmlspecialchars($user['Address']) ?></td>
                                        <td class="py-3 px-6 text-left"><?= htmlspecialchars($user['RegistrationDate']) ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
        <div class="flex space-x-4 mt-6">
            <a href="register.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <i class="fas fa-user-plus fa-lg mr-2"></i> Register Buyer
            </a>
            <a href="register_seller.php" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <i class="fas fa-user-plus fa-lg mr-2"></i> Register Seller
            </a>
            <a href="register_admin.php" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <i class="fas fa-user-plus fa-lg mr-2"></i> Register Admin
            </a>
        </div>
    </div>

    <?php $conn->close(); ?>
</body>
</html>