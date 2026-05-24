<?php
require_once "../includes/db.php";

$stmt = $pdo->query("SELECT COUNT(*) AS total FROM TravelPackage");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tripistry</title>
</head>
<body>
    <h1>Tripistry is running</h1>
    <p>Database connected successfully.</p>
    <p>Total travel packages: <?php echo $result["total"]; ?></p>
</body>
</html>