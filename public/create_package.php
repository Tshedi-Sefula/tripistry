<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();
if (!isAgency()) { die("Access denied."); }

$userID  = $_SESSION["user_id"];
$message = "";
$msgType = "error";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title       = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $basePrice   = floatval($_POST["basePrice"]);
    $durationDays = intval($_POST["durationDays"]);
    $startDate   = $_POST["startDate"];
    $endDate     = $_POST["endDate"];
    $itinerary   = trim($_POST["itinerary"]);
    $packageType = $_POST["packageType"];

    if ($title === "" || $basePrice <= 0 || $durationDays <= 0) {
        $message = "Please fill in all required fields correctly.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO TravelPackage
                (agencyUserID, title, description, basePrice, durationDays, startDate, endDate, itinerary, status, packageType)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', ?)
        ");
        $stmt->execute([$userID, $title, $description, $basePrice, $durationDays, $startDate, $endDate, $itinerary, $packageType]);

        $newPkgID = $pdo->lastInsertId();
        if ($packageType === "regular") {
            $pdo->prepare("INSERT INTO RegularPackage (packageID) VALUES (?)")->execute([$newPkgID]);
        } else {
            $pdo->prepare("INSERT INTO GroupTrip (packageID, minGroupSize, maxGroupSize, currentGroupSize) VALUES (?,2,20,0)")->execute([$newPkgID]);
        }

        $message = "Package created successfully!";
        $msgType = "success";
    }
}
?>
<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Package — Tripistry</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<?php include "../includes/navbar.php"; ?>
<div class="wrapper">
    <div class="page-content">
        <a class="btn-back" href="agency_packages.php">Back to My Packages</a>
        <h1 class="page-title">Create Package</h1>
        <p class="page-subtitle">DESIGN A NEW TRAVEL EXPERIENCE</p>

        <div class="glass-card" style="max-width:680px;">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <form method="POST" class="book-form">
                <div class="form-group">
                    <label>Package Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Zanzibar Escape">
                </div>
                <div class="form-group">
                    <label>Package Type</label>
                    <select name="packageType" required>
                        <option value="regular">Regular</option>
                        <option value="group">Group</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Base Price (R) *</label>
                    <input type="number" name="basePrice" step="0.01" min="1" required placeholder="e.g. 12500">
                </div>
                <div class="form-group">
                    <label>Duration (days) *</label>
                    <input type="number" name="durationDays" min="1" required placeholder="e.g. 7">
                </div>
                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="startDate">
                </div>
                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="endDate">
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Brief overview of this package…"></textarea>
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Itinerary</label>
                    <textarea name="itinerary" rows="5" placeholder="Day 1: Arrival…&#10;Day 2: City tour…"></textarea>
                </div>
                <button type="submit" class="btn-search">Create Package</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
