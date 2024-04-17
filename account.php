<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$name = $email = $phone = $address = "";
$has_address = false;
$address_update = false;
$editing_address = false;

// Check if the edit button was clicked
if (isset($_POST["edit"])) {
    $editing_address = true;
}

// Fetch user data
$account_sql = "SELECT Name, Email, Phone, Address FROM users WHERE UserID = ?";
if ($stmt = mysqli_prepare($conn, $account_sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_bind_result($stmt, $name, $email, $phone, $address);
        if (mysqli_stmt_fetch($stmt)) {
            $has_address = !empty($address);
        }
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }
    mysqli_stmt_close($stmt);
}

// Handle form submission for address update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["address"])) {
    $new_address = trim($_POST["address"]);
    $update_sql = "UPDATE users SET Address = ? WHERE UserID = ?";
    if ($stmt = mysqli_prepare($conn, $update_sql)) {
        mysqli_stmt_bind_param($stmt, "si", $new_address, $user_id);
        if (mysqli_stmt_execute($stmt)) {
            $address = $new_address; // Update the address variable to reflect new changes
            $has_address = true; // Set has_address to true as now we have an address
            $address_update = true; // Set address_update to true to display success message
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Account Settings</title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    </head>
    <body class="bg-gray-100">
    <?php include 'navbar.php'; ?>
        <div class="container mx-auto mt-8">
            <h2 class="text-xl font-bold mb-4">Account Settings</h2>
            <div class="bg-white p-6 rounded-lg shadow">
                <?php if ($address_update): ?>
                    <div class="p-2 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">Address updated successfully.</span>
                    </div>
                <?php endif; ?>
                <p class="mb-2"><strong>Account Details</strong></p>
                <p class="mb-2"><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
                <p class="mb-2"><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                <p class="mb-4"><strong>Phone:</strong> <?php echo htmlspecialchars($phone); ?></p>
                <p class="mb-4">
                    <strong>Address:</strong> <?php echo $has_address ? htmlspecialchars($address) : "You have not set an address."; ?>
                    <?php if ($has_address): ?>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="inline">
                            <button type="submit" name="edit" class="text-sm text-blue-600 hover:text-blue-900">
                                <i class="fas fa-edit"></i>
                            </button>
                        </form>
                    <?php endif; ?>
                </p>
                <?php if ($editing_address || !$has_address): ?>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="mb-4">
                            <label for="address" class="block text-sm font-medium text-gray-700">Address:</label>
                            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($address); ?>" class="mt-1 p-2 w-full border border-gray-400 rounded-md shadow-sm bg-gray-50" required>
                        </div>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update Address</button>
                    </form>
                <?php endif; ?>
                <button onclick="location.href='reset_password.php'" class="mt-4 w-64 inline-block px-6 py-2 text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 rounded-md shadow-sm">
                Reset Your Password
                </button><br><br>
                <button onclick="location.href='logout.php'" class="mt-4 w-64 inline-block px-6 py-2 text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50 rounded-md shadow-sm">
                    Logout
                </button>
            </div>
        </div>
    </body>
</html>