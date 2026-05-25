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
<html>
<head>
    <title>Create Package - Tripistry</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 30px; }
        .container { background: white; max-width: 750px; margin: auto; padding: 25px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        input, textarea, select { width: 100%; padding: 10px; margin-top: 6px; margin-bottom: 15px; }
        textarea { height: 120px; }
        button, .btn { padding: 10px 15px; background: #007bff; color: white; border: none; border-radius: 5px; }
        .back { background: #555; }
    </style>
</head>
<body>

<?php include "../includes/navbar.php"; ?>

<div class="container">
    <h1>Create Travel Package</h1>

    <?php if ($message): ?>
        <p><strong><?= htmlspecialchars($message) ?></strong></p>
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

        <button type="submit">Create Package</button>
        <a class="btn back" href="agency_packages.php">Back</a>
    </form>
</div>
</body>
</html>