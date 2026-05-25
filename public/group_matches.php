<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();

if (!isTraveller()) {
    die("Access denied.");
}

$userID = $_SESSION["user_id"];

$stmt = $pdo->prepare("
    SELECT preference
    FROM TravellerPreference
    WHERE userID = ?
");
$stmt->execute([$userID]);
$preferences = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->prepare("
    SELECT
        p.packageID,
        p.title,
        p.description,
        p.basePrice,
        p.durationDays,
        p.startDate,
        p.endDate,
        g.minGroupSize,
        g.maxGroupSize,
        g.currentGroupSize
    FROM GroupTrip g
    JOIN TravelPackage p ON g.packageID = p.packageID
    WHERE p.status = 'active'
");
$stmt->execute();
$groupTrips = $stmt->fetchAll(PDO::FETCH_ASSOC);

$matches = [];

foreach ($groupTrips as $trip) {
    $score = 0;

    if ($trip["currentGroupSize"] < $trip["maxGroupSize"]) {
        $score += 30;
    }

    if ($trip["basePrice"] <= 15000) {
        $score += 25;
    }

    if ($trip["durationDays"] <= 7) {
        $score += 15;
    }

    foreach ($preferences as $pref) {
        $text = strtolower($trip["title"] . " " . $trip["description"]);

        if (strpos($text, strtolower($pref)) !== false) {
            $score += 20;
        }
    }

    if (!empty($trip["startDate"])) {
        $score += 10;
    }

    $trip["matchScore"] = min($score, 100);
    $matches[] = $trip;
}

usort($matches, function ($a, $b) {
    return $b["matchScore"] - $a["matchScore"];
});
?>

<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find a Group — Tripistry</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include "../includes/navbar.php"; ?>

<div class="wrapper">
    <div class="page-content">
        <h1 class="page-title">Group Trip Matches</h1>
        <p class="page-subtitle">RANKED BY COMPATIBILITY — GROUP SPACE, PRICE, DURATION &amp; YOUR PREFERENCES</p>

        <?php if (count($matches) > 0): ?>
            <div class="packages-grid">
                <?php foreach ($matches as $trip): ?>
                    <div class="package-card">
                        <h2><?php echo htmlspecialchars($trip["title"]); ?></h2>
                        <p><?php echo htmlspecialchars($trip["description"]); ?></p>
                        <div class="price-tag">R<?php echo number_format($trip["basePrice"], 2); ?></div>
                        <div class="package-meta">
                            <span class="meta-badge">⏱ <?php echo htmlspecialchars($trip["durationDays"]); ?> days</span>
                            <span class="meta-badge">👥 <?php echo htmlspecialchars($trip["currentGroupSize"]); ?>/<?php echo htmlspecialchars($trip["maxGroupSize"]); ?></span>
                            <span class="meta-badge" style="color:var(--gold); border-color:rgba(255,200,0,0.35);">
                                ★ <?php echo htmlspecialchars($trip["matchScore"]); ?>% match
                            </span>
                        </div>
                        <a class="btn" href="package_details.php?id=<?php echo htmlspecialchars($trip["packageID"]); ?>">
                            View Package
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="glass-card" style="text-align:center; padding:3rem;">
                <p style="color:var(--text-dim); font-size:16px;">No group trips found.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>