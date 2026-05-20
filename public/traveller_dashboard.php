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

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }

       

        .container {
            padding: 30px;
        }

        .card-container {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .card {
            background: white;
            width: 280px;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .card h2 {
            margin-top: 0;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
        }

        .btn:hover {
            background: #0056b3;
        }
    </style>
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