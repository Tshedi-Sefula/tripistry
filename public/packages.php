<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();

$sort = $_GET["sort"] ?? "price_asc";
$type = $_GET["type"] ?? "";

$orderBy = "totalPrice ASC";

if ($sort === "price_desc") {
    $orderBy = "totalPrice DESC";
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
        totalPrice,
        durationDays,
        packageType,
        status
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
<html>
<head>
    <title>Travel Packages - Tripistry</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }

        .filters {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .package-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .price {
            font-size: 20px;
            font-weight: bold;
            color: green;
        }

        .view-btn {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        select, button {
            padding: 8px;
            margin-right: 10px;
        }
    </style>
</head>

<body>

<?php include "../includes/navbar.php"; ?>
<h1>Available Travel Packages</h1>

<div class="filters">
    <form method="GET">
        <label>Sort by:</label>
        <select name="sort">
            <option value="price_asc" <?php if ($sort === "price_asc") echo "selected"; ?>>
                Price: Low to High
            </option>
            <option value="price_desc" <?php if ($sort === "price_desc") echo "selected"; ?>>
                Price: High to Low
            </option>
            <option value="duration_asc" <?php if ($sort === "duration_asc") echo "selected"; ?>>
                Duration: Short to Long
            </option>
            <option value="duration_desc" <?php if ($sort === "duration_desc") echo "selected"; ?>>
                Duration: Long to Short
            </option>
        </select>

        <label>Package Type:</label>
        <select name="type">
            <option value="">All</option>
            <option value="regular" <?php if ($type === "regular") echo "selected"; ?>>
                Regular
            </option>
            <option value="group" <?php if ($type === "group") echo "selected"; ?>>
                Group
            </option>
        </select>

        <button type="submit">Apply</button>
    </form>
</div>

<?php if (count($packages) > 0): ?>

    <?php foreach ($packages as $package): ?>

        <div class="package-card">

            <h2><?php echo htmlspecialchars($package["title"]); ?></h2>

            <p><?php echo htmlspecialchars($package["description"]); ?></p>

            <p class="price">
                Price: R<?php echo number_format($package["totalPrice"], 2); ?>
            </p>

            <p>
                Duration: <?php echo $package["durationDays"]; ?> days
            </p>

            <p>
                Package Type: <?php echo ucfirst($package["packageType"]); ?>
            </p>

            <a class="view-btn"
               href="package_details.php?id=<?php echo $package["packageID"]; ?>">
                View Details
            </a>

        </div>

    <?php endforeach; ?>

<?php else: ?>

    <p>No travel packages found.</p>

<?php endif; ?>

</body>
</html>