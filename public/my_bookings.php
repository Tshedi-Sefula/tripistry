<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();

if (!isTraveller()) {
    die("Access denied.");
}

$userID = $_SESSION["user_id"];

$stmt = $pdo->prepare("
    SELECT travellerID
    FROM traveller
    WHERE userID = ?
");

$stmt->execute([$userID]);

$traveller = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$traveller) {
    die("Traveller profile not found.");
}

$travellerID = $traveller["travellerID"];

$stmt = $pdo->prepare("
    SELECT
        b.bookingID,
        b.bookingDate,
        b.numTravellers,
        b.totalAmount,
        b.status,
        b.paymentStatus,
        p.title
    FROM booking b
    JOIN travelPackage p
        ON b.packageID = p.packageID
    WHERE b.travellerID = ?
    ORDER BY b.bookingDate DESC
");

$stmt->execute([$travellerID]);

$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Bookings - Tripistry</title>
    <link rel="stylesheet" href="/css/style.css">

</head>
<body>

<?php include "../includes/navbar.php"; ?>

<h1>My Bookings</h1>

<?php if (count($bookings) > 0): ?>

    <?php foreach ($bookings as $booking): ?>

        <div class="booking-card">

            <h2>
                <?php echo htmlspecialchars($booking["title"]); ?>
            </h2>

            <p>
                <strong>Booking ID:</strong>
                <?php echo $booking["bookingID"]; ?>
            </p>

            <p>
                <strong>Booking Date:</strong>
                <?php echo $booking["bookingDate"]; ?>
            </p>

            <p>
                <strong>Travellers:</strong>
                <?php echo $booking["numTravellers"]; ?>
            </p>

            <p>
                <strong>Total Amount:</strong>
                R<?php echo number_format($booking["totalAmount"], 2); ?>
            </p>

            <p class="status">
                Booking Status:
                <?php echo ucfirst($booking["status"]); ?>
            </p>

            <p class="status">
                Payment Status:
                <?php echo ucfirst($booking["paymentStatus"]); ?>
            </p>

            <a
                href="cancel_booking.php?id=<?php echo $booking["bookingID"]; ?>"
                onclick="return confirm('Are you sure you want to cancel this booking?');"
                style="
                    display:inline-block;
                    margin-top:10px;
                    padding:8px 12px;
                    background:#dc3545;
                    color:white;
                    text-decoration:none;
                    border-radius:5px;
                "
                >
                Cancel Booking
            </a>

        </div>

    <?php endforeach; ?>

<?php else: ?>

    <p>You have no bookings yet.</p>

<?php endif; ?>

</body>
</html>