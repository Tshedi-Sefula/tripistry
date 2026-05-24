<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) { die("Invalid package ID."); }

$packageID = (int)$_GET["id"];

$stmt = $pdo->prepare("
SELECT 
        p.packageID, 
        p.title, 
        p.description, 
        p.basePrice, 
        p.durationDays,
        p.startDate, 
        p.endDate, 
        p.itinerary, 
        p.status,
        IF(g.packageID IS NOT NULL, 'Group Trip', 'Regular Trip') AS packageType,
        a.name AS agencyName, 
        a.userID AS agencyUserID,
        u.phone AS agencyPhone,
        a.website AS agencyWebsite, 
        a.address AS agencyAddress, 
        a.rating AS agencyRating
    FROM TravelPackage p
    JOIN TravelAgency a ON p.agencyUserID = a.userID
    JOIN User u ON a.userID = u.userID
    LEFT JOIN GroupTrip g ON p.packageID = g.packageID
    WHERE p.packageID = ?
");
$stmt->execute([$packageID]);
$package = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$package) { die("Package not found."); }

$reviewStmt = $pdo->prepare("
SELECT 
        r.rating, 
        r.comment, 
        r.reviewDate, 
        r.sentiment,
        CONCAT(t.firstName, ' ', t.lastName) AS travellerName
    FROM Review r
    JOIN Traveller t ON r.travellerUserID = t.userID
    WHERE r.packageID = ? 
      AND r.targetType = 'package'
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

<div class="view-layout">
            
            <div class="view-image-wrap">
                <span class="plane-emoji-large">✈️</span>
            </div>

            <div class="view-content">
                
                <div class="view-info-header">
                    <div class="view-manufacturer"><?php echo htmlspecialchars($package["agencyName"]); ?></div>
                    <h1 class="view-model"><?php echo htmlspecialchars($package["title"]); ?></h1>
                    <p class="view-description"><?php echo htmlspecialchars($package["description"]); ?></p>
                    <div class="price-tag" style="margin-top: 1rem;">
                        R<?php echo number_format($package["basePrice"], 2); ?>
                    </div>
                </div>

                <div class="view-specs">
                    <div class="spec-item">
                        <div class="spec-label">Duration</div>
                        <div class="spec-value"><?php echo $package["durationDays"]; ?> <span class="spec-unit">Days</span></div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">Package Type</div>
                        <div class="spec-value" style="font-size: 1.1rem;">
                            <?php echo htmlspecialchars($package["packageType"]); ?>
                        </div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">Status</div>
                        <div class="spec-value" style="font-size: 1.1rem; color: var(--gold);">
                            <?php echo htmlspecialchars(ucfirst($package["status"])); ?>
                        </div>
                    </div>
                </div>

                <div class="section-divider"></div>
                
                <h3 style="font-family: var(--font-display); color: #fff; margin-bottom: 0.5rem;">Itinerary</h3>
                <p class="view-description"><?php echo nl2br(htmlspecialchars($package["itinerary"])); ?></p>

                <div class="section-divider"></div>

                <h3 style="font-family: var(--font-display); color: #fff; margin-bottom: 0.5rem;">Agency Information</h3>
                <p class="view-description">
                    <strong>Phone:</strong> <?php echo htmlspecialchars($package["agencyPhone"]); ?><br>
                    <strong>Website:</strong> <?php echo htmlspecialchars($package["agencyWebsite"]); ?><br>
                    <strong>Address:</strong> <?php echo htmlspecialchars($package["agencyAddress"]); ?><br>
                    <strong>Rating:</strong> <span style="color: var(--gold);"><?php echo htmlspecialchars($package["agencyRating"]); ?>/5</span>
                </p>

                <div class="section-divider"></div>

                <h3 style="font-family: var(--font-display); color: #fff; margin-bottom: 1rem;">Reviews</h3>
                
                <?php if (count($reviews) > 0): ?>
                    <div class="reviews-list" style="display:flex; flex-direction:column; gap:1rem;">
                        <?php foreach ($reviews as $review): ?>
                            <div class="glass-card" style="padding: 1.5rem;">
                                <div style="display:flex; justify-content:space-between; margin-bottom: 0.5rem;">
                                    <strong style="color:var(--gold);">Rating: <?php echo htmlspecialchars($review["rating"]); ?>/5</strong>
                                    
                                    <span class="cabin-tag"><?php echo htmlspecialchars(ucfirst($review["sentiment"])); ?></span>
                                </div>
                                
                                <p class="view-description" style="margin-bottom: 0.8rem;">
                                    "<?php echo htmlspecialchars($review["comment"]); ?>"
                                </p>
                                
                                <small style="color:var(--text-dim);">
                                    By <?php echo htmlspecialchars($review["travellerName"]); ?> on <?php echo htmlspecialchars($review["reviewDate"]); ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="view-description">No reviews yet.</p>
                <?php endif; ?>

            </div>
        </div>
            </div>

            <div>
                <div class="view-manufacturer"><?php echo ucfirst($package["packageType"]); ?> Package · <?php echo htmlspecialchars($package["agencyName"]); ?></div>
                <h1 class="view-model"><?php echo htmlspecialchars($package["title"]); ?></h1>
                <p class="view-description"><?php echo htmlspecialchars($package["description"]); ?></p>

                <div style="font-size:2rem; font-family:var(--font-display); color:var(--at-accent);
                            text-shadow:2px 2px 0 var(--at-outline); margin:1.2rem 0;">
                    R<?php echo number_format($package["basePrice"], 2); ?>
                </div>

                <hr class="section-divider">

                <div class="view-specs">
                    <div class="spec-item">
                        <div class="spec-label">Duration</div>
                        <div class="spec-value"><?php echo $package["durationDays"]; ?> <span class="spec-unit">days</span></div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">Start Date</div>
                        <div class="spec-value" style="font-size:1rem;"><?php echo $package["startDate"]; ?></div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">End Date</div>
                        <div class="spec-value" style="font-size:1rem;"><?php echo $package["endDate"]; ?></div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">Agency Rating</div>
                        <div class="spec-value" style="color:var(--at-accent);">★ <?php echo $package["agencyRating"]; ?></div>
                    </div>
                </div>

                <hr class="section-divider">

                <div class="spec-label" style="margin-bottom:.5rem;">Itinerary</div>
                <p class="view-description"><?php echo nl2br(htmlspecialchars($package["itinerary"])); ?></p>

                <?php if (isTraveller()): ?>
                    <div style="display:flex; gap:1rem; margin-top:1.8rem; flex-wrap:wrap;">
                        <a class="btn-primary" href="book_package.php?id=<?php echo $package["packageID"]; ?>">Book This Package</a>
                        <a class="btn-secondary" href="leave_review.php?id=<?php echo $package["packageID"]; ?>">Leave a Review</a>
                    </div>
                <?php endif; ?>

                <hr class="section-divider" style="margin-top:2rem;">

                <!-- Agency info -->
                <div class="glass-card" style="margin-top:1rem;">
                    <h3 style="font-family:var(--font-display); color:var(--at-accent); margin-bottom:1rem;">Agency Info</h3>
                    <div class="detail-row"><span>Name</span><strong><?php echo htmlspecialchars($package["agencyName"]); ?></strong></div>
                    <div class="detail-row"><span>Phone</span><strong><?php echo htmlspecialchars($package["agencyPhone"] ?? "N/A"); ?></strong></div>
                    <div class="detail-row"><span>Website</span><strong><?php echo htmlspecialchars($package["agencyWebsite"] ?? "N/A"); ?></strong></div>
                    <div class="detail-row"><span>Address</span><strong><?php echo htmlspecialchars($package["agencyAddress"] ?? "N/A"); ?></strong></div>
                </div>

                <!-- Reviews -->
                <div class="glass-card" style="margin-top:1.4rem;">
                    <h3 style="font-family:var(--font-display); color:var(--at-accent); margin-bottom:1rem;">
                        Reviews (<?php echo count($reviews); ?>)
                    </h3>
                    <?php if (count($reviews) > 0): ?>
                        <?php foreach ($reviews as $r): ?>
                            <div style="border-bottom:1px dashed rgba(255,255,255,0.1); padding:0.8rem 0;">
                                <div style="color:var(--at-accent); font-family:var(--font-display);">★ <?php echo $r["rating"]; ?>/5</div>
                                <p style="color:var(--text-light); font-size:14px; margin:.3rem 0;"><?php echo htmlspecialchars($r["comment"]); ?></p>
                                <small style="color:var(--text-dim);">By <?php echo htmlspecialchars($r["travellerName"]); ?> · <?php echo $r["reviewDate"]; ?></small>
                            </div>
                        <?php endforeach; ?>
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
