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
        p.packageID,
        p.title,
        p.description,
        p.basePrice,
        p.durationDays,
        p.startDate,
        p.endDate,
        p.itinerary,
        p.status,
        a.name AS agencyName,
        a.website AS agencyWebsite,
        a.address AS agencyAddress,
        a.rating AS agencyRating
    FROM TravelPackage p
    JOIN TravelAgency a ON p.agencyUserID = a.userID
    WHERE p.packageID = ?
");
$stmt->execute([$packageID]);
$package = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$package) {
    die("Package not found.");
}

$reviewStmt = $pdo->prepare("
    SELECT 
        r.rating,
        r.comment,
        r.reviewDate,
        r.sentiment,
        u.email AS travellerName
    FROM Review r
    JOIN User u ON r.travellerUserID = u.userID
    WHERE r.packageID = ?
      AND r.targetType = 'package'
    ORDER BY r.reviewDate DESC
");

$reviewStmt->execute([$packageID]);
$reviews = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($package["title"]); ?></title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 30px;
        }

        .container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            max-width: 850px;
            margin: auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .price {
            color: green;
            font-size: 24px;
            font-weight: bold;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 10px;
            margin-top: 10px;
        }

        .back {
            background: #555;
        }

        .review-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: #fafafa;
        }
    </style>
</head>

<body>
<?php include "../includes/navbar.php"; ?>
<div class="container">

    <h1><?php echo htmlspecialchars($package["title"]); ?></h1>

    <p><?php echo htmlspecialchars($package["description"]); ?></p>

    <p class="price">
        R<?php echo number_format($package["basePrice"], 2); ?>
    </p>

    <p><strong>Duration:</strong> <?php echo $package["durationDays"]; ?> days</p>
    <p><strong>Package Type:</strong> <?php echo ucfirst($package["status"]); ?></p>
    <p><strong>Start Date:</strong> <?php echo htmlspecialchars($package["startDate"]); ?></p>
    <p><strong>End Date:</strong> <?php echo htmlspecialchars($package["endDate"]); ?></p>

    <h2>Itinerary</h2>
    <p><?php echo nl2br(htmlspecialchars($package["itinerary"])); ?></p>

    <h2>Agency Information</h2>
    <p><strong>Agency:</strong> <?php echo htmlspecialchars($package["agencyName"]); ?></p>
    <p><strong>Phone:</strong> <?php echo htmlspecialchars($package["agencyPhone"]); ?></p>
    <p><strong>Website:</strong> <?php echo htmlspecialchars($package["agencyWebsite"]); ?></p>
    <p><strong>Address:</strong> <?php echo htmlspecialchars($package["agencyAddress"]); ?></p>
    <p><strong>Rating:</strong> <?php echo htmlspecialchars($package["agencyRating"]); ?>/5</p>

    <h2>Reviews</h2>

    <?php if (count($reviews) > 0): ?>

        <?php foreach ($reviews as $review): ?>

            <div class="review-card">
                <p><strong>Rating:</strong> <?php echo htmlspecialchars($review["rating"]); ?>/5</p>

                <p><?php echo htmlspecialchars($review["comment"]); ?></p>
                <p>
                    <strong>Sentiment:</strong>
                    <?php echo htmlspecialchars($review["sentiment"]); ?>
                </p>
                <p>
                    <small>
                        By <?php echo htmlspecialchars($review["travellerName"]); ?>
                        on <?php echo htmlspecialchars($review["reviewDate"]); ?>
                    </small>
                </p>
            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <p>No reviews yet.</p>

    <?php endif; ?>

    <br>

    <a class="btn" href="book_package.php?id=<?php echo $package["packageID"]; ?>">
        Book This Package
    </a>

    <a class="btn" href="leave_review.php?id=<?php echo $package["packageID"]; ?>">
        Leave Review
    </a>

    <a class="btn back" href="packages.php">
        Back to Packages
    </a>

</div>

</body>
</html>