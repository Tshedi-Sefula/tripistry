<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();

if (!isAgency()) {
    die("Access denied.");
}

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
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $basePrice = floatval($_POST["basePrice"]);
    $durationDays = intval($_POST["durationDays"]);
    $startDate = $_POST["startDate"];
    $endDate = $_POST["endDate"];
    $itinerary = trim($_POST["itinerary"]);
    $packageType = $_POST["packageType"];

    if ($title === "" || $basePrice <= 0 || $durationDays <= 0) {
        $message = "Please fill in all required fields correctly.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO travelPackage
            (
                agencyID,
                title,
                description,
                basePrice,
                durationDays,
                startDate,
                endDate,
                itinerary,
                status,
                packageType
            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?
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
            $itinerary,
            $packageType
        ]);

        $message = "Package created successfully.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Package - Tripistry</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<?php include "../includes/navbar.php"; ?>

<div class="container">

    <h1>Create Travel Package</h1>

    <?php if ($message): ?>
        <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <form method="POST">

        <label>Title</label>
        <input type="text" name="title" required>

        <label>Description</label>
        <textarea name="description" required></textarea>

        <label>Base Price</label>
        <input type="number" name="basePrice" step="0.01" min="1" required>

        <label>Duration Days</label>
        <input type="number" name="durationDays" min="1" required>

        <label>Start Date</label>
        <input type="date" name="startDate" required>

        <label>End Date</label>
        <input type="date" name="endDate" required>

        <label>Itinerary</label>
        <textarea name="itinerary" required></textarea>

        <label>Package Type</label>
        <select name="packageType" required>
            <option value="regular">Regular</option>
            <option value="group">Group</option>
        </select>

        <button type="submit">Create Package</button>

        <a class="btn back" href="agency_packages.php">Back</a>

    </form>

</div>

</body>
</html>