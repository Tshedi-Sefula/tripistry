<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();
if (!isTraveller()) { die("Access denied."); }

$userID = $_SESSION["user_id"];

$stmt = $pdo->prepare("
    SELECT
        b.bookingID,
        b.bookingDate,
        b.numTravellers,
        b.totalAmount,
        b.status,
        b.paymentStatus,
        p.title,
        p.packageID
    FROM Booking b
    JOIN TravelPackage p ON b.packageID = p.packageID
    WHERE b.travellerUserID = ?
    ORDER BY b.bookingDate DESC
");
$stmt->execute([$userID]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings — Tripistry</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<?php include "../includes/navbar.php"; ?>
<div class="wrapper">
    <div class="page-content">
        <h1 class="page-title">My Bookings</h1>
        <p class="page-subtitle">YOUR TRAVEL ADVENTURES</p>

        <?php if (count($bookings) > 0): ?>
            <div class="bookings-list">
                <?php foreach ($bookings as $b): ?>
                    <div class="booking-card">
                        <div class="booking-details">
                            <div class="booking-plane-name"><?php echo htmlspecialchars($b["title"]); ?></div>
                            <div class="detail-row">
                                <span>Booking ID: <strong>#<?php echo $b["bookingID"]; ?></strong></span>
                                <span>Date: <strong><?php echo $b["bookingDate"]; ?></strong></span>
                                <span>Travellers: <strong><?php echo $b["numTravellers"]; ?></strong></span>
                            </div>
                            <div class="detail-row">
                                <span>Total: <strong style="color:var(--at-accent);">R<?php echo number_format($b["totalAmount"], 2); ?></strong></span>
                                <span class="badge badge-<?php echo $b['status']; ?>"><?php echo ucfirst($b["status"]); ?></span>
                                <span class="badge badge-<?php echo $b['paymentStatus']; ?>"><?php echo ucfirst($b["paymentStatus"]); ?></span>
                            </div>
                        </div>
                        <a class="btn-cancel"
                           href="cancel_booking.php?id=<?php echo $b["bookingID"]; ?>"
                           onclick="return confirm('Cancel this booking?');">
                            Cancel
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="glass-card" style="text-align:center; padding:3rem;">
                <p style="color:var(--text-dim); font-size:16px; margin-bottom:1.5rem;">No bookings yet.</p>
                <a class="btn-primary" href="packages.php">Browse Packages</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
