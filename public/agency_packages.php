<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();

if (!isAgency()) {
    die("Access denied.");
}

$userID = $_SESSION["user_id"];

$stmt = $pdo->prepare("SELECT * FROM TravelAgency WHERE userID = ?");
$stmt->execute([$userID]);
$agency = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$agency) {
    die("Agency profile not found.");
}

$stmt = $pdo->prepare("
    SELECT 
        packageID,
        title,
        description,
        basePrice,
        itinerary,
        durationDays,
        status,
        dateCreated
    FROM TravelPackage 
    WHERE agencyUserID = ? 
    ORDER BY dateCreated DESC
");
$stmt->execute([$userID]);
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Packages — Tripistry</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include "../includes/navbar.php"; ?>

<div class="wrapper">
    <div class="page-content">
        <h1 class="page-title">My Packages</h1>
        <p class="page-subtitle">YOUR PUBLISHED TRAVEL EXPERIENCES</p>

        <div class="btn-row" style="margin-bottom:1.8rem;">
            <a class="btn" href="create_package.php">+ Create New Package</a>
            <a class="btn-secondary" href="agency_dashboard.php">← Dashboard</a>
        </div>

        <?php if (count($packages) > 0): ?>
            <div class="packages-grid">
                <?php foreach ($packages as $package): ?>
                    <div class="package-card">
                        <h2><?php echo htmlspecialchars($package["title"]); ?></h2>
                        <p><?php echo htmlspecialchars($package["description"]); ?></p>
                        <div class="price-tag">R<?php echo number_format($package["basePrice"], 2); ?></div>
                        <div class="package-meta">
                            <span class="meta-badge">⏱ <?php echo $package["durationDays"]; ?> days</span>
                            <span class="badge badge-<?php echo $package['status']; ?>"><?php echo ucfirst($package["status"]); ?></span>
                        </div>
                        <div class="btn-row">
                            <a class="btn-secondary" href="edit_package.php?id=<?php echo $package["packageID"]; ?>">Edit</a>
                            <a class="btn-danger"
                               href="delete_package.php?id=<?php echo $package["packageID"]; ?>"
                               onclick="return confirm('Are you sure you want to delete this package? This action cannot be undone.');">
                                Delete
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="glass-card" style="text-align:center; padding:3rem;">
                <p style="color:var(--text-dim); font-size:16px; margin-bottom:1.5rem;">You have not created any packages yet.</p>
                <a class="btn" href="create_package.php">Create Your First Package</a>
            </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>