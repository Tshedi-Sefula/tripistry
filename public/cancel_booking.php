<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();
if (!isTraveller()) { die("Access denied."); }
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) { die("Invalid booking ID."); }

$bookingID = (int)$_GET["id"];
$userID    = $_SESSION["user_id"];

$stmt = $pdo->prepare("SELECT bookingID FROM Booking WHERE bookingID = ? AND travellerUserID = ?");
$stmt->execute([$bookingID, $userID]);
if (!$stmt->fetch()) { die("Booking not found or access denied."); }

$stmt = $pdo->prepare("DELETE FROM Booking WHERE bookingID = ? AND travellerUserID = ?");
$stmt->execute([$bookingID, $userID]);

header("Location: my_bookings.php");
exit;
