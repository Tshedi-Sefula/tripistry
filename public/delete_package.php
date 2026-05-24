<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();
if (!isAgency()) { die("Access denied."); }
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) { die("Invalid package ID."); }

$packageID = (int)$_GET["id"];
$userID    = $_SESSION["user_id"];

$stmt = $pdo->prepare("SELECT packageID FROM TravelPackage WHERE packageID=? AND agencyUserID=?");
$stmt->execute([$packageID, $userID]);
if (!$stmt->fetch()) { die("Package not found or access denied."); }

$pdo->prepare("DELETE FROM TravelPackage WHERE packageID=? AND agencyUserID=?")->execute([$packageID,$userID]);

header("Location: agency_packages.php");
exit;
