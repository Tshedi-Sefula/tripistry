<?php
// navbar.php — included by every page
// auth.php must be required BEFORE this file
$userRole  = $_SESSION["role"]  ?? "";
$userEmail = $_SESSION["email"] ?? "Guest";
?>

<video autoplay muted loop playsinline class="bg-video">
    <source src="../public/img/AdventureTime.mp4" type="video/mp4">
</video>
<div class="bg-overlay"></div>

<nav class="navbar">
    <a href="index.php">
        <img src="/img/logo2.png" alt="Tripistry" class="logo">
    </a>

    <ul>
        <?php if ($userRole === "traveller"): ?>
            <li><a href="traveller_dashboard.php">Dashboard</a></li>
            <li><a href="packages.php">Packages</a></li>
            <li><a href="my_bookings.php">My Bookings</a></li>
        <?php elseif ($userRole === "agency"): ?>
            <li><a href="agency_dashboard.php">Dashboard</a></li>
            <li><a href="agency_packages.php">My Packages</a></li>
            <li><a href="create_package.php">+ Create</a></li>
        <?php else: ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
        <?php endif; ?>

        <?php if ($userRole !== ""): ?>
            <li><a href="logout.php">Logout</a></li>
        <?php endif; ?>
    </ul>
</nav>
