<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireLogin();

if (!isTraveller()) {
    die("Access denied.");
}

$stmt = $pdo->prepare("
    SELECT
        packageID,
        title,
        description,
        basePrice,
        durationDays,
        status
    FROM TravelPackage
    WHERE status = 'active'
    ORDER BY basePrice ASC
    LIMIT 6
");
$stmt->execute();
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recommended Packages - Tripistry</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        .page-wrapper {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .hero {
            background: white;
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }

        .hero h1 {
            margin: 0 0 10px;
            font-size: 34px;
            color: #222;
        }

        .hero p {
            margin: 0;
            color: #555;
            font-size: 16px;
        }

        .package-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 22px;
        }

        .package-card {
            background: white;
            padding: 24px;
            border-radius: 14px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.1);
            border: 1px solid #eee;
        }

        .package-card h2 {
            margin-top: 0;
            color: #222;
            font-size: 23px;
        }

        .package-card p {
            color: #444;
            line-height: 1.5;
        }

        .price {
            color: #198754;
            font-size: 20px;
            font-weight: bold;
            margin-top: 15px;
        }

        .meta {
            color: #666;
            font-size: 14px;
        }

        .btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 15px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 7px;
        }

        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>

<body>

<?php include "../includes/navbar.php"; ?>

<div class="page-wrapper">

    <div class="hero">
        <h1>Recommended Packages</h1>
        <p>
            These packages are recommended based on affordability and availability.
        </p>
    </div>

    <?php if (count($packages) > 0): ?>

        <div class="package-grid">

            <?php foreach ($packages as $package): ?>

                <div class="package-card">

                    <h2><?php echo htmlspecialchars($package["title"]); ?></h2>

                    <p>
                        <?php echo htmlspecialchars($package["description"]); ?>
                    </p>

                    <p class="price">
                        R<?php echo number_format($package["basePrice"], 2); ?>
                    </p>

                    <p class="meta">
                        Duration: <?php echo htmlspecialchars($package["durationDays"]); ?> days
                    </p>

                    <a class="btn" href="package_details.php?id=<?php echo htmlspecialchars($package["packageID"]); ?>">
                        View Details
                    </a>

                </div>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <p>No recommendations available.</p>

    <?php endif; ?>

</div>

</body>
</html>