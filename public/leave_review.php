<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/sentiment.php";

requireLogin();

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid package ID.");
}

$packageID = intval($_GET["id"]);

$stmt = $pdo->prepare("
    SELECT packageID, title
    FROM TravelPackage
    WHERE packageID = ?
");
$stmt->execute([$packageID]);
$package = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$package) {
    die("Package not found.");
}

$stmt = $pdo->query("SELECT userID FROM Traveller LIMIT 1");
$traveller = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$traveller) {
    die("No traveller profile exists.");
}

$travellerUserID = $traveller["userID"];
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $rating = floatval($_POST["rating"]);
    $comment = trim($_POST["comment"]);
    $sentiment = analyseSentiment($comment);

    if ($rating < 1 || $rating > 5) {
        $message = "Rating must be between 1 and 5.";
    } elseif ($comment === "") {
        $message = "Comment cannot be empty.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO Review (
                travellerUserID,
                rating,
                comment,
                targetType,
                packageID,
                agencyUserID,
                sentiment
            )
            VALUES (?, ?, ?, 'package', ?, NULL, ?)
        ");

        $stmt->execute([
            $travellerUserID,
            $rating,
            $comment,
            $packageID,
            $sentiment
        ]);

        $message = "Review submitted successfully. Sentiment detected: " . $sentiment;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Leave Review - Tripistry</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        .page-wrapper {
            max-width: 750px;
            margin: 45px auto;
            padding: 0 20px;
        }

        .review-card {
            background: white;
            padding: 35px;
            border-radius: 14px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.12);
        }

        h1 {
            margin-top: 0;
            font-size: 34px;
            color: #222;
        }

        h2 {
            color: #333;
            margin-bottom: 25px;
            font-size: 24px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 7px;
            color: #333;
        }

        input,
        textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
        }

        textarea {
            min-height: 130px;
            resize: vertical;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 4px rgba(0,123,255,0.35);
        }

        .message {
            background: #eaf7ea;
            border-left: 5px solid #28a745;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-weight: bold;
        }

        .actions {
            display: flex;
            gap: 12px;
        }

        button,
        .btn {
            padding: 12px 18px;
            border: none;
            text-decoration: none;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
        }

        button {
            background: #007bff;
            color: white;
        }

        button:hover {
            background: #0056b3;
        }

        .btn {
            background: #555;
            color: white;
        }

        .btn:hover {
            background: #333;
        }
    </style>
</head>

<body>

<?php include "../includes/navbar.php"; ?>

<div class="page-wrapper">
    <div class="review-card">

        <h1>Leave Review</h1>

        <h2><?php echo htmlspecialchars($package["title"]); ?></h2>

        <?php if ($message): ?>
            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <label>Rating (1 - 5)</label>
            <input
                type="number"
                name="rating"
                step="0.1"
                min="1"
                max="5"
                required
            >

            <label>Comment</label>
            <textarea name="comment" required></textarea>

            <div class="actions">
                <button type="submit">Submit Review</button>
                <a class="btn" href="package_details.php?id=<?php echo htmlspecialchars($packageID); ?>">
                    Back
                </a>
            </div>

        </form>

    </div>
</div>

</body>
</html>