<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();

$sort = $_GET["sort"] ?? "price_asc";

$orderBy = "basePrice ASC";

if ($sort === "price_desc") {
    $orderBy = "basePrice DESC";
} elseif ($sort === "duration_asc") {
    $orderBy = "durationDays ASC";
} elseif ($sort === "duration_desc") {
    $orderBy = "durationDays DESC";
}

$sql = "
    SELECT
        packageID,
        title,
        description,
        basePrice,
        durationDays,
        status
    FROM TravelPackage
    WHERE status = 'active'
    ORDER BY $orderBy
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Packages — Tripistry</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include "../includes/navbar.php"; ?>

<div class="wrapper">
    <div class="page-content">
        <h1 class="page-title">Available Packages</h1>
        <p class="page-subtitle">EXPLORE ADVENTURES FROM OUR PARTNER AGENCIES</p>

        <div class="packages-toolbar">
            <form method="GET" style="display:flex; gap:1rem; align-items:center; flex-wrap:wrap; width:100%;">
                <label>Sort by</label>
                <select name="sort">
                    <option value="price_asc"     <?php if ($sort==="price_asc")     echo "selected"; ?>>Price: Low → High</option>
                    <option value="price_desc"    <?php if ($sort==="price_desc")    echo "selected"; ?>>Price: High → Low</option>
                    <option value="duration_asc"  <?php if ($sort==="duration_asc")  echo "selected"; ?>>Duration: Short → Long</option>
                    <option value="duration_desc" <?php if ($sort==="duration_desc") echo "selected"; ?>>Duration: Long → Short</option>
                </select>
                <button type="submit" class="btn">Apply</button>
            </form>
        </div>

        <?php if (count($packages) > 0): ?>
            <div class="packages-grid">
                <?php foreach ($packages as $package): ?>
                    <div class="package-card">
                        <h2><?php echo htmlspecialchars($package["title"]); ?></h2>
                        <p><?php echo htmlspecialchars($package["description"]); ?></p>
                        <div class="price-tag">R<?php echo number_format($package["basePrice"], 2); ?></div>
                        <div class="package-meta">
                            <span class="meta-badge">⏱ <?php echo htmlspecialchars($package["durationDays"]); ?> days</span>
                            <span class="badge badge-<?php echo $package['status']; ?>"><?php echo ucfirst($package["status"]); ?></span>
                        </div>
                        <a class="btn" href="package_details.php?id=<?php echo htmlspecialchars($package["packageID"]); ?>">View Details</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="glass-card" style="text-align:center; padding:3rem;">
                <p style="color:var(--text-dim); font-size:16px;">No travel packages found.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>