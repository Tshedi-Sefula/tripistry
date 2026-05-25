<?php
require_once "../includes/db.php";

$stmt = $pdo->query("SELECT COUNT(*) AS total FROM TravelPackage");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tripistry</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<video autoplay muted loop playsinline class="bg-video">
    <source src="../img/StevenUniverseBarn.mp4" type="video/mp4">
</video>
<div class="bg-overlay"></div>

<div class="wrapper">
    <div class="hero">
        <h1>Tripistry</h1>
        <h2><?php echo $result["total"]; ?> adventures waiting for you</h2>
        <div class="pa-buttons">
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </div>
    </div>
</div>

</body>
</html>