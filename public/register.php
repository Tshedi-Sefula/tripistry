<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

if (isLoggedIn()) {
    header("Location: " . (isTraveller() ? "traveller_dashboard.php" : "agency_dashboard.php"));
    exit();
}

$message = "";
$msgType = "error";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $role     = $_POST["role"];

    if ($email === "" || $password === "" || !in_array($role, ["traveller","agency"])) {
        $message = "Please fill in all fields correctly.";
    } else {
        $stmt = $pdo->prepare("SELECT userID FROM User WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $message = "That email is already registered.";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO User (email, passwordHash, role) VALUES (?, ?, ?)");
            $stmt->execute([$email, $passwordHash, $role]);
            $userID = $pdo->lastInsertId();

            if ($role === "traveller") {
                $stmt = $pdo->prepare("
INSERT INTO Traveller 
                    (userID, firstName, lastName, passportNum, phoneNo, nationality, dateOfBirth, preferences)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $userID,
                    $firstName,
                    $lastName,
                    $passportNum, // Saved from your branch!
                    $phoneNo,     // From their branch!
                    $nationality,
                    $dateOfBirth, // They renamed DOB to dateOfBirth
                    $preferences  // From their branch!
                ]);

            } else {
                $agencyName = trim($_POST["agencyName"]);
                $phone = trim($_POST["agencyPhone"]);
                $website = trim($_POST["website"]);
                $address = trim($_POST["address"]);
                $description = trim($_POST["agencyDescription"]);

                $stmt = $pdo->prepare("
                    INSERT INTO TravelAgency
                    (userID, name, phone, website, address, description)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $userID,
                    trim($_POST["firstName"]   ?? ""),
                    trim($_POST["lastName"]    ?? ""),
                    trim($_POST["passportNum"] ?? "") ?: null,
                    trim($_POST["nationality"] ?? ""),
                    $_POST["DOB"] ?: null
                ]);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO TravelAgency (userID, name, website, address, description)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $userID,
                    trim($_POST["agencyName"]        ?? ""),
                    trim($_POST["website"]           ?? ""),
                    trim($_POST["address"]           ?? ""),
                    trim($_POST["agencyDescription"] ?? "")
                ]);
            }

            // ── AUTO-LOGIN after registration ──────────────────────
            $_SESSION["user_id"] = $userID;
            $_SESSION["email"]   = $email;
            $_SESSION["role"]    = $role;

            header("Location: " . ($role === "traveller" ? "traveller_dashboard.php" : "agency_dashboard.php"));
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Tripistry</title>
    <link rel="stylesheet" href="/css/style.css">
    <script>
        function toggleRoleFields() {
            var role = document.getElementById("role").value;
            document.getElementById("travellerFields").style.display = role === "traveller" ? "block" : "none";
            document.getElementById("agencyFields").style.display    = role === "agency"    ? "block" : "none";
        }
        window.onload = toggleRoleFields;
    </script>
</head>
<body>
<video autoplay muted loop playsinline class="bg-video">
    <source src="img/StevenUniverseBarn.mp4" type="video/mp4">
</video>
<div class="bg-overlay"></div>

<div class="wrapper auth-center">
    <div class="auth-card" style="max-width:520px;">
        <h2>Create Account</h2>
        <p>Join the Tripistry adventure!</p>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="your@email.com">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Min 8 characters">
            </div>
            <div class="form-group">
                <label>I am a…</label>
                <select id="role" name="role" onchange="toggleRoleFields()" required>
                    <option value="traveller">Traveller</option>
                    <option value="agency">Travel Agency</option>
                </select>
            </div>

            <div id="travellerFields">
                <div class="form-group"><label>First Name</label><input type="text" name="firstName" placeholder="Finn"></div>
                <div class="form-group"><label>Last Name</label><input type="text" name="lastName" placeholder="The Human"></div>
                <div class="form-group"><label>Passport Number</label><input type="text" name="passportNum" placeholder="Optional"></div>
                <div class="form-group"><label>Nationality</label><input type="text" name="nationality" placeholder="e.g. South African"></div>
                <div class="form-group"><label>Date of Birth</label><input type="date" name="DOB"></div>
            </div>

            <div id="agencyFields" style="display:none;">
                <div class="form-group"><label>Agency Name</label><input type="text" name="agencyName" placeholder="Ooo Travel Co."></div>
                <div class="form-group"><label>Website</label><input type="text" name="website" placeholder="https://yoursite.com"></div>
                <div class="form-group"><label>Address</label><input type="text" name="address" placeholder="123 Candy Kingdom"></div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="agencyDescription" rows="3" placeholder="Tell travellers about your agency…"></textarea>
                </div>
            </div>

            <button type="submit" class="btn-search" style="margin-top:1rem; width:100%;">Register & Go!</button>
        </form>

        <p style="margin-top:1rem; text-align:center; font-size:14px; color:var(--text-dim);">
            Already have an account? <a href="login.php" style="color:var(--at-primary); text-decoration:none;">Login</a>
        </p>
    </div>
</div>
</body>
</html>
<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

if (isLoggedIn()) {
    header("Location: " . (isTraveller() ? "traveller_dashboard.php" : "agency_dashboard.php"));
    exit();
}

$message = "";
$msgType = "error";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $role     = $_POST["role"];

    if ($email === "" || $password === "" || !in_array($role, ["traveller","agency"])) {
        $message = "Please fill in all fields correctly.";
    } else {
        $stmt = $pdo->prepare("SELECT userID FROM User WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $message = "That email is already registered.";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO User (email, passwordHash, role) VALUES (?, ?, ?)");
            $stmt->execute([$email, $passwordHash, $role]);
            $userID = $pdo->lastInsertId();

            if ($role === "traveller") {
                // Safely grab all the traveller variables from the form
                $firstName   = trim($_POST["firstName"] ?? "");
                $lastName    = trim($_POST["lastName"] ?? "");
                $passportNum = trim($_POST["passportNum"] ?? "") ?: null;
                $phoneNo     = trim($_POST["phoneNo"] ?? "") ?: null;
                $nationality = trim($_POST["nationality"] ?? "");
                $dateOfBirth = $_POST["dateOfBirth"] ?: null;
                $preferences = trim($_POST["preferences"] ?? "") ?: null;

                $stmt = $pdo->prepare("
                    INSERT INTO Traveller 
                    (userID, firstName, lastName, passportNum, phoneNo, nationality, dateOfBirth, preferences)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $userID,
                    $firstName,
                    $lastName,
                    $passportNum, 
                    $phoneNo,     
                    $nationality,
                    $dateOfBirth, 
                    $preferences  
                ]);

            } else {
                // Safely grab all the agency variables from the form
                $agencyName  = trim($_POST["agencyName"] ?? "");
                $phone       = trim($_POST["agencyPhone"] ?? "");
                $website     = trim($_POST["website"] ?? "") ?: null;
                $address     = trim($_POST["address"] ?? "") ?: null;
                $description = trim($_POST["agencyDescription"] ?? "") ?: null;

                $stmt = $pdo->prepare("
                    INSERT INTO TravelAgency
                    (userID, name, phone, website, address, description)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $userID,
                    $agencyName,
                    $phone,
                    $website,
                    $address,
                    $description
                ]);
            }

            // ── AUTO-LOGIN after registration ──────────────────────
            $_SESSION["user_id"] = $userID;
            $_SESSION["email"]   = $email;
            $_SESSION["role"]    = $role;

            header("Location: " . ($role === "traveller" ? "traveller_dashboard.php" : "agency_dashboard.php"));
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Tripistry</title>
    <link rel="stylesheet" href="/css/style.css">
    <script>
        function toggleRoleFields() {
            var role = document.getElementById("role").value;
            document.getElementById("travellerFields").style.display = role === "traveller" ? "block" : "none";
            document.getElementById("agencyFields").style.display    = role === "agency"    ? "block" : "none";
        }
        window.onload = toggleRoleFields;
    </script>
</head>
<body>
<video autoplay muted loop playsinline class="bg-video">
    <source src="img/StevenUniverseBarn.mp4" type="video/mp4">
</video>
<div class="bg-overlay"></div>

<div class="wrapper auth-center">
    <div class="auth-card" style="max-width:520px;">
        <h2>Create Account</h2>
        <p>Join the Tripistry adventure!</p>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="your@email.com">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Min 8 characters">
            </div>
            <div class="form-group">
                <label>I am a…</label>
                <select id="role" name="role" onchange="toggleRoleFields()" required>
                    <option value="traveller">Traveller</option>
                    <option value="agency">Travel Agency</option>
                </select>
            </div>

            <div id="travellerFields">
                <div class="form-group"><label>First Name</label><input type="text" name="firstName" placeholder="Finn"></div>
                <div class="form-group"><label>Last Name</label><input type="text" name="lastName" placeholder="The Human"></div>
                <div class="form-group"><label>Passport Number</label><input type="text" name="passportNum" placeholder="Optional"></div>
                <div class="form-group"><label>Phone Number</label><input type="text" name="phoneNo" placeholder="+27..."></div>
                <div class="form-group"><label>Nationality</label><input type="text" name="nationality" placeholder="e.g. South African"></div>
                <div class="form-group"><label>Date of Birth</label><input type="date" name="dateOfBirth"></div>
                <div class="form-group"><label>Travel Preferences</label><input type="text" name="preferences" placeholder="e.g. Beach, Hiking"></div>
            </div>

            <div id="agencyFields" style="display:none;">
                <div class="form-group"><label>Agency Name</label><input type="text" name="agencyName" placeholder="Ooo Travel Co."></div>
                <div class="form-group"><label>Phone Number</label><input type="text" name="agencyPhone" placeholder="+27..."></div>
                <div class="form-group"><label>Website</label><input type="text" name="website" placeholder="https://yoursite.com"></div>
                <div class="form-group"><label>Address</label><input type="text" name="address" placeholder="123 Candy Kingdom"></div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="agencyDescription" rows="3" placeholder="Tell travellers about your agency…"></textarea>
                </div>
            </div>

            <button type="submit" class="btn-search" style="margin-top:1rem; width:100%;">Register & Go!</button>
        </form>

        <p style="margin-top:1rem; text-align:center; font-size:14px; color:var(--text-dim);">
            Already have an account? <a href="login.php" style="color:var(--gold); text-decoration:none;">Login</a>
        </p>
    </div>
</div>
</body>
</html>