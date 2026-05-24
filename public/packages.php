<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();

$sort = $_GET["sort"] ?? "price_asc";
$type = $_GET["type"] ?? "";

$orderBy = "totalPrice ASC";
if ($sort === "price_desc")     $orderBy = "totalPrice DESC";
elseif ($sort === "duration_asc")  $orderBy = "durationDays ASC";
elseif ($sort === "duration_desc") $orderBy = "durationDays DESC";

$sql = "
    SELECT packageID, title, description, totalPrice, durationDays, packageType, status
    FROM travelPackage
    WHERE status = 'active'
";

$params = [];
if ($type !== "") {
    $sql .= " AND packageType = ?";
    $params[] = $type;
}
$sql .= " ORDER BY $orderBy";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Packages — Tripistry</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<?php include "../includes/navbar.php"; ?>

<div class="wrapper">
    <div class="page-content">
        <h1 class="page-title">Available Packages</h1>
        <p class="page-subtitle">EXPLORE ADVENTURES FROM OUR PARTNER AGENCIES</p>

        <!-- Toolbar / Filters -->
        <div class="packages-toolbar">
            <form method="GET" style="display:flex; gap:1rem; align-items:center; flex-wrap:wrap; width:100%;">
                <label>Sort by</label>
                <select name="sort">
                    <option value="price_asc"      <?php if ($sort === "price_asc")      echo "selected"; ?>>Price: Low → High</option>
                    <option value="price_desc"     <?php if ($sort === "price_desc")     echo "selected"; ?>>Price: High → Low</option>
                    <option value="duration_asc"   <?php if ($sort === "duration_asc")   echo "selected"; ?>>Duration: Short → Long</option>
                    <option value="duration_desc"  <?php if ($sort === "duration_desc")  echo "selected"; ?>>Duration: Long → Short</option>
                </select>

                <label>Type</label>
                <select name="type">
                    <option value="">All Types</option>
                    <option value="regular" <?php if ($type === "regular") echo "selected"; ?>>Regular</option>
                    <option value="group"   <?php if ($type === "group")   echo "selected"; ?>>Group</option>
                </select>

                <button type="submit" class="btn">Apply</button>
            </form>
        </div>

        <?php if (count($packages) > 0): ?>
            <div class="packages-grid">
                <?php foreach ($packages as $pkg): ?>
                    <div class="package-card">
                        <h2><?php echo htmlspecialchars($pkg["title"]); ?></h2>
                        <p><?php echo htmlspecialchars($pkg["description"]); ?></p>
                        <div class="price-tag">R<?php echo number_format($pkg["totalPrice"], 2); ?></div>
                        <div class="package-meta">
                            <span class="meta-badge">⏱ <?php echo $pkg["durationDays"]; ?> days</span>
                            <span class="meta-badge"><?php echo ucfirst($pkg["packageType"]); ?></span>
                        </div>
                        <a class="btn" href="package_details.php?id=<?php echo $pkg["packageID"]; ?>">View Details</a>
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
