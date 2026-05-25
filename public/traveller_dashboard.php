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
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include "../includes/navbar.php"; ?>

<div class="wrapper">
    <div class="page-content">
        <h1 class="page-title">Welcome to Tripistry</h1>
        <p class="page-subtitle">YOUR TRAVELLER DASHBOARD</p>

        <div class="dashboard-grid">

            <div class="dashboard-card">
                <span class="card-icon">🌍</span>
                <h2>Browse Packages</h2>
                <p>Explore available travel packages from different agencies.</p>
                <a class="btn" href="packages.php">View Packages</a>
            </div>

            <div class="dashboard-card">
                <span class="card-icon">🎫</span>
                <h2>My Bookings</h2>
                <p>View your bookings and payment status.</p>
                <a class="btn" href="my_bookings.php">View Bookings</a>
            </div>

            <div class="dashboard-card">
                <span class="card-icon">✨</span>
                <h2>Recommended</h2>
                <p>Packages picked for you based on affordability and availability.</p>
                <a class="btn" href="recommendations.php">View Picks</a>
            </div>

            <div class="dashboard-card">
                <span class="card-icon">👥</span>
                <h2>Find a Group</h2>
                <p>Discover group trips that match your travel preferences.</p>
                <a class="btn" href="group_matches.php">Find Groups</a>
            </div>

        </div>
    </div>
</div>

</body>
</html>