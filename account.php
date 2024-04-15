<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

$name = $email = $phone = "";
$address = "";
$has_address = false;
$user_id = $_SESSION["user_id"];
$orders = [];

if (isset($_SESSION["user_id"])) {
    $account_sql = "SELECT Name, Email, Phone, Address FROM users WHERE UserID = ?";

    if ($stmt = mysqli_prepare($conn, $account_sql)) {
        mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $name, $email, $phone, $address);
                mysqli_stmt_fetch($stmt);
                $has_address = !empty($address);
            }
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        mysqli_stmt_close($stmt);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["address"])) {
    $new_address = trim($_POST["address"]);
    $update_sql = "UPDATE users SET Address = ? WHERE UserID = ?";

    if ($stmt = mysqli_prepare($conn, $update_sql)) {
        mysqli_stmt_bind_param($stmt, "si", $new_address, $_SESSION["user_id"]);

        if (mysqli_stmt_execute($stmt)) {
            $address = $new_address; // Update the address variable
            $has_address = true; // Set has_address to true
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
</head>

<body class="bg-gray-100">
    <?php include 'navbar.php'; ?>
    <div class="container mx-auto mt-8">
        <h2 class="text-xl font-bold mb-4">Account Settings</h2>
        <div class="bg-white p-6 rounded-lg shadow">
            <p class="mb-2"><strong>Account Details</strong></p>
            <p class="mb-2"><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
            <p class="mb-2"><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
            <p class="mb-4"><strong>Phone:</strong> <?php echo htmlspecialchars($phone); ?></p>
            <div>
                <p class="mb-4"><strong>Address:</strong>
                    <?php echo $has_address ? htmlspecialchars($address) : "You have not set an address."; ?></p>
                <?php if (!$has_address): ?>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="mb-4">
                            <label for="address" class="block text-sm font-medium text-gray-700">Address:</label>
                            <input type="text" id="address" name="address"
                                class="mt-1 p-2 w-full border border-gray-400 rounded-md shadow-sm bg-gray-50" required>
                        </div>
                        <input type="submit" value="Update Address"
                            class="cursor-pointer bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    </form>
                <?php endif; ?>
            </div>
            <a href="reset_password.php" class="text-blue-500 hover:underline">Reset Your Password</a><br><br>
            <a href="logout.php" class="text-blue-500 hover:underline">Logout</a>
        </div>
    </div>
</body>

</html>