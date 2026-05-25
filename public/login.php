<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $ipAddress = $_SERVER["REMOTE_ADDR"] ?? "unknown";

    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS failedAttempts
        FROM LoginAttempt
        WHERE email = ?
          AND ipAddress = ?
          AND success = 0
          AND attemptTime >= (NOW() - INTERVAL 5 MINUTE)
    ");

    $stmt->execute([$email, $ipAddress]);

    $attemptData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($attemptData["failedAttempts"] >= 5) {

        $error = "Too many failed login attempts. Please wait 5 minutes and try again.";

    } else {

        
        $stmt = $pdo->prepare("SELECT * FROM User WHERE email = ?");
        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        
        if ($user && password_verify($password, $user["passwordHash"])) {

           
            $stmt = $pdo->prepare("
                INSERT INTO LoginAttempt (email, ipAddress, success)
                VALUES (?, ?, 1)
            ");

            $stmt->execute([$email, $ipAddress]);

            
            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["userID"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["role"] = $user["role"];

            if ($user["role"] === "traveller") {

                header("Location: traveller_dashboard.php");

            } else {

                header("Location: agency_dashboard.php");

            }

            exit();

        } else {

            
            $stmt = $pdo->prepare("
                INSERT INTO LoginAttempt (email, ipAddress, success)
                VALUES (?, ?, 0)
            ");

            $stmt->execute([$email, $ipAddress]);

            $error = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Tripistry</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<video autoplay muted loop playsinline class="bg-video">
    <source src="../img/StevenUniverseBarn.mp4" type="video/mp4">
</video>
<div class="bg-overlay"></div>

<div class="wrapper auth-center">
    <div class="auth-card">
        <h2>Welcome Back</h2>
        <p>Login to your Tripistry account</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="your@email.com">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn-search">Login</button>
        </form>

        <p style="margin-top:1.2rem; text-align:center; font-size:14px; color:var(--text-dim);">
            No account? <a href="register.php" style="color:var(--gold); text-decoration:none;">Create one</a>
        </p>
    </div>
</div>

</body>
</html>