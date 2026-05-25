<?php
require_once "../includes/db.php";

$stmt = $pdo->query("SELECT COUNT(*) AS total FROM TravelPackage");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tripistry</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 80px 20px;
            background: #f8f9fa;
        }
        h1 {
            color: #2c3e50;
            font-size: 3rem;
        }
        p {
            font-size: 1.3rem;
            color: #555;
        }
        .btn {
            display: inline-block;
            padding: 15px 35px;
            margin: 20px;
            font-size: 1.3rem;
            text-decoration: none;
            border-radius: 50px;
            color: white;
            background-color: #3498db;
            transition: 0.3s;
        }
        .btn:hover {
            background-color: #2980b9;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <h1>Tripistry is running</h1>
    <p>Database connected successfully.</p>
    <p><strong>Total travel packages:</strong> <?php echo $result["total"]; ?></p>
    
    <br><br>
    <a href="login.php" class="btn">Go to Login Page</a>
</body>
</html>