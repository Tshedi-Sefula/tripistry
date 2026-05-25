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
<html>
<head>
    <title>Group Matches</title>
</head>
<body>

<?php include "../includes/navbar.php"; ?>

<h1>Group Trip Matches</h1>

<p>
    Group trips are ranked using a simple compatibility score based on:
    group space, price, duration, dates and traveller preferences.
</p>

<?php if (count($matches) > 0): ?>

    <?php foreach ($matches as $trip): ?>

        <div style="border:1px solid #ccc; padding:15px; margin:15px;">
            <h2><?php echo htmlspecialchars($trip["title"]); ?></h2>

            <p>
                <strong>Match Score:</strong>
                <?php echo htmlspecialchars($trip["matchScore"]); ?>%
            </p>

            <p><?php echo htmlspecialchars($trip["description"]); ?></p>

            <p>
                <strong>Price:</strong>
                R<?php echo number_format($trip["basePrice"], 2); ?>
            </p>

            <p>
                <strong>Duration:</strong>
                <?php echo htmlspecialchars($trip["durationDays"]); ?> days
            </p>

            <p>
                <strong>Group:</strong>
                <?php echo htmlspecialchars($trip["currentGroupSize"]); ?>
                /
                <?php echo htmlspecialchars($trip["maxGroupSize"]); ?>
            </p>

            <a href="package_details.php?id=<?php echo htmlspecialchars($trip["packageID"]); ?>">
                View Package
            </a>
        </div>

    <?php endforeach; ?>

<?php else: ?>

    <p>No group trips found.</p>

<?php endif; ?>

</body>
</html>