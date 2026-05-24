<?php
// navbar.php — included by every page
// auth.php must be required BEFORE this file
$userRole  = $_SESSION["role"]  ?? "";
$userEmail = $_SESSION["email"] ?? "Guest";
// Determine current page for active link
$currentPage = basename($_SERVER["PHP_SELF"]);
?>

<video autoplay muted loop playsinline class="bg-video">
    <source src="../img/StevenUniverseBarn.mp4" type="video/mp4">
</video>
<div class="bg-overlay"></div>

<nav class="navbar">
    <a href="/public/index.php">
        <img src="/img/log.svg" alt="Tripistry" class="logo">
    </a>

    <ul>
        <?php if ($userRole === "traveller"): ?>
            <li><a href="traveller_dashboard.php" <?php if ($currentPage === 'traveller_dashboard.php') echo 'class="active"'; ?>>Dashboard</a></li>
            <li><a href="packages.php" <?php if ($currentPage === 'packages.php') echo 'class="active"'; ?>>Packages</a></li>
            <li><a href="my_bookings.php" <?php if ($currentPage === 'my_bookings.php') echo 'class="active"'; ?>>My Bookings</a></li>
        <?php elseif ($userRole === "agency"): ?>
            <li><a href="agency_dashboard.php" <?php if ($currentPage === 'agency_dashboard.php') echo 'class="active"'; ?>>Dashboard</a></li>
            <li><a href="agency_packages.php" <?php if ($currentPage === 'agency_packages.php') echo 'class="active"'; ?>>My Packages</a></li>
            <li><a href="create_package.php" <?php if ($currentPage === 'create_package.php') echo 'class="active"'; ?>>+ Create</a></li>
        <?php else: ?>
            <li><a href="login.php" <?php if ($currentPage === 'login.php') echo 'class="active"'; ?>>Login</a></li>
            <li><a href="register.php" <?php if ($currentPage === 'register.php') echo 'class="active"'; ?>>Register</a></li>
        <?php endif; ?>

        <?php if ($userRole !== ""): ?>
            <li><a href="logout.php">Logout</a></li>
        <?php endif; ?>
    </ul>
</nav>
