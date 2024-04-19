<?php
require_once 'config.php';

$logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'];
$page = basename($_SERVER['PHP_SELF']);

?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<style>
    .nav-logo {
        display: flex;
        align-items: center;
        height: 42px; /* Set height to align with icons */
        font-size: 20px; /* Adjust font size as needed */
        padding: 0 12px; /* Padding to align text vertically and horizontally */
    }
    .nav-link {
        display: flex;
        align-items: center;
        padding: 8px 12px; /* Adjust padding to visually match the logo */
        border-radius: 4px;
    }
    .nav-link i {
        font-size: 20px; /* Ensure icons are the right size */
    }
    .nav-link:hover {
        background-color: #575757; /* Slightly lighter than #707070 for hover effect */
    }
    .nav-search-input {
        background-color: #1f2937; /* bg-gray-800 for search input */
        border: none;
        color: white;
        padding: 8px 12px;
    }
    .nav-search-button {
        background-color: #1a73e8; /* Blue background for button */
        color: white;
        border: none;
        padding: 8px 16px;
    }
</style>
<nav class="bg-gray-900 text-white p-4">
    <div class="container mx-auto flex justify-between items-center">
        <div class="flex space-x-4">
            <!-- Logo or Home Link -->
            <a href="index.php" class="nav-logo hover:text-gray-300">Sampoerna Connect</a>

            <!-- Left Side - Navigational Links -->
            <a href="index.php" class="<?= ($page == 'index.php') ? 'text-gray-300' : 'hover:bg-gray-700'; ?> nav-link">
                <i class="fas fa-home"></i>
            </a>
            <a href="category.php" class="hover:bg-gray-700 nav-link">
                <i class="fas fa-th"></i>
            </a>
            <div class="relative flex">
                <form action="search.php" method="get" class="flex">
                    <input type="text" name="query" class="nav-search-input rounded-l" placeholder="Search">
                    <button type="submit" class="nav-search-button rounded-r">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
        <div class="flex space-x-4">
            <?php if ($logged_in): ?>
                <a href="register_seller.php" class="hover:bg-gray-700 nav-link">
                    <i class="fas fa-store"></i>
                </a>
                <a href="cart.php" class="hover:bg-gray-700 nav-link">
                    <i class="fas fa-shopping-cart"></i>
                </a>
                <a href="notifications.php" class="hover:bg-gray-700 nav-link">
                    <i class="fas fa-bell"></i>
                </a>
                <a href="account.php" class="hover:bg-gray-700 nav-link">
                    <i class="fas fa-user-circle"></i>
                </a>
            <?php else: ?>
                <a href="login.php" class="hover:bg-gray-700 nav-link">
                    <i class="fas fa-sign-in-alt"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>