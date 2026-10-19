<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";

// Prevent logged-in students from accessing login page again
if (isset($_SESSION["user_id"])) {
    header("Location: ../../public/dashboard.php");
    exit;
}

// Generate CSRF token if not set
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

        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";

        if ($email === "" || $password === "") {
            $message = "Both email and password are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Invalid email or password.";
        } else {

            $sql = "SELECT id, name, email, password, role
                    FROM users
                    WHERE email = ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user["password"])) {
                    session_regenerate_id(true);

                    // Refresh CSRF token on login
                    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

                    $_SESSION["user_id"] = (int) $user["id"];
                    $_SESSION["user_name"] = $user["name"];
                    $_SESSION["user_email"] = $user["email"];
                    $_SESSION["user_role"] = $user["role"];

                    header("Location: ../../public/dashboard.php");
                    exit;
                } else {
                    $message = "Invalid email or password.";
                }
            } else {
                $message = "Invalid email or password.";
            }

            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Campus Nexus</title>
    <meta name="description" content="Sign in to your Campus Nexus student account.">
    <!-- Official Favicon -->
    <link rel="icon" type="image/png" href="../../images/favicon.png">
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body class="auth-page bg-grid-peach" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px;">

    <main class="auth-card card-interactive animate-fade-up" style="width: 100%; max-width: 440px; background: white; padding: 36px 32px; border-radius: var(--radius-xl); box-shadow: var(--shadow-lg);">

        <div style="text-align: center; margin-bottom: 24px;">
            <a class="brand" href="../../public/index.php" style="justify-content: center; font-size: 1.5rem; margin-bottom: 12px;">
                <div class="brand-logo-wrap" style="width: 40px; height: 40px;">
                    <img src="../../images/cn-logo.png" alt="Campus Nexus Emblem" class="brand-logo-img">
                </div>
                <div class="brand-info" style="text-align: left;">
                    <span class="brand-text">Campus <span class="brand-accent">Nexus</span></span>
                    <span class="brand-subtext">ADIT · CVM University</span>
                </div>
            </a>

            <!-- Institutional Affiliation Badge -->
            <div style="display: inline-flex; align-items: center; gap: 8px; padding: 4px 12px; background: var(--surface-muted); border: 1px solid var(--border); border-radius: var(--radius-full); margin: 6px 0 14px;">
                <img src="../../images/adit-logo.png" alt="ADIT" style="height: 18px; width: auto;">
                <span style="font-size: 0.72rem; font-weight: 700; color: var(--muted);">ADIT & CVM UNIVERSITY</span>
                <img src="../../images/cvm-university-logo.png" alt="CVM" style="height: 18px; width: auto;">
            </div>

            <h1 class="font-display" style="font-size: 1.8rem; margin: 4px 0 6px; color: var(--text-primary);">Welcome Back</h1>
            <p class="auth-subtitle" style="color: var(--muted); margin: 0; font-size: 0.95rem;">
                Sign in to collaborate, share skills, and build projects.
            </p>
        </div>

        <?php if ($message !== ""): ?>
            <div class="alert alert-error" role="alert" style="margin-bottom: 20px;">
                <span class="alert-icon" aria-hidden="true">✕</span>
                <div class="alert-content"><?= htmlspecialchars($message) ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">

            <div style="margin-bottom: 18px;">
                <label for="email" style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px;">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($_POST["email"] ?? "") ?>"
                    autocomplete="email"
                    placeholder="student@university.edu"
                    required
                >
            </div>

            <div style="margin-bottom: 22px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label for="password" style="font-weight: 600; font-size: 0.9rem; margin: 0;">Password</label>
                </div>
                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="current-password"
                    placeholder="Enter your password"
                    required
                >
            </div>

            <button type="submit" class="btn btn-glow" style="width: 100%; padding: 12px; font-size: 1rem;">
                Sign In
            </button>
        </form>

        <p class="auth-footer" style="text-align: center; margin-top: 24px; color: var(--muted); font-size: 0.92rem;">
            Don't have an account yet?
            <a href="register.php" style="font-weight: 700; color: var(--primary);">Join Campus Nexus</a>
        </p>

    </main>

</body>
</html>
