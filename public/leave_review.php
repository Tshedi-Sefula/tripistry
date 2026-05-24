<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();

if (!isTraveller()) {
    die("Only travellers can leave reviews.");
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid package ID.");
}

$packageID = $_GET["id"];
$userID    = $_SESSION["user_id"];

$stmt = $pdo->prepare("SELECT travellerID FROM traveller WHERE userID = ?");
$stmt->execute([$userID]);
$traveller = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$traveller) {
    die("Traveller profile not found.");
}
$travellerID = $traveller["travellerID"];

$stmt = $pdo->prepare("SELECT packageID, title, agencyID FROM travelPackage WHERE packageID = ?");
$stmt->execute([$packageID]);
$package = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$package) {
    die("Package not found.");
}

$message = "";
$msgType = "error";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $rating  = floatval($_POST["rating"]);
    $comment = trim($_POST["comment"]);

    if ($rating < 1 || $rating > 5) {
        $message = "Rating must be between 1 and 5.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO review (travellerID, targetType, packageID, agencyID, rating, comment)
            VALUES (?, 'package', ?, NULL, ?, ?)
        ");
        $stmt->execute([$travellerID, $packageID, $rating, $comment]);
        $message = "Review submitted successfully!";
        $msgType = "success";
    }
}
?>
<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Review — Tripistry</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<?php include "../includes/navbar.php"; ?>

<div class="wrapper">
    <div class="page-content">
        <a class="btn-back" href="package_details.php?id=<?php echo $packageID; ?>">Back to Package</a>

        <h1 class="page-title">Leave a Review</h1>
        <p class="page-subtitle"><?php echo htmlspecialchars($package["title"]); ?></p>

        <div class="glass-card" style="max-width:480px;">

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="rating">Rating (1 – 5)</label>
                    <input type="number" id="rating" name="rating" step="0.1" min="1" max="5" required placeholder="e.g. 4.5">
                </div>
                <div class="form-group">
                    <label for="comment">Your Review</label>
                    <textarea id="comment" name="comment" rows="4" required
                        placeholder="Share your experience with this package…"></textarea>
                </div>
                <button type="submit" class="btn-search">Submit Review</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
