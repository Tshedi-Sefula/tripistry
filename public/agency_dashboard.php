<?php
require_once "../includes/auth.php";

requireLogin();

if (!isAgency()) {
    die("Access denied.");
}
?>

<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agency Dashboard — Tripistry</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include "../includes/navbar.php"; ?>

<div class="wrapper">
    <div class="page-content">
        <h1 class="page-title">Agency Dashboard</h1>
        <p class="page-subtitle">MANAGE YOUR TRAVEL PACKAGES</p>

        <div class="dashboard-grid">

            <div class="dashboard-card">
                <span class="card-icon">📦</span>
                <h2>Manage Packages</h2>
                <p>View, edit and manage your travel packages.</p>
                <a class="btn" href="agency_packages.php">View Packages</a>
            </div>

            <div class="dashboard-card">
                <span class="card-icon">✨</span>
                <h2>Create Package</h2>
                <p>Create a new travel package for travellers.</p>
                <a class="btn" href="create_package.php">Create Package</a>
            </div>

        </div>
    </div>
</div>

</body>
</html>
