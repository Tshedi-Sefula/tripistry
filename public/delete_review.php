<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();

if (!isTraveller()) {
    die("Access denied.");
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid review ID.");
}

$reviewID = intval($_GET["id"]);

$travellerUserID = $_SESSION["userID"] ?? $_SESSION["user_id"] ?? null;

$stmt = $pdo->prepare("
    SELECT
        reviewID,
        packageID
    FROM Review
    WHERE reviewID = ?
      AND travellerUserID = ?
");

$stmt->execute([
    $reviewID,
    $travellerUserID
]);

$review = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$review) {
    die("Review not found or access denied.");
}

$packageID = $review["packageID"];

$stmt = $pdo->prepare("
    DELETE FROM Review
    WHERE reviewID = ?
      AND travellerUserID = ?
");

$stmt->execute([
    $reviewID,
    $travellerUserID
]);

header("Location: package_details.php?id=" . $packageID);
exit;
?>