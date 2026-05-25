<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();

if (!isAgency()) {
    die("Access denied.");
}

$userID = $_SESSION["user_id"];

$stmt = $pdo->prepare("SELECT userID FROM TravelAgency WHERE userID = ?");
$stmt->execute([$userID]);
$agency = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$agency) {
    die("Agency profile not found.");
}

$agencyID = $agency["userID"];
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title        = trim($_POST["title"]);
    $description  = trim($_POST["description"]);
    $basePrice    = floatval($_POST["basePrice"]);
    $durationDays = intval($_POST["durationDays"]);
    $startDate    = $_POST["startDate"];
    $endDate      = $_POST["endDate"];
    $itinerary    = trim($_POST["itinerary"]);

    if ($title === "" || $basePrice <= 0 || $durationDays <= 0) {
        $message = "Please fill in all required fields correctly.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO TravelPackage 
            (
                agencyUserID,
                title,
                description,
                basePrice,
                durationDays,
                startDate,
                endDate,
                itinerary,
                status
            )
            VALUES 
            (
                ?, ?, ?, ?, ?, ?, ?, ?, 'active'
            )
        ");

        $stmt->execute([
            $agencyID,
            $title,
            $description,
            $basePrice,
            $durationDays,
            $startDate,
            $endDate,
            $itinerary
        ]);

        $message = "Package created successfully.";
    }
}
?>

<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Package — Tripistry</title>
    <link rel="stylesheet" href="css/style.css">
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
                    <label>Title <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="title" required placeholder="e.g. Zanzibar Escape">
                </div>

                <div class="form-group">
                    <label>Base Price (R) <span style="color:var(--danger);">*</span></label>
                    <input type="number" name="basePrice" step="0.01" min="1" required placeholder="e.g. 12500">
                </div>

                <div class="form-group">
                    <label>Duration (days) <span style="color:var(--danger);">*</span></label>
                    <input type="number" name="durationDays" min="1" required placeholder="e.g. 7">
                </div>

                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="startDate" required>
                </div>

                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="endDate" required>
                </div>

                <div class="form-group" style="grid-column:1/-1;">
                    <label>Description</label>
                    <textarea name="description" required placeholder="A brief overview of what this package offers…"></textarea>
                </div>

                <div class="form-group" style="grid-column:1/-1;">
                    <label>Itinerary</label>
                    <textarea name="itinerary" required placeholder="Day 1: Arrival&#10;Day 2: City tour…"></textarea>
                </div>

                <button type="submit" class="btn-search">Create Package</button>
                <a class="btn-secondary" href="agency_packages.php" style="display:inline-flex; align-items:center;">Back</a>

            </form>
        </div>
    </div>
</div>

</body>
</html>