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

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-card {
            background: white;
            width: 400px;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.15);
        }

        h1 {
            margin-top: 0;
            color: #007bff;
            text-align: center;
        }

        .subtitle {
            text-align: center;
            color: #555;
            margin-bottom: 25px;
        }

        label {
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 11px;
            margin-top: 6px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        .error {
            background: #ffe1e1;
            color: #b00020;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            text-align: center;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
        }

        .register-link a {
            color: #007bff;
            font-weight: bold;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
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