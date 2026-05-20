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

$packageID = $_GET["id"];
$userID = $_SESSION["user_id"];

$stmt = $pdo->prepare("SELECT travellerID FROM traveller WHERE userID = ?");
$stmt->execute([$userID]);
$traveller = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$traveller) {
    die("Traveller profile not found.");
}

$travellerID = $traveller["travellerID"];

$stmt = $pdo->prepare("
    SELECT packageID, title, totalPrice
    FROM travelPackage
    WHERE packageID = ? AND status = 'active'
");
$stmt->execute([$packageID]);
$package = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$package) {
    die("Package not found.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $numTravellers = intval($_POST["numTravellers"]);

    if ($numTravellers < 1) {
        $message = "Number of travellers must be at least 1.";
    } else {
        $totalAmount = $package["totalPrice"] * $numTravellers;

        $stmt = $pdo->prepare("
            INSERT INTO booking 
            (travellerID, packageID, numTravellers, totalAmount, status, paymentStatus)
            VALUES (?, ?, ?, ?, 'pending', 'unpaid')
        ");

        $stmt->execute([
            $travellerID,
            $packageID,
            $numTravellers,
            $totalAmount
        ]);

        $message = "Booking created successfully.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Package - Tripistry</title>
</head>
<body>
    
<?php include "../includes/navbar.php"; ?>

<h1>Book Package</h1>

<h2><?php echo htmlspecialchars($package["title"]); ?></h2>

<p>Price per traveller: R<?php echo number_format($package["totalPrice"], 2); ?></p>

<?php if ($message): ?>
    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>

<form method="POST">
    <label>Number of Travellers</label><br>
    <input type="number" name="numTravellers" min="1" value="1" required><br><br>

    <button type="submit">Confirm Booking</button>
</form>

<br>

<a href="package_details.php?id=<?php echo $packageID; ?>">Back to Package</a>

</body>
</html>