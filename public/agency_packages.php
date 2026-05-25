<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();

if (!isAgency()) {
    die("Access denied.");
}

$userID = $_SESSION["user_id"];

$stmt = $pdo->prepare("SELECT * FROM TravelAgency WHERE userID = ?");
$stmt->execute([$userID]);
$agency = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$agency) {
    die("Agency profile not found.");
}

$stmt = $pdo->prepare("
    SELECT 
        packageID,
        title,
        description,
        basePrice,
        itinerary,
        durationDays,
        status,
        dateCreated
    FROM TravelPackage 
    WHERE agencyUserID = ? 
    ORDER BY dateCreated DESC
");
$stmt->execute([$userID]);
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Agency Packages</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 25px;
        }

        .package-card {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .btn {
            display: inline-block;
            padding: 8px 12px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 8px;
        }

        .delete {
            background: #dc3545;
        }

        .back {
            background: #555;
        }
    </style>
</head>
<body>

<?php include "../includes/navbar.php"; ?>

<h1>My Agency Packages</h1>

<a class="btn" href="create_package.php">Create New Package</a>
<a class="btn back" href="agency_dashboard.php">Back to Dashboard</a>

<br><br>

<?php if (count($packages) > 0): ?>

    <?php foreach ($packages as $package): ?>

        <div class="package-card">
            <h2><?php echo htmlspecialchars($package["title"]); ?></h2>

            <p><?php echo htmlspecialchars($package["description"]); ?></p>

            <p><strong>Price:</strong> R<?php echo number_format($package["basePrice"], 2); ?></p>
            <p><strong>Duration:</strong> <?php echo $package["durationDays"]; ?> days</p>
            <p><strong>Type:</strong> <?php echo ($package["itinerary"]); ?></p>
            <p><strong>Status:</strong> <?php echo ucfirst($package["status"]); ?></p>

            <a class="btn" href="edit_package.php?id=<?php echo $package["packageID"]; ?>">
                Edit
            </a>

            <a class="btn delete"
                href="delete_package.php?id=<?php echo $package["packageID"]; ?>"
                onclick="return confirm('Are you sure you want to delete this package? This action cannot be undone.');">
                Delete
            </a>
        </div>

    <?php endforeach; ?>

<?php else: ?>

    <p>You have not created any packages yet.</p>

<?php endif; ?>

</body>
</html>