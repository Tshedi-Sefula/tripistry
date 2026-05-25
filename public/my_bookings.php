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
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings — Tripistry</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include "../includes/navbar.php"; ?>

<div class="wrapper">
    <div class="page-content">
        <h1 class="page-title">My Bookings</h1>
        <p class="page-subtitle">YOUR UPCOMING AND RECENT TRIPS</p>

        <?php if (count($bookings) > 0): ?>
            <div class="bookings-list">
                <?php foreach ($bookings as $booking): ?>
                    <div class="booking-card">
                        <div class="booking-details">
                            <div class="booking-plane-name"><?php echo htmlspecialchars($booking["title"]); ?></div>
                            <div class="detail-row">
                                <span><strong>Booking ID</strong> #<?php echo htmlspecialchars($booking["bookingID"]); ?></span>
                                <span><strong>Date</strong> <?php echo htmlspecialchars($booking["bookingDate"]); ?></span>
                                <span><strong>Travellers</strong> <?php echo htmlspecialchars($booking["numTravellers"]); ?></span>
                            </div>
                            <div class="detail-row">
                                <span><strong>Total</strong> R<?php echo number_format($booking["totalAmount"], 2); ?></span>
                                <span><strong>Status</strong>
                                    <span class="badge badge-<?php echo $booking['status']; ?>">
                                        <?php echo htmlspecialchars(ucfirst($booking["status"])); ?>
                                    </span>
                                </span>
                                <span><strong>Payment</strong>
                                    <span class="badge badge-<?php echo $booking['paymentStatus']; ?>">
                                        <?php echo htmlspecialchars(ucfirst($booking["paymentStatus"])); ?>
                                    </span>
                                </span>
                            </div>
                        </div>
                        <a class="btn-cancel"
                           href="cancel_booking.php?id=<?php echo htmlspecialchars($booking["bookingID"]); ?>"
                           onclick="return confirm('Are you sure you want to cancel this booking?');">
                            Cancel Booking
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="glass-card" style="text-align:center; padding:3rem;">
                <p style="color:var(--text-dim); font-size:16px; margin-bottom:1.5rem;">You have no bookings yet.</p>
                <a class="btn" href="packages.php">Browse Packages</a>
            </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>