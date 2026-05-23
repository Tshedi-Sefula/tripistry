<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userRole = $_SESSION["role"] ?? "";
$userEmail = $_SESSION["email"] ?? "Guest";
?>

<video autoplay muted loop playsinline class="video-background">
    <source src="../AdventureTime.mp4" type="video/mp4">
    Your browser does not support the video tag.
</video>

<style>

body {
    margin: 0;
}

.navbar {
    background: #007bff;
    padding: 15px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: white;
}

.nav-left a,
.nav-right a {
    color: white;
    text-decoration: none;
    margin-right: 15px;
    font-weight: bold;
}

.nav-left a:hover,
.nav-right a:hover {
    text-decoration: underline;
}

.user-info {
    margin-right: 20px;
    font-size: 14px;
}

.logout-btn {
    background: #dc3545;
    padding: 8px 12px;
    border-radius: 5px;
}

.logout-btn:hover {
    background: #b02a37;
}

</style>

<div class="navbar">

    <div class="nav-left">

        <?php if ($userRole === "traveller"): ?>

            <a href="traveller_dashboard.php">Dashboard</a>
            <a href="packages.php">Packages</a>
            <a href="my_bookings.php">My Bookings</a>

        <?php elseif ($userRole === "agency"): ?>

            <a href="agency_dashboard.php">Dashboard</a>
            <a href="agency_packages.php">My Packages</a>
            <a href="create_package.php">Create Package</a>

        <?php endif; ?>

    </div>

    <div class="nav-right">

        <span class="user-info">
            Logged in as:
            <strong><?php echo htmlspecialchars($userEmail); ?></strong>
            (<?php echo htmlspecialchars($userRole); ?>)
        </span>

        <a class="logout-btn" href="logout.php">
            Logout
        </a>

    </div>

</div>