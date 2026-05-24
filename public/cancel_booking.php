<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();

if (!isTraveller()) {
    die("Access denied.");
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid booking ID.");
}

$bookingID = $_GET["id"];
$userID = $_SESSION["user_id"];


$stmt = $pdo->prepare("
    SELECT travellerID
    FROM Traveller
    WHERE userID = ?
");

$stmt->execute([$userID]);

$traveller = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$traveller) {
    die("Traveller profile not found.");
}

$travellerID = $traveller["travellerID"];



$stmt = $pdo->prepare("
    SELECT bookingID
    FROM booking
    WHERE bookingID = ?
      AND travellerID = ?
");

$stmt->execute([$bookingID, $travellerID]);

$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    die("Booking not found or access denied.");
}



$stmt = $pdo->prepare("
    DELETE FROM booking
    WHERE bookingID = ?
      AND travellerID = ?
");

$stmt->execute([$bookingID, $travellerID]);

header("Location: my_bookings.php");
exit;
?>