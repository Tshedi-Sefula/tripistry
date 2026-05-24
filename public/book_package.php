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
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Package - Tripistry</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 30px;
        }

        .container {
            background: white;
            max-width: 700px;
            margin: auto;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        input {
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 20px;
            width: 100%;
        }

        button {
            padding: 10px 15px;
            background: #007bff;
            border: none;
            color: white;
            border-radius: 5px;
        }
    </style>
</head>

<body>

<?php include "../includes/navbar.php"; ?>

<div class="container">

    <h1>Book Package</h1>

    <h2><?php echo htmlspecialchars($package["title"]); ?></h2>

    <p>
        Price per traveller:
        <strong>R<?php echo number_format($package["basePrice"], 2); ?></strong>
    </p>

    <p>
        Duration:
        <?php echo htmlspecialchars($package["durationDays"]); ?> days
    </p>

    <?php if ($message): ?>
        <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <form method="POST">

        <label>Number of Travellers</label><br>

        <input
            type="number"
            name="numTravellers"
            min="1"
            required
        >

        <button type="submit">
            Confirm Booking
        </button>

    </form>

</div>

</body>
</html>