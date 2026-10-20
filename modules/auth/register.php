<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";

// Prevent logged-in students from accessing registration again
if (isset($_SESSION["user_id"])) {
    header("Location: ../../public/dashboard.php");
    exit;
}

// Generate CSRF token if empty
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";
$messageType = "error";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // CSRF verification
    $submittedToken = $_POST["csrf_token"] ?? "";
    if (!hash_equals($_SESSION["csrf_token"], $submittedToken)) {
        $message = "Invalid security session. Please refresh and try again.";
    } else {

        $name = trim($_POST["name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";

        if ($name === "" || $email === "" || $password === "") {
            $message = "All registration fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Please provide a valid educational or personal email address.";
        } elseif (strlen($password) < 8) {
            $message = "Password must be at least 8 characters long for account security.";
        } else {

            // Check if email already registered
            $checkSql = "SELECT id FROM users WHERE email = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                $message = "An account with this email address already exists.";
                $checkStmt->close();
            } else {
                $checkStmt->close();

                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $sql = "INSERT INTO users (name, email, password, role)
                        VALUES (?, ?, ?, 'student')";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $name, $email, $hashedPassword);

                if ($stmt->execute()) {
                    $newUserId = $stmt->insert_id;
                    $stmt->close();

                    // Initialize empty student profile
                    $profileSql = "INSERT INTO student_profiles (user_id, department, semester, bio)
                                   VALUES (?, 'General Studies', 1, 'Student at Campus Nexus.')
                                   ON DUPLICATE KEY UPDATE user_id = user_id";
                    $profStmt = $conn->prepare($profileSql);
                    $profStmt->bind_param("i", $newUserId);
                    $profStmt->execute();
                    $profStmt->close();

                    // Set initial user points entry
                    $pointSql = "INSERT INTO user_points (user_id, points) VALUES (?, 0)
                                 ON DUPLICATE KEY UPDATE points = points";
                    $pointStmt = $conn->prepare($pointSql);
                    $pointStmt->bind_param("i", $newUserId);
                    $pointStmt->execute();
                    $pointStmt->close();

                    // Automatically log in newly registered student
                    session_regenerate_id(true);
                    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
                    $_SESSION["user_id"] = (int) $newUserId;
                    $_SESSION["user_name"] = $name;
                    $_SESSION["user_email"] = $email;
                    $_SESSION["user_role"] = "student";
                    $_SESSION["flash_success"] = "Welcome to Campus Nexus! Your collaboration account is active.";

                    header("Location: ../../public/dashboard.php");
                    exit;

                } else {
                    $message = "Registration could not be completed. Please try again.";
                    $stmt->close();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Campus Nexus</title>
    <meta name="description" content="Register for a Campus Nexus student collaboration account.">
    <!-- Official Favicon -->
    <link rel="icon" type="image/png" href="../../images/favicon.png">
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body class="auth-page bg-grid-peach" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px;">

    <main class="auth-card card-interactive animate-fade-up" style="width: 100%; max-width: 480px; background: white; padding: 36px 32px; border-radius: var(--radius-xl); box-shadow: var(--shadow-lg);">

        <div style="text-align: center; margin-bottom: 24px;">
            <a class="brand" href="../../public/index.php" style="justify-content: center; font-size: 1.5rem; margin-bottom: 12px;">
                <div class="brand-logo-wrap" style="width: 40px; height: 40px;">
                    <img src="../../images/cn-logo.png" alt="Campus Nexus Emblem" class="brand-logo-img">
                </div>
                <div class="brand-info" style="text-align: left;">
                    <span class="brand-text">Campus <span class="brand-accent">Nexus</span></span>
                    <span class="brand-subtext">Innovation & Collaboration Hub</span>
                </div>
            </a>

            <h1 class="font-display" style="font-size: 1.8rem; margin: 4px 0 6px; color: var(--text-primary);">Join Campus Nexus</h1>
            <p class="auth-subtitle" style="color: var(--muted); margin: 0; font-size: 0.95rem;">
                Connect with peers, find project teams, and exchange skills.
            </p>
        </div>

        <?php if ($message !== ""): ?>
            <div class="alert alert-error" role="alert" style="margin-bottom: 20px;">
                <span class="alert-icon" aria-hidden="true">✕</span>
                <div class="alert-content"><?= htmlspecialchars($message) ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">

            <div style="margin-bottom: 16px;">
                <label for="name" style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px;">Full Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?= htmlspecialchars($_POST["name"] ?? "") ?>"
                    autocomplete="name"
                    placeholder="e.g. Alex Johnson"
                    required
                >
            </div>

            <div style="margin-bottom: 16px;">
                <label for="email" style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px;">University or Personal Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($_POST["email"] ?? "") ?>"
                    autocomplete="email"
                    placeholder="alex@university.edu"
                    required
                >
            </div>

            <div style="margin-bottom: 22px;">
                <label for="password" style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px;">Create Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="new-password"
                    placeholder="Minimum 8 characters"
                    required
                >
            </div>

            <button type="submit" class="btn btn-glow" style="width: 100%; padding: 12px; font-size: 1rem;">
                Create Your Account
            </button>
        </form>

        <p class="auth-footer" style="text-align: center; margin-top: 24px; color: var(--muted); font-size: 0.92rem;">
            Already have an account?
            <a href="login.php" style="font-weight: 700; color: var(--primary);">Sign in here</a>
        </p>

    </main>

</body>
</html>