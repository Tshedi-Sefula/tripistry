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

$stmt = $pdo->prepare("
    SELECT *
    FROM TravelPackage
    WHERE packageID = ?
      AND agencyUserID = ?
");
$stmt->execute([$packageID, $agencyID]);
$package = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$package) {
    die("Package not found or you do not own this package.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $basePrice = floatval($_POST["basePrice"]);
    $durationDays = intval($_POST["durationDays"]);
    $startDate = $_POST["startDate"];
    $endDate = $_POST["endDate"];
    $itinerary = trim($_POST["itinerary"]);
    $status = $_POST["status"];

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
                status = ?
            WHERE packageID = ?
              AND agencyUserID = ?
        ");

        $stmt->execute([
            $title,
            $description,
            $basePrice,
            $durationDays,
            $startDate,
            $endDate,
            $itinerary,
            $status,
            $packageID,
            $agencyID
        ]);

        $message = "Package updated successfully.";

        // Refresh package data
        $stmt = $pdo->prepare("
            SELECT * FROM TravelPackage 
            WHERE packageID = ? AND agencyUserID = ?
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
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include "../includes/navbar.php"; ?>

<div class="wrapper">
    <div class="page-content">
        <a class="btn-back" href="agency_packages.php">Back to My Packages</a>

        <h1 class="page-title">Edit Package</h1>
        <p class="page-subtitle">UPDATE YOUR TRAVEL EXPERIENCE</p>

        <div class="glass-card" style="max-width:680px;">

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <form method="POST" class="book-form">

                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title"
                           value="<?php echo htmlspecialchars($package["title"]); ?>" required>
                </div>

                <div class="form-group">
                    <label>Base Price (R)</label>
                    <input type="number" name="basePrice" step="0.01" min="1"
                           value="<?php echo htmlspecialchars($package["basePrice"]); ?>" required>
                </div>

                <div class="form-group">
                    <label>Duration (days)</label>
                    <input type="number" name="durationDays" min="1"
                           value="<?php echo htmlspecialchars($package["durationDays"]); ?>" required>
                </div>

                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="startDate"
                           value="<?php echo htmlspecialchars($package["startDate"]); ?>" required>
                </div>

                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="endDate"
                           value="<?php echo htmlspecialchars($package["endDate"]); ?>" required>
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
                    <textarea name="description" required><?php echo htmlspecialchars($package["description"]); ?></textarea>
                </div>

                <div class="form-group" style="grid-column:1/-1;">
                    <label>Itinerary</label>
                    <textarea name="itinerary" required><?php echo htmlspecialchars($package["itinerary"]); ?></textarea>
                </div>

                <button type="submit" class="btn-search">Update Package</button>
                <a class="btn-secondary" href="agency_packages.php" style="display:inline-flex; align-items:center;">Back</a>

            </form>
        </div>
    </div>
</div>

</body>
</html>