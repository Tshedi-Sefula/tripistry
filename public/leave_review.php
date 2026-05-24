<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();
if (!isTraveller()) { die("Only travellers can leave reviews."); }
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) { die("Invalid package ID."); }

$packageID = (int)$_GET["id"];
$userID    = $_SESSION["user_id"];

$stmt = $pdo->prepare("SELECT packageID, title FROM TravelPackage WHERE packageID=?");
$stmt->execute([$packageID]);
$package = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$package) { die("Package not found."); }

$message = "";
$msgType = "error";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $rating  = floatval($_POST["rating"]);
    $comment = trim($_POST["comment"]);

    if ($rating < 1 || $rating > 5) {
        $message = "Rating must be between 1 and 5.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO Review (travellerUserID, targetType, packageID, agencyUserID, rating, comment)
            VALUES (?, 'package', ?, NULL, ?, ?)
        ");
        $stmt->execute([$userID, $packageID, $rating, $comment]);
        $message = "Review submitted!";
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
            <form method="POST">
                <div class="form-group">
                    <label>Rating (1–5)</label>
                    <input type="number" name="rating" step="0.1" min="1" max="5" required placeholder="e.g. 4.5">
                </div>
                <div class="form-group">
                    <label>Your Review</label>
                    <textarea name="comment" rows="4" required placeholder="Share your experience…"></textarea>
                </div>
                <button type="submit" class="btn-search" style="margin-top:1rem;">Submit Review</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
