<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid package ID.");
}

$packageID = $_GET["id"];

$stmt = $pdo->prepare("
    SELECT
        p.packageID, p.title, p.description, p.totalPrice, p.durationDays,
        p.startDate, p.endDate, p.itinerary, p.packageType,
        a.name AS agencyName, a.phone AS agencyPhone,
        a.website AS agencyWebsite, a.address AS agencyAddress, a.rating AS agencyRating
    FROM travelPackage p
    JOIN travelAgency a ON p.agencyID = a.agencyID
    WHERE p.packageID = ?
");
$stmt->execute([$packageID]);
$package = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$package) {
    die("Package not found.");
}

$reviewStmt = $pdo->prepare("
    SELECT r.rating, r.comment, r.reviewDate, t.name AS travellerName
    FROM review r
    JOIN traveller t ON r.travellerID = t.travellerID
    WHERE r.packageID = ? AND r.targetType = 'package'
    ORDER BY r.reviewDate DESC
");
$reviewStmt->execute([$packageID]);
$reviews = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($package["title"]); ?> — Tripistry</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<?php include "../includes/navbar.php"; ?>

<div class="wrapper">
    <div class="page-content">
        <a class="btn-back" href="packages.php">Back to Packages</a>

        <div class="detail-layout">

            <!-- Main info -->
            <div>
                <div class="view-manufacturer"><?php echo ucfirst($package["packageType"]); ?> Package</div>
                <h1 class="view-model"><?php echo htmlspecialchars($package["title"]); ?></h1>
                <p class="view-description"><?php echo htmlspecialchars($package["description"]); ?></p>

                <div class="price-tag" style="margin: 1.2rem 0;">
                    R<?php echo number_format($package["totalPrice"], 2); ?>
                </div>

                <hr class="section-divider">

                <div class="view-specs">
                    <div class="spec-item">
                        <div class="spec-label">Duration</div>
                        <div class="spec-value"><?php echo $package["durationDays"]; ?> <span class="spec-unit">days</span></div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">Start Date</div>
                        <div class="spec-value" style="font-size:1rem;"><?php echo htmlspecialchars($package["startDate"]); ?></div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">End Date</div>
                        <div class="spec-value" style="font-size:1rem;"><?php echo htmlspecialchars($package["endDate"]); ?></div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">Package Type</div>
                        <div class="spec-value" style="font-size:1rem;"><?php echo ucfirst($package["packageType"]); ?></div>
                    </div>
                </div>

                <hr class="section-divider">

                <div class="spec-label" style="margin-bottom:.5rem;">Itinerary</div>
                <div class="itinerary-block"><?php echo nl2br(htmlspecialchars($package["itinerary"])); ?></div>

                <div class="btn-row" style="margin-top:1.8rem;">
                    <a class="btn" href="book_package.php?id=<?php echo $package["packageID"]; ?>">Book This Package</a>
                    <a class="btn-secondary" href="leave_review.php?id=<?php echo $package["packageID"]; ?>">Leave a Review</a>
                </div>
            </div>

            <!-- Side panel: Agency + Reviews -->
            <div style="display:flex; flex-direction:column; gap:1.4rem;">

                <div class="detail-panel">
                    <h2>Agency Info</h2>
                    <div class="detail-row-item"><strong>Agency</strong><span><?php echo htmlspecialchars($package["agencyName"]); ?></span></div>
                    <div class="detail-row-item"><strong>Phone</strong><span><?php echo htmlspecialchars($package["agencyPhone"]); ?></span></div>
                    <div class="detail-row-item"><strong>Website</strong><span><?php echo htmlspecialchars($package["agencyWebsite"]); ?></span></div>
                    <div class="detail-row-item"><strong>Address</strong><span><?php echo htmlspecialchars($package["agencyAddress"]); ?></span></div>
                    <div class="detail-row-item"><strong>Rating</strong>
                        <span style="color:var(--gold);">★ <?php echo htmlspecialchars($package["agencyRating"]); ?>/5</span>
                    </div>
                </div>

                <div class="detail-panel">
                    <h2>Reviews</h2>
                    <?php if (count($reviews) > 0): ?>
                        <div class="reviews-list">
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-card">
                                    <div class="review-rating">★ <?php echo htmlspecialchars($review["rating"]); ?>/5</div>
                                    <p><?php echo htmlspecialchars($review["comment"]); ?></p>
                                    <small>
                                        By <?php echo htmlspecialchars($review["travellerName"]); ?>
                                        on <?php echo htmlspecialchars($review["reviewDate"]); ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p style="color:var(--text-dim); font-size:14px;">No reviews yet. Be the first!</p>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>

</body>
</html>
