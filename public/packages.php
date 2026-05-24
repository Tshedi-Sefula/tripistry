<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();

$sort = $_GET["sort"] ?? "price_asc";
$type = $_GET["type"] ?? "";
$search = trim($_GET["search"] ?? "");

// Keep your 'p.' aliases for the ORDER BY
$orderBy = "p.basePrice ASC";
if ($sort === "price_desc")    $orderBy = "p.basePrice DESC";
elseif ($sort === "duration_asc")  $orderBy = "p.durationDays ASC";
elseif ($sort === "duration_desc") $orderBy = "p.durationDays DESC";

// Combine your search joins with their status column and our packageType fix
$sql = "
    SELECT 
        p.packageID, 
        p.title, 
        p.description, 
        p.basePrice, 
        p.durationDays, 
        p.status,
        IF(g.packageID IS NOT NULL, 'Group Trip', 'Regular Trip') AS packageType,
        a.name AS agencyName, 
        a.rating AS agencyRating
    FROM TravelPackage p
    JOIN TravelAgency a ON p.agencyUserID = a.userID
    LEFT JOIN GroupTrip g ON p.packageID = g.packageID
    WHERE p.status = 'active'
";

$params = [];

// Safely filter by the new packageType logic without breaking the database!
if ($type !== "") {
    $sql .= " AND IF(g.packageID IS NOT NULL, 'Group Trip', 'Regular Trip') = ?";
    $params[] = $type;
}

if ($search !== "") {
    $sql .= " AND (p.title LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
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

        <form method="GET" class="packages-toolbar" style="margin-bottom:2rem;">
            <input class="search-bar" type="text" name="search"
                   placeholder="Search packages…"
                   value="<?php echo htmlspecialchars($search); ?>">

            <select class="toolbar-select" name="sort">
                <option value="price_asc"     <?php if ($sort==="price_asc")     echo "selected"; ?>>Price ↑</option>
                <option value="price_desc"    <?php if ($sort==="price_desc")    echo "selected"; ?>>Price ↓</option>
                <option value="duration_asc"  <?php if ($sort==="duration_asc")  echo "selected"; ?>>Duration ↑</option>
                <option value="duration_desc" <?php if ($sort==="duration_desc") echo "selected"; ?>>Duration ↓</option>
            </select>

            <select class="toolbar-select" name="type">
                <option value="">All Types</option>
                <option value="Regular Trip" <?php if ($type==="Regular Trip") echo "selected"; ?>>Regular</option>
                <option value="Group Trip"   <?php if ($type==="Group Trip")   echo "selected"; ?>>Group</option>
            </select>

            <button type="submit" class="btn-primary">Filter</button>
        </form>

        <?php if (count($packages) > 0): ?>
            <div class="planes-grid">
                <?php foreach ($packages as $pkg): ?>
                    <a class="plane-card-link" href="package_details.php?id=<?php echo htmlspecialchars($pkg["packageID"]); ?>">
                        <div class="plane-card">
                            <div class="plane-card-img">
                                <span class="plane-emoji">✈️</span>
                            </div>
                            <div class="plane-card-body">
                                <div class="plane-manufacturer"><?php echo htmlspecialchars($pkg["agencyName"]); ?> · ★ <?php echo htmlspecialchars($pkg["agencyRating"]); ?></div>
                                <div class="plane-model"><?php echo htmlspecialchars($pkg["title"]); ?></div>
                                <div class="plane-stats">
                                    <div class="plane-stat"><strong>R<?php echo number_format($pkg["basePrice"], 2); ?></strong>price</div>
                                    <div class="plane-stat"><strong><?php echo htmlspecialchars($pkg["durationDays"]); ?> days</strong>duration</div>
                                    <div class="plane-stat"><strong><?php echo htmlspecialchars($pkg["packageType"]); ?></strong>type</div>
                                    <div class="plane-stat" style="color: var(--gold);"><strong><?php echo htmlspecialchars(ucfirst($pkg["status"])); ?></strong>status</div>
                                </div>
                            </div>
                            <div class="plane-card-footer">
                                <span class="btn-view">View Details →</span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="glass-card" style="text-align:center; padding:3rem;">
                <p style="color:var(--text-dim); font-size:16px;">No packages found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
