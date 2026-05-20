<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();

if (!isAgency()) {
    die("Access denied.");
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid package ID.");
}

$packageID = $_GET["id"];
$userID = $_SESSION["user_id"];



$stmt = $pdo->prepare("
    SELECT agencyID
    FROM travelAgency
    WHERE userID = ?
");

$stmt->execute([$userID]);

$agency = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$agency) {
    die("Agency profile not found.");
}

$agencyID = $agency["agencyID"];



$stmt = $pdo->prepare("
    SELECT packageID
    FROM travelPackage
    WHERE packageID = ?
      AND agencyID = ?
");

$stmt->execute([$packageID, $agencyID]);

$package = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$package) {
    die("Package not found or access denied.");
}


$stmt = $pdo->prepare("
    DELETE FROM travelPackage
    WHERE packageID = ?
      AND agencyID = ?
");

$stmt->execute([$packageID, $agencyID]);

header("Location: agency_packages.php");
exit;
?>