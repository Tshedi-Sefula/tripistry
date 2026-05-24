<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/sentiment.php";

requireLogin();

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid package ID.");
}

$packageID = intval($_GET["id"]);
$userID    = $_SESSION["user_id"];

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

$sessionUserID = $_SESSION["userID"] ?? $_SESSION["user_id"] ?? null;

$stmt = $pdo->prepare("
    SELECT userID
    FROM Traveller
    WHERE userID = ?
");
$stmt->execute([$sessionUserID]);
$traveller = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$traveller) {
    $stmt = $pdo->query("
        SELECT userID
        FROM Traveller
        LIMIT 1
    ");
    $traveller = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$traveller) {
    die("No traveller profile exists in the database.");
}

$travellerUserID = $traveller["userID"];

$message = "";
$msgType = "error";

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
    <link rel="stylesheet" href="/css/style.css">
    <title>Leave Review - Tripistry</title>
</head>

<body>
<?php include "../includes/navbar.php"; ?>

<h1>Leave Review</h1>

<h2><?php echo htmlspecialchars($package["title"]); ?></h2>

<?php if ($message): ?>
    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>

<form method="POST">
    <label>Rating (1 - 5)</label><br>
    <input type="number" name="rating" step="0.1" min="1" max="5" required><br><br>

    <label>Comment</label><br>
    <textarea name="comment" required></textarea><br><br>

    <button type="submit">Submit Review</button>
</form>

</body>
</html>
