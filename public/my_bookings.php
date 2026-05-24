<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();

if (!isTraveller()) {
    die("Access denied.");
}

$travellerUserID = $_SESSION["userID"] ?? $_SESSION["user_id"] ?? null;

if ($travellerUserID === null) {
    die("User session not found.");
}

$stmt = $pdo->prepare("
    SELECT
        b.bookingID,
        b.bookingDate,
        b.numTravellers,
        b.totalAmount,
        b.status,
        b.paymentStatus,
        p.title
    FROM Booking b
    JOIN TravelPackage p
        ON b.packageID = p.packageID
    WHERE b.travellerUserID = ?
    ORDER BY b.bookingDate DESC
");

$stmt->execute([$travellerUserID]);

$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Bookings - Tripistry</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }

        .booking-card {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .status {
            font-weight: bold;
        }
    </style>
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
                <?php echo htmlspecialchars($booking["bookingID"]); ?>
            </p>

            <p>
                <strong>Booking Date:</strong>
                <?php echo htmlspecialchars($booking["bookingDate"]); ?>
            </p>

            <p>
                <strong>Travellers:</strong>
                <?php echo htmlspecialchars($booking["numTravellers"]); ?>
            </p>

            <p>
                <strong>Total Amount:</strong>
                R<?php echo number_format($booking["totalAmount"], 2); ?>
            </p>

            <p class="status">
                Booking Status:
                <?php echo htmlspecialchars(ucfirst($booking["status"])); ?>
            </p>

            <p class="status">
                Payment Status:
                <?php echo htmlspecialchars(ucfirst($booking["paymentStatus"])); ?>
            </p>

            <a
                href="cancel_booking.php?id=<?php echo htmlspecialchars($booking["bookingID"]); ?>"
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