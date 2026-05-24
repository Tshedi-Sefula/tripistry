<?php
require_once "../includes/auth.php";

requireLogin();

if (!isAgency()) {
    die("Access denied.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Agency Dashboard - Tripistry</title>
    <link rel="stylesheet" href="/css/style.css">
    

</head>

<body>

<body>

<?php include "../includes/navbar.php"; ?>

<div class="container">

    <h2>Welcome Agency</h2>

    <div class="card-container">

        <div class="card">

            <h2>Manage Packages</h2>

            <p>
                View, edit and manage your travel packages.
            </p>

            <a class="btn" href="agency_packages.php">
                View Packages
            </a>

        </div>

        <div class="card">

            <h2>Create Package</h2>

            <p>
                Create a new travel package for travellers.
            </p>

            <a class="btn" href="create_package.php">
                Create Package
            </a>

        </div>

    </div>

</div>

</body>
</html>