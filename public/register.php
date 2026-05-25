<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email      = trim($_POST["email"] ?? '');
    $password   = trim($_POST["password"] ?? '');
    $role       = $_POST["role"] ?? '';

    if ($email === "" || $password === "" || ($role !== "traveller" && $role !== "agency")) {
        $message = "Please fill in all fields correctly.";
    } else {
        $stmt = $pdo->prepare("SELECT userID FROM User WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $message = "Email already exists.";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO User (email, passwordHash, role) VALUES (?, ?, ?)");
            $stmt->execute([$email, $passwordHash, $role]);

            $userID = $pdo->lastInsertId();

            if ($role === "traveller") {
                $firstName   = trim($_POST["firstName"] ?? '');
                $lastName    = trim($_POST["lastName"] ?? '');
                $passportNum = trim($_POST["passportNum"] ?? '');
                $nationality = trim($_POST["nationality"] ?? '');
                $dob         = $_POST["dateOfBirth"] ?? null;

                $stmt = $pdo->prepare("
                    INSERT INTO Traveller (userID, firstName, lastName, passportNum, nationality, DOB)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$userID, $firstName, $lastName, $passportNum ?: null, $nationality, $dob]);

            } else { // Agency
                $agencyName  = trim($_POST["agencyName"] ?? '');
                $website     = trim($_POST["website"] ?? '');
                $address     = trim($_POST["address"] ?? '');
                $description = trim($_POST["agencyDescription"] ?? '');

                $stmt = $pdo->prepare("
                    INSERT INTO TravelAgency (userID, name, description, website, address)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$userID, $agencyName, $description, $website, $address]);
            }

            $message = "Registration successful. You can now log in.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Tripistry</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 30px; }
        .container { background: white; max-width: 750px; margin: auto; padding: 25px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        input, textarea, select { width: 100%; padding: 10px; margin-top: 6px; margin-bottom: 15px; }
        textarea { height: 100px; }
        button, .btn { padding: 12px 20px; background: #007bff; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .section { display: none; }
    </style>

    <script>
        function toggleRoleFields() {
            var role = document.getElementById("role").value;
            
            // Toggle sections
            document.getElementById("travellerFields").style.display = (role === "traveller") ? "block" : "none";
            document.getElementById("agencyFields").style.display = (role === "agency") ? "block" : "none";

            // Toggle required attributes dynamically
            toggleRequiredFields(role);
        }

        function toggleRequiredFields(role) {
            // Traveller fields
            const travellerInputs = document.querySelectorAll('#travellerFields input, #travellerFields textarea');
            travellerInputs.forEach(input => {
                input.required = (role === "traveller");
            });

            // Agency fields
            const agencyInputs = document.querySelectorAll('#agencyFields input, #agencyFields textarea');
            agencyInputs.forEach(input => {
                input.required = (role === "agency");
            });
        }
    </script>
</head>

<body onload="toggleRoleFields()">

<div class="container">
    <h1>Register</h1>

    <?php if ($message): ?>
        <p><strong><?= htmlspecialchars($message) ?></strong></p>
    <?php endif; ?>

    <form method="POST" id="registerForm">
        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Role</label>
        <select name="role" id="role" onchange="toggleRoleFields()" required>
            <option value="traveller">Traveller</option>
            <option value="agency">Travel Agency</option>
        </select>

        <!-- Traveller Fields -->
        <div id="travellerFields" class="section">
            <h2>Traveller Details</h2>
            <label>First Name</label>
            <input type="text" name="firstName">

            <label>Last Name</label>
            <input type="text" name="lastName">

            <label>Passport Number (Optional)</label>
            <input type="text" name="passportNum">

            <label>Nationality</label>
            <input type="text" name="nationality">

            <label>Date of Birth</label>
            <input type="date" name="dateOfBirth">
        </div>

        <!-- Agency Fields -->
        <div id="agencyFields" class="section">
            <h2>Agency Details</h2>
            <label>Agency Name</label>
            <input type="text" name="agencyName">

            <label>Website</label>
            <input type="text" name="website">

            <label>Address</label>
            <input type="text" name="address">

            <label>Description</label>
            <textarea name="agencyDescription"></textarea>
        </div>

        <button type="submit">Register</button>
        <a class="btn" href="login.php" style="background:#555; margin-left:10px;">Back to Login</a>
    </form>
</div>

</body>
</html>