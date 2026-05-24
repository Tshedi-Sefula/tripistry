<?php
require_once "../includes/auth.php";

requireLogin();

if (!isTraveller()) {
    die("Access denied.");
}
?>
<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Tripistry</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<?php include "../includes/navbar.php"; ?>

<div class="wrapper">
    <div class="page-content">
        <h1 class="page-title">Welcome Back</h1>
        <p class="page-subtitle">YOUR TRIPISTRY TRAVELLER DASHBOARD</p>

        <div class="dashboard-grid">

            <div class="dashboard-card">
                <span class="card-icon">🌍</span>
                <h2>Browse Packages</h2>
                <p>Explore available travel packages from agencies around the world.</p>
                <a class="btn" href="packages.php">View Packages</a>
            </div>

            <div class="dashboard-card">
                <span class="card-icon">🎫</span>
                <h2>My Bookings</h2>
                <p>View your current bookings and payment status at a glance.</p>
                <a class="btn" href="my_bookings.php">View Bookings</a>
            </div>

        </div>
    </div>
</div>

</body>
</html>
