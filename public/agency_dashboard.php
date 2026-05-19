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
            width: 300px;
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