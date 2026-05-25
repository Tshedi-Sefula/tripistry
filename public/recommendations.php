<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();

if (!isTraveller()) {
    die("Access denied.");
}

$stmt = $pdo->prepare("
    SELECT
        packageID,
        title,
        description,
        basePrice,
        durationDays,
        status
    FROM TravelPackage
    WHERE status = 'active'
    ORDER BY basePrice ASC
    LIMIT 6
");
$stmt->execute();
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recommended Packages — Tripistry</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include "../includes/navbar.php"; ?>

<div class="wrapper">
    <div class="page-content">
        <h1 class="page-title">Recommended Packages</h1>
        <p class="page-subtitle">PICKED FOR YOU BASED ON AFFORDABILITY AND AVAILABILITY</p>

        <?php if (count($packages) > 0): ?>
            <div class="packages-grid">
                <?php foreach ($packages as $package): ?>
                    <div class="package-card">
                        <h2><?php echo htmlspecialchars($package["title"]); ?></h2>
                        <p><?php echo htmlspecialchars($package["description"]); ?></p>
                        <div class="price-tag">R<?php echo number_format($package["basePrice"], 2); ?></div>
                        <div class="package-meta">
                            <span class="meta-badge">⏱ <?php echo htmlspecialchars($package["durationDays"]); ?> days</span>
                        </div>
                        <a class="btn" href="package_details.php?id=<?php echo htmlspecialchars($package["packageID"]); ?>">
                            View Details
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="glass-card" style="text-align:center; padding:3rem;">
                <p style="color:var(--text-dim); font-size:16px;">No recommendations available.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>