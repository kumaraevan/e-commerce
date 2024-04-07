<?php
require_once 'config.php';

$logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'];
$page = basename($_SERVER['PHP_SELF']);

?>
<nav class="bg-gray-900 text-white p-4">
    <div class="container mx-auto flex justify-between items-center">
        <div class="flex space-x-4">
            <!-- Logo or Home Link -->
            <a href="index.php" class="text-xl font-bold hover:text-gray-300">eCommerce</a>

            <!-- Left Side - Navigational Links -->
            <a href="index.php" class="<?= ($page == 'index.php') ? 'text-gray-300' : 'hover:bg-gray-700'; ?> px-3 py-2 rounded">Home</a>
            <a href="#products" class="hover:bg-gray-700 px-3 py-2 rounded">Products</a>
            <div class="relative">
                <form action="search.php" method="get">
                    <input type="text" name="query" class="bg-gray-800 text-white px-4 py-2 rounded-l" placeholder="Search...">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded-r">
                        Search
                    </button>
                </form>
            </div>
        </div>
        <div class="flex space-x-4">
            <?php if ($logged_in): ?>
                <a href="register_seller.php" class="hover:bg-gray-700 px-3 py-2 rounded">Open Shop!</a>
                <a href="cart.php" class="hover:bg-gray-700 px-3 py-2 rounded">Cart (0)</a>
                
                <!-- Notification Button -->
                <a href="notifications.php" class="hover:bg-gray-700 px-3 py-2 rounded">Notifications</a>
                
                <a href="account.php" class="hover:bg-gray-700 px-3 py-2 rounded">My Account</a>
            <?php else: ?>
                <a href="login.php" class="hover:bg-gray-700 px-3 py-2 rounded">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>