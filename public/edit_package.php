<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();
if (!isAgency()) { die("Access denied."); }
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) { die("Invalid package ID."); }

$packageID = (int)$_GET["id"];
$userID    = $_SESSION["user_id"];

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid package ID.");
}

$packageID = $_GET["id"];
$userID = $_SESSION["user_id"];

$stmt = $pdo->prepare("
    SELECT agencyID
    FROM TravelAgency
    WHERE userID = ?
");
$stmt->execute([$userID]);
$agency = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$agency) {
    die("Agency profile not found.");
}

$agencyID = $agency["agencyID"];

$stmt = $pdo->prepare("
    SELECT *
    FROM TravelPackage
    WHERE packageID = ?
      AND agencyID = ?
");
$stmt->execute([$packageID, $agencyID]);
$package = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$package) { die("Package not found or access denied."); }

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
    $status      = $_POST["status"];
    $packageType = $_POST["packageType"];

    if ($title === "" || $basePrice <= 0 || $durationDays <= 0) {
        $message = "Please fill in all required fields correctly.";
    } else {
        $stmt = $pdo->prepare("
            UPDATE TravelPackage
            SET
                title = ?,
                description = ?,
                basePrice = ?,
                durationDays = ?,
                startDate = ?,
                endDate = ?,
                itinerary = ?,
                status = ?,
                packageType = ?
            WHERE packageID = ?
              AND agencyID = ?
        ");
        $stmt->execute([$title,$description,$basePrice,$durationDays,
                        $startDate,$endDate,$itinerary,$status,$packageType,
                        $packageID,$userID]);
        $message = "Package updated successfully.";
        $msgType = "success";

        $stmt = $pdo->prepare("
            SELECT *
            FROM TravelPackage
            WHERE packageID = ?
              AND agencyID = ?
        ");
        $stmt->execute([$packageID, $agencyID]);
        $package = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Package — Tripistry</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<?php include "../includes/navbar.php"; ?>
<div class="wrapper">
    <div class="page-content">
        <a class="btn-back" href="agency_packages.php">Back to My Packages</a>
        <h1 class="page-title">Edit Package</h1>

        <div class="glass-card" style="max-width:680px;">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <form method="POST" class="book-form">
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" required value="<?php echo htmlspecialchars($package["title"]); ?>">
                </div>
                <div class="form-group">
                    <label>Package Type</label>
                    <select name="packageType" required>
                        <option value="regular" <?php if ($package["packageType"]==="regular") echo "selected"; ?>>Regular</option>
                        <option value="group"   <?php if ($package["packageType"]==="group")   echo "selected"; ?>>Group</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Base Price (R) *</label>
                    <input type="number" name="basePrice" step="0.01" min="1" required value="<?php echo $package["basePrice"]; ?>">
                </div>
                <div class="form-group">
                    <label>Duration (days) *</label>
                    <input type="number" name="durationDays" min="1" required value="<?php echo $package["durationDays"]; ?>">
                </div>
                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="startDate" value="<?php echo $package["startDate"]; ?>">
                </div>
                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="endDate" value="<?php echo $package["endDate"]; ?>">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" required>
                        <option value="active"   <?php if ($package["status"]==="active")   echo "selected"; ?>>Active</option>
                        <option value="inactive" <?php if ($package["status"]==="inactive") echo "selected"; ?>>Inactive</option>
                        <option value="draft"    <?php if ($package["status"]==="draft")    echo "selected"; ?>>Draft</option>
                    </select>
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Description</label>
                    <textarea name="description" rows="3"><?php echo htmlspecialchars($package["description"]); ?></textarea>
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Itinerary</label>
                    <textarea name="itinerary" rows="5"><?php echo htmlspecialchars($package["itinerary"]); ?></textarea>
                </div>
                <button type="submit" class="btn-search">Update Package</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
