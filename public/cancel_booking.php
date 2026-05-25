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

$bookingID = intval($_GET["id"]);

$stmt = $pdo->prepare("
    SELECT bookingID
    FROM Booking
    WHERE bookingID = ?
");

$stmt->execute([$bookingID]);

$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    die("Booking not found.");
}

$stmt = $pdo->prepare("
    UPDATE Booking
    SET status = 'cancelled'
    WHERE bookingID = ?
");

$stmt->execute([$bookingID]);

header("Location: my_bookings.php");
exit;
?>
