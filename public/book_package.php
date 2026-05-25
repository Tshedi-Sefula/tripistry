<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();

if (!isTraveller()) {
    die("Only travellers can book packages.");
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid package ID.");
}

$packageID = intval($_GET["id"]);
$sessionUserID = $_SESSION["userID"] ?? $_SESSION["user_id"] ?? null;

$stmt = $pdo->prepare("
    SELECT userID
    FROM Traveller
    WHERE userID = ?
");

$stmt->execute([$sessionUserID]);
$traveller = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$traveller) {
    $stmt = $pdo->query("
        SELECT userID
        FROM Traveller
        LIMIT 1
    ");

    $traveller = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$traveller) {
    die("No traveller profile exists in the database.");
}

$travellerUserID = $traveller["userID"];

$stmt = $pdo->prepare("
    SELECT
        packageID,
        title,
        basePrice,
        durationDays
    FROM TravelPackage
    WHERE packageID = ?
");

$stmt->execute([$packageID]);
$package = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$package) {
    die("Package not found.");
}

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $numTravellers = intval($_POST["numTravellers"]);

    if ($numTravellers < 1) {
        $message = "Number of travellers must be at least 1.";
    } else {
        $totalAmount = $package["basePrice"] * $numTravellers;

        $stmt = $pdo->prepare("
            INSERT INTO Booking (
                travellerUserID,
                packageID,
                bookingDate,
                numTravellers,
                totalAmount,
                status,
                paymentStatus
            )
            VALUES (?, ?, CURDATE(), ?, ?, 'confirmed', 'paid')
        ");

        $stmt->execute([
            $travellerUserID,
            $packageID,
            $numTravellers,
            $totalAmount
        ]);

        $message = "Booking created successfully.";
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
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include "../includes/navbar.php"; ?>

<div class="wrapper">
    <div class="page-content">
        <a class="btn-back" href="package_details.php?id=<?php echo htmlspecialchars($packageID); ?>">Back to Package</a>

        <h1 class="page-title">Book Package</h1>
        <p class="page-subtitle">CONFIRM YOUR TRAVEL BOOKING</p>

        <div class="glass-card" style="max-width:540px;">
            <div class="view-manufacturer">Package</div>

            <div class="view-model" style="font-size:1.6rem; margin-bottom:.4rem;">
                <?php echo htmlspecialchars($package["title"]); ?>
            </div>

            <p style="color:var(--text-dim); font-size:14px; margin-bottom:.4rem;">
                <strong>Duration:</strong>
                <?php echo htmlspecialchars($package["durationDays"]); ?> days
            </p>

            <p style="color:var(--text-dim); font-size:14px; margin-bottom:1.2rem;">
                Price per traveller:
                <strong style="color:var(--gold);">
                    R<?php echo number_format($package["basePrice"], 2); ?>
                </strong>
            </p>

            <?php if ($message): ?>
                <div class="<?php echo $success ? 'confirm-msg' : 'alert alert-error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
                <form method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="numTravellers">Number of Travellers</label>
                        <input type="number" id="numTravellers" name="numTravellers" min="1" required>
                    </div>

                    <button type="submit" class="btn-search">Confirm Booking</button>
                </form>
            <?php else: ?>
                <div class="btn-row">
                    <a class="btn" href="my_bookings.php">View My Bookings</a>
                    <a class="btn-secondary" href="packages.php">Browse More</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
