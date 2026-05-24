<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

if (isLoggedIn()) {
    header("Location: " . (isTraveller() ? "traveller_dashboard.php" : "agency_dashboard.php"));
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $stmt = $pdo->prepare("SELECT * FROM User WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["passwordHash"])) {
        $_SESSION["user_id"] = $user["userID"];
        $_SESSION["email"]   = $user["email"];
        $_SESSION["role"]    = $user["role"];

        header("Location: " . ($user["role"] === "traveller" ? "traveller_dashboard.php" : "agency_dashboard.php"));
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Tripistry</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<video autoplay muted loop playsinline class="bg-video">
    <source src="img/StevenUniverseBarn.mp4" type="video/mp4">
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
            No account? <a href="register.php" style="color:var(--gold); text-decoration:none;">Register here</a>
        </p>
    </div>
</div>

</body>
</html>
