<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();
if (!isAgency()) { die("Access denied."); }

$userID = $_SESSION["user_id"];

$stmt = $pdo->prepare("
    SELECT agencyID
    FROM TravelAgency
    WHERE userID = ?
");
$stmt->execute([$userID]);
$agency = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$agency) {
    die("Agency profile not found.");
}

$agencyID = $agency["agencyID"];

$stmt = $pdo->prepare("
    SELECT
        packageID,
        title,
        description,
        totalPrice,
        durationDays,
        status,
        packageType
    FROM TravelPackage
    WHERE agencyID = ?
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
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<?php include "../includes/navbar.php"; ?>
<div class="wrapper">
    <div class="page-content">
        <h1 class="page-title">My Packages</h1>
        <p class="page-subtitle">YOUR PUBLISHED TRAVEL EXPERIENCES</p>

        <div style="display:flex; gap:1rem; margin-bottom:1.8rem;">
            <a class="btn-primary" href="create_package.php">+ Create New Package</a>
            <a class="btn-secondary" href="agency_dashboard.php">← Dashboard</a>
        </div>

        <?php if (count($packages) > 0): ?>
            <div class="planes-grid">
                <?php foreach ($packages as $pkg): ?>
                    <div class="plane-card">
                        <div class="plane-card-img"><span class="plane-emoji">✈️</span></div>
                        <div class="plane-card-body">
                            <div class="plane-model"><?php echo htmlspecialchars($pkg["title"]); ?></div>
                            <p class="view-description" style="font-size:13px; margin-bottom:.8rem;"><?php echo htmlspecialchars(substr($pkg["description"],0,80)); ?>…</p>
                            <div class="plane-stats">
                                <div class="plane-stat"><strong>R<?php echo number_format($pkg["basePrice"],2); ?></strong>price</div>
                                <div class="plane-stat"><strong><?php echo $pkg["durationDays"]; ?> days</strong>duration</div>
                            </div>
                            <div style="margin-top:.5rem;">
                                <span class="badge badge-<?php echo $pkg['status']; ?>"><?php echo ucfirst($pkg["status"]); ?></span>
                                <span class="badge badge-pending" style="margin-left:.4rem;"><?php echo ucfirst($pkg["packageType"]); ?></span>
                            </div>
                        </div>
                        <div class="plane-card-footer">
                            <a class="btn-view" href="edit_package.php?id=<?php echo $pkg["packageID"]; ?>">Edit</a>
                            <a class="btn-cancel" href="delete_package.php?id=<?php echo $pkg["packageID"]; ?>"
                               onclick="return confirm('Delete this package?');">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="glass-card" style="text-align:center; padding:3rem;">
                <p style="color:var(--text-dim); margin-bottom:1.5rem;">No packages yet.</p>
                <a class="btn-primary" href="create_package.php">Create Your First Package</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
