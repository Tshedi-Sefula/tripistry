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
    SELECT *
    FROM travelPackage
    WHERE packageID = ?
      AND agencyID = ?
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
    $packageType = $_POST["packageType"];

    if ($title === "" || $basePrice <= 0 || $durationDays <= 0) {
        $message = "Please fill in all required fields correctly.";
    } else {
        $stmt = $pdo->prepare("
            UPDATE travelPackage
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

        $stmt->execute([
            $title,
            $description,
            $basePrice,
            $durationDays,
            $startDate,
            $endDate,
            $itinerary,
            $status,
            $packageType,
            $packageID,
            $agencyID
        ]);

        $message = "Package updated successfully.";

        $stmt = $pdo->prepare("
            SELECT *
            FROM travelPackage
            WHERE packageID = ?
              AND agencyID = ?
        ");
        $stmt->execute([$packageID, $agencyID]);
        $package = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Package - Tripistry</title>
    <link rel="stylesheet" href="/css/style.css">
    
</head>

<body>

<?php include "../includes/navbar.php"; ?>

<div class="container">

    <h1>Edit Travel Package</h1>

    <?php if ($message): ?>
        <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <form method="POST">

        <label>Title</label>
        <input type="text" name="title"
               value="<?php echo htmlspecialchars($package["title"]); ?>" required>

        <label>Description</label>
        <textarea name="description" required><?php echo htmlspecialchars($package["description"]); ?></textarea>

        <label>Base Price</label>
        <input type="number" name="basePrice" step="0.01" min="1"
               value="<?php echo htmlspecialchars($package["basePrice"]); ?>" required>

        <label>Duration Days</label>
        <input type="number" name="durationDays" min="1"
               value="<?php echo htmlspecialchars($package["durationDays"]); ?>" required>

        <label>Start Date</label>
        <input type="date" name="startDate"
               value="<?php echo htmlspecialchars($package["startDate"]); ?>" required>

        <label>End Date</label>
        <input type="date" name="endDate"
               value="<?php echo htmlspecialchars($package["endDate"]); ?>" required>

        <label>Itinerary</label>
        <textarea name="itinerary" required><?php echo htmlspecialchars($package["itinerary"]); ?></textarea>

        <label>Status</label>
        <select name="status" required>
            <option value="active" <?php if ($package["status"] === "active") echo "selected"; ?>>Active</option>
            <option value="inactive" <?php if ($package["status"] === "inactive") echo "selected"; ?>>Inactive</option>
            <option value="draft" <?php if ($package["status"] === "draft") echo "selected"; ?>>Draft</option>
        </select>

        <label>Package Type</label>
        <select name="packageType" required>
            <option value="regular" <?php if ($package["packageType"] === "regular") echo "selected"; ?>>Regular</option>
            <option value="group" <?php if ($package["packageType"] === "group") echo "selected"; ?>>Group</option>
        </select>

        <button type="submit">Update Package</button>

        <a class="btn back" href="agency_packages.php">Back</a>

    </form>

</div>

</body>
</html>