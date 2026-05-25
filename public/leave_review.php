<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/sentiment.php";

requireLogin();

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid package ID.");
}

$packageID = intval($_GET["id"]);

$stmt = $pdo->prepare("
    SELECT packageID, title
    FROM TravelPackage
    WHERE packageID = ?
");
$stmt->execute([$packageID]);
$package = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$package) {
    die("Package not found.");
}

$stmt = $pdo->query("SELECT userID FROM Traveller LIMIT 1");
$traveller = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$traveller) {
    die("No traveller profile exists.");
}

$travellerUserID = $traveller["userID"];
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $rating = floatval($_POST["rating"]);
    $comment = trim($_POST["comment"]);
    $sentiment = analyseSentiment($comment);

    if ($rating < 1 || $rating > 5) {
        $message = "Rating must be between 1 and 5.";
    } elseif ($comment === "") {
        $message = "Comment cannot be empty.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO Review (
                travellerUserID,
                rating,
                comment,
                targetType,
                packageID,
                agencyUserID,
                sentiment
            )
            VALUES (?, ?, ?, 'package', ?, NULL, ?)
        ");

        $stmt->execute([
            $travellerUserID,
            $rating,
            $comment,
            $packageID,
            $sentiment
        ]);

        $message = "Review submitted successfully. Sentiment detected: " . $sentiment;
    }
}
?>

<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Review — Tripistry</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include "../includes/navbar.php"; ?>

<div class="wrapper">
    <div class="page-content">
        <a class="btn-back" href="package_details.php?id=<?php echo htmlspecialchars($packageID); ?>">Back to Package</a>

        <h1 class="page-title">Leave a Review</h1>
        <p class="page-subtitle"><?php echo htmlspecialchars($package["title"]); ?></p>

        <div class="glass-card" style="max-width:520px;">

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label>Rating (1 – 5)</label>
                    <input type="number" name="rating" step="0.1" min="1" max="5" required placeholder="e.g. 4.5">
                </div>
                <div class="form-group">
                    <label>Comment</label>
                    <textarea name="comment" required placeholder="Share your experience…"></textarea>
                </div>
                <button type="submit" class="btn-search">Submit Review</button>
            </form>

        </div>
    </div>
</div>

</body>
</html>