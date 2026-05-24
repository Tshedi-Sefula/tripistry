<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tripistry</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<video autoplay muted loop playsinline class="bg-video">
    <source src="/img/AdventureTime.mp4" type="video/mp4">
</video>
<div class="bg-overlay"></div>

<div class="wrapper">
    <div class="hero">
        <h1>Tripistry</h1>
        <h2>Your Adventure Awaits — Come on, Grab Your Friends</h2>
        <div class="pa-buttons">
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
            <?php if (isLoggedIn()): ?>
                <?php if (isTraveller()): ?>
                    <a href="traveller_dashboard.php" class="current">My Dashboard</a>
                <?php else: ?>
                    <a href="agency_dashboard.php" class="current">My Dashboard</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
