<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->execute([$email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["passwordHash"])) {
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
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Tripistry</title>
    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

<div class="login-wrapper">

    <div class="login-card">

        <h1>Tripistry</h1>

        <p class="subtitle">
            Login to your travel account
        </p>

        <?php if ($error): ?>
            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>

        </form>

        <div class="register-link">
            Don’t have an account?
            <a href="register.php">Create one</a>
        </div>

    </div>

</div>

</body>
</html>