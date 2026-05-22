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
$userID = $_SESSION["user_id"];

$stmt = $pdo->prepare("
    SELECT travellerID
    FROM traveller
    WHERE userID = ?
");

$stmt->execute([$userID]);

$traveller = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$traveller) {
    die("Traveller profile not found.");
}

$travellerID = $traveller["travellerID"];

$stmt = $pdo->prepare("
    SELECT
        p.packageID,
        p.title,
        p.agencyID
    FROM travelPackage p
    WHERE p.packageID = ?
");

$stmt->execute([$packageID]);

$package = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$package) {
    die("Package not found.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $rating = floatval($_POST["rating"]);
    $comment = trim($_POST["comment"]);

    if ($rating < 1 || $rating > 5) {
        $message = "Rating must be between 1 and 5.";
    } else {

            $stmt = $pdo->prepare("
        INSERT INTO review
            (
            travellerID,
            targetType,
            packageID,
            agencyID,
            rating,
            comment
        )
        VALUES
        (
            ?,
            'package',
            ?,
            NULL,
            ?,
            ?
        )
    ");

$stmt->execute([
    $travellerID,
    $packageID,
    $rating,
    $comment
]);

        $message = "Review submitted successfully.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Leave Review - Tripistry</title>
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>

<?php include "../includes/navbar.php"; ?>

<div class="container">

    <h1>Leave Review</h1>

    <h2>
        <?php echo htmlspecialchars($package["title"]); ?>
    </h2>

    <?php if ($message): ?>

        <p>
            <strong><?php echo htmlspecialchars($message); ?></strong>
        </p>

    <?php endif; ?>

    <form method="POST">

        <label>Rating (1 - 5)</label><br>

        <input
            type="number"
            name="rating"
            step="0.1"
            min="1"
            max="5"
            required
        ><br>

        <label>Comment</label><br>

        <textarea name="comment" required></textarea><br>

        <button type="submit">
            Submit Review
        </button>

    </form>

</div>

</body>
</html>