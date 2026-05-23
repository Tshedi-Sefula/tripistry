<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$userRole = $_SESSION["role"] ?? "";
$userEmail = $_SESSION["email"] ?? "Guest";
?>

<video autoplay muted loop playsinline class="video-background">
    <source src="../AdventureTime.mp4" type="video/mp4">
</video>

<a href="index.php">
    <img src="../img/logo.2.png" alt="Tripistry Logo" class="logo">
</a>

<div class="navbar">
    <div class="nav-left">
        <ul>
            <?php if ($userRole === "traveller"): ?>
                <li><a href="traveller_dashboard.php">Dashboard</a></li>
                <li><a href="packages.php">Packages</a></li>
                <li><a href="my_bookings.php">My Bookings</a></li>
            <?php elseif ($userRole === "agency"): ?>
                <li><a href="agency_dashboard.php">Dashboard</a></li>
                <li><a href="agency_packages.php">My Packages</a></li>
                <li><a href="create_package.php">Create</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="nav-right">
        <ul>
            <li>
                <span class="user-info" style="color: white; font-family: 'Blonde'; letter-spacing: 2px; margin-right: 15px; text-transform: uppercase; font-size: 12px;">
                    Logged in: <?php echo htmlspecialchars($userEmail); ?>
                </span>
            </li>
            <li><a href="logout.php" class="logout-btn">Logout</a></li>
        </ul>
    </div>
</div>