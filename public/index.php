<?php
require_once "../includes/db.php";

$stmt = $pdo->query("SELECT COUNT(*) AS total FROM travelPackage");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tripistry</title>
    <link rel="stylesheet" href="../css/style.css">  " might remove as file didn't have a style section"
</head>
<body>
    <h1>Tripistry is running</h1>
    <p>Database connected successfully.</p>
    <p>Total travel packages: <?php echo $result["total"]; ?></p>
</body>
</html>