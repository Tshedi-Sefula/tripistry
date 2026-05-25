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
    SELECT userID 
    FROM TravelAgency 
    WHERE userID = ?
");
$stmt->execute([$userID]);
$agency = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$agency) {
    die("Agency profile not found.");
}

$agencyID = $agency["userID"];

// Check ownership
$stmt = $pdo->prepare("
    SELECT packageID 
    FROM TravelPackage 
    WHERE packageID = ? AND agencyUserID = ?
");
$stmt->execute([$packageID, $agencyID]);

if (!$stmt->fetch()) {
    die("Package not found or access denied.");
}

// Delete
$stmt = $pdo->prepare("
    DELETE FROM TravelPackage 
    WHERE packageID = ? AND agencyUserID = ?
");
$stmt->execute([$packageID, $agencyID]);

header("Location: agency_packages.php");
exit;
?>