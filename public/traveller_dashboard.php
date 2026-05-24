<?php
require_once "../includes/auth.php";

requireLogin();

if (!isTraveller()) {
    die("Access denied.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Traveller Dashboard - Tripistry</title>
    <link rel="stylesheet" href="/css/style.css">
    
</head>

<body>

<body>

<?php include "../includes/navbar.php"; ?>

<div class="container">

    <h2>Welcome to Tripistry</h2>

    <div class="card-container">

        <div class="card">
            <h2>Browse Packages</h2>

            <p>
                Explore available travel packages from different agencies.
            </p>

            <a class="btn" href="packages.php">
                View Packages
            </a>
        </div>

        <div class="card">
            <h2>My Bookings</h2>

            <p>
                View your bookings and payment status.
            </p>

            <a class="btn" href="my_bookings.php">
                View Bookings
            </a>
        </div>

    </div>

</div>

</body>
</html>