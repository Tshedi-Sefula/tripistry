<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();

$userID = $_SESSION["userID"] ?? $_SESSION["user_id"] ?? null;

$stmt = $pdo->prepare("
    SELECT p.*
    FROM TravelPackage p
    WHERE p.status = 'active'
    ORDER BY p.basePrice ASC
    LIMIT 5
");
$stmt->execute();

$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recommended Packages</title>
</head>
<body>

<?php include "../includes/navbar.php"; ?>

<h1>Recommended Packages</h1>

<p>
    These packages are recommended based on affordability and availability.
</p>

<?php foreach ($packages as $package): ?>
    <div style="border:1px solid #ccc; padding:15px; margin:15px;">
        <h2><?php echo htmlspecialchars($package["title"] ?? $package["name"] ?? "Package"); ?></h2>

        <p>
            <?php echo htmlspecialchars($package["description"] ?? "No description available."); ?>
        </p>

        <p>
            <strong>Price:</strong>
            R<?php echo htmlspecialchars($package["basePrice"] ?? $package["price"] ?? "N/A"); ?>
        </p>

        <a href="package_details.php?id=<?php echo $package["packageID"]; ?>">
            View Details
        </a>
    </div>
<?php endforeach; ?>

</body>
</html>