<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();
if (!isTraveller()) { die("Only travellers can book packages."); }

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) { die("Invalid package ID."); }

$packageID = (int)$_GET["id"];
$userID    = $_SESSION["user_id"];

$stmt = $pdo->prepare("SELECT packageID, title, basePrice FROM TravelPackage WHERE packageID = ? AND status = 'active'");
$stmt->execute([$packageID]);
$package = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$package) { die("Package not found."); }

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $numTravellers = intval($_POST["numTravellers"]);
    if ($numTravellers < 1) {
        $message = "Number of travellers must be at least 1.";
    } else {
        $totalAmount = $package["basePrice"] * $numTravellers;
        $stmt = $pdo->prepare("
            INSERT INTO Booking (travellerUserID, packageID, numTravellers, totalAmount, status, paymentStatus)
            VALUES (?, ?, ?, ?, 'pending', 'unpaid')
        ");
        $stmt->execute([$userID, $packageID, $numTravellers, $totalAmount]);
        $message = "Booking created! Total: R" . number_format($totalAmount, 2);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Package — Tripistry</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<?php include "../includes/navbar.php"; ?>
<div class="wrapper">
    <div class="page-content">
        <a class="btn-back" href="package_details.php?id=<?php echo $packageID; ?>">Back to Package</a>
        <h1 class="page-title">Book Package</h1>
        <p class="page-subtitle">CONFIRM YOUR TRAVEL BOOKING</p>

        <div class="glass-card" style="max-width:540px;">
            <div class="view-manufacturer">Package</div>
            <div class="view-model" style="font-size:1.6rem; margin-bottom:.4rem;">
                <?php echo htmlspecialchars($package["title"]); ?>
            </div>
            <p style="color:var(--text-dim); font-size:14px; margin-bottom:1.2rem;">
                Price per traveller: <strong style="color:var(--at-accent);">R<?php echo number_format($package["basePrice"], 2); ?></strong>
            </p>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $success ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="numTravellers">Number of Travellers</label>
                        <input type="number" id="numTravellers" name="numTravellers" min="1" value="1" required>
                    </div>
                    <button type="submit" class="btn-search" style="margin-top:1rem;">Confirm Booking</button>
                </form>
            <?php else: ?>
                <div style="display:flex; gap:1rem; margin-top:1rem;">
                    <a class="btn-primary" href="my_bookings.php">View My Bookings</a>
                    <a class="btn-secondary" href="packages.php">Browse More</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
