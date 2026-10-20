<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";
require_once "../../includes/auth_logger.php";

// Redirect already authenticated students to dashboard
if (isset($_SESSION["user_id"])) {
    header("Location: ../../public/dashboard.php");
    exit;
}

// Ensure secure CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";
$messageType = "error";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // 1. Verify CSRF Token
    $submittedToken = $_POST["csrf_token"] ?? "";
    if (!hash_equals($_SESSION["csrf_token"], $submittedToken)) {
        $message = "Institutional session expired or invalid. Please refresh the page.";
    } else {

        $identifier = trim($_POST["identifier"] ?? "");
        $password = $_POST["password"] ?? "";
        $remember = isset($_POST["remember_me"]);

        if ($identifier === "" || $password === "") {
            $message = "Please provide both your Institutional Email / Roll No and password.";
        } else {

            // 2. Query user by either Email OR Student Roll Number (joined with student_profiles)
            $sql = "SELECT 
                        u.id, 
                        u.name, 
                        u.email, 
                        u.password, 
                        u.role, 
                        sp.department, 
                        sp.semester, 
                        sp.roll_number
                    FROM users u
                    LEFT JOIN student_profiles sp ON u.id = sp.user_id
                    WHERE u.email = ? OR sp.roll_number = ?
                    LIMIT 1";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $identifier, $identifier);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user["password"])) {
                    // Session security regeneration
                    session_regenerate_id(true);

                    // Update last login timestamp in institutional record
                    $updateStmt = $conn->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
                    if ($updateStmt) {
                        $updateStmt->bind_param("i", $user["id"]);
                        $updateStmt->execute();
                        $updateStmt->close();
                    }

                    // Audit trail record
                    logAuthActivity($conn, (int)$user["id"], $user["email"], 'LOGIN_SUCCESS');

                    // Set authenticated student session
                    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
                    $_SESSION["user_id"] = (int) $user["id"];
                    $_SESSION["user_name"] = $user["name"];
                    $_SESSION["user_email"] = $user["email"];
                    $_SESSION["user_role"] = $user["role"];
                    $_SESSION["user_department"] = $user["department"] ?? "";
                    $_SESSION["user_roll_number"] = $user["roll_number"] ?? "";
                    $_SESSION["flash_success"] = "Welcome back, " . htmlspecialchars($user["name"]) . "! Institutional access verified.";

                    if ($remember) {
                        // Extend session cookie to 30 days
                        $cookieLifetime = 30 * 24 * 60 * 60;
                        setcookie(session_name(), session_id(), time() + $cookieLifetime, "/", "", false, true);
                    }

                    header("Location: ../../public/dashboard.php");
                    exit;

                } else {
                    // Invalid password - log failed audit entry
                    logAuthActivity($conn, (int)$user["id"], $user["email"], 'LOGIN_FAILED');
                    $message = "Invalid institutional credentials. Please check your password.";
                }
            } else {
                // User not found - log attempt
                logAuthActivity($conn, null, $identifier, 'LOGIN_FAILED');
                $message = "No student record found with the provided Email or Roll Number.";
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
    <title>Student Identity & Access Portal | Campus Nexus</title>
    <meta name="description" content="Secure Institutional Single Sign-On and Identity Portal for Campus Nexus.">
    <!-- Official Favicon -->
    <link rel="icon" type="image/png" href="../../images/favicon.png">
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body class="auth-page">

    <div class="auth-portal-wrapper">

        <main class="auth-portal-card" role="main">

            <!-- Left Institutional Showcase Panel -->
            <aside class="auth-portal-sidebar" aria-label="Campus Nexus Institutional Overview">
                <div class="auth-portal-sidebar-content">

                    <a class="brand" href="../../public/index.php" style="margin-bottom: 24px; text-decoration: none;">
                        <div class="brand-logo-wrap" style="width: 44px; height: 44px;">
                            <img src="../../images/cn-logo.png" alt="Campus Nexus Emblem" class="brand-logo-img">
                        </div>
                        <div class="brand-info">
                            <span class="brand-text" style="color: white; font-size: 1.25rem;">Campus <span class="brand-accent">Nexus</span></span>
                            <span class="brand-subtext" style="color: #94a3b8; font-size: 0.72rem;">Student Collaboration Network</span>
                        </div>
                    </a>

                    <div class="auth-portal-badge">
                        <span class="live-dot" aria-hidden="true"></span>
                        <span>Institutional Single Sign-On</span>
                    </div>

                    <h2 class="font-display" style="font-size: 1.45rem; line-height: 1.3; margin: 0 0 10px; color: #ffffff;">
                        Centralized Academic Access Gateway
                    </h2>
                    <p style="font-size: 0.85rem; color: #94a3b8; line-height: 1.55; margin: 0;">
                        Secure authentication portal for students, peer tutors, hackathon organizers, and faculty-approved student project teams.
                    </p>

                    <ul class="auth-feature-list">
                        <li class="auth-feature-item">
                            <div class="auth-feature-icon" aria-hidden="true">🏛️</div>
                            <div class="auth-feature-text">
                                <h4>Unified Student Records</h4>
                                <p>Integrated tracking of academic skills, verified badges, and active projects.</p>
                            </div>
                        </li>
                        <li class="auth-feature-item">
                            <div class="auth-feature-icon" aria-hidden="true">🔒</div>
                            <div class="auth-feature-text">
                                <h4>Enterprise Access Protection</h4>
                                <p>Cryptographic password hashing (Bcrypt) and automated security audit records.</p>
                            </div>
                        </li>
                        <li class="auth-feature-item">
                            <div class="auth-feature-icon" aria-hidden="true">⚡</div>
                            <div class="auth-feature-text">
                                <h4>Instant Teaming & Exchange</h4>
                                <p>Direct matchmaking for cross-disciplinary hackathons and peer mentoring.</p>
                            </div>
                        </li>
                    </ul>

                </div>

                <div class="auth-portal-sidebar-footer">
                    <span>Academic Year 2026–27</span>
                    <span style="display: inline-flex; align-items: center; gap: 5px;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        SSL 256-Bit
                    </span>
                </div>
            </aside>

            <!-- Right Interactive Form Panel -->
            <section class="auth-portal-main">

                <!-- Navigation Tabs (Sign In vs Student Enrollment) -->
                <nav class="auth-nav-tabs" aria-label="Portal Mode">
                    <a href="login.php" class="auth-nav-tab active">Student Sign In</a>
                    <a href="register.php" class="auth-nav-tab">New Enrollment</a>
                </nav>

                <h1 class="auth-form-title">Student Portal Access</h1>
                <p class="auth-form-subtitle">
                    Enter your registered university email or student roll number to access your workspace.
                </p>

                <?php if ($message !== ""): ?>
                    <div class="alert alert-error" role="alert" style="margin-bottom: 22px;">
                        <span class="alert-icon" aria-hidden="true">✕</span>
                        <div class="alert-content"><?= htmlspecialchars($message) ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" novalidate id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">

                    <!-- Email or Student Roll Number -->
                    <div class="form-field">
                        <label for="identifier" class="form-field-label">
                            <span>University Email or Student ID / Roll No<span class="required-star">*</span></span>
                        </label>
                        <div class="form-input-container">
                            <span class="input-lead-icon" aria-hidden="true">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            </span>
                            <input
                                type="text"
                                id="identifier"
                                name="identifier"
                                value="<?= htmlspecialchars($_POST["identifier"] ?? "") ?>"
                                autocomplete="username"
                                placeholder="e.g. alex@university.edu or 2024CS1049"
                                required
                            >
                        </div>
                    </div>

                    <!-- Password with Show/Hide Toggle -->
                    <div class="form-field">
                        <div class="form-field-label">
                            <label for="password" style="margin: 0;">Password<span class="required-star">*</span></label>
                        </div>
                        <div class="form-input-container">
                            <span class="input-lead-icon" aria-hidden="true">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            </span>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                autocomplete="current-password"
                                placeholder="Enter institutional access password"
                                required
                            >
                            <button
                                type="button"
                                class="pwd-toggle-btn"
                                id="togglePasswordBtn"
                                aria-label="Toggle password visibility"
                                title="Show / Hide Password"
                            >
                                <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me & IT Assistance Row -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin: 16px 0 24px; font-size: 0.85rem;">
                        <label class="form-checkbox-label">
                            <input type="checkbox" name="remember_me" id="rememberMe" <?= isset($_POST["remember_me"]) ? "checked" : "" ?>>
                            <span>Remember this workstation</span>
                        </label>
                        <a href="mailto:it-support@campusnexus.internal?subject=Student%20Account%20Recovery" style="color: var(--primary); text-decoration: none; font-weight: 600;" title="Contact IT Helpdesk">
                            Need Access Help?
                        </a>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-glow" style="width: 100%; padding: 13px; font-size: 1rem; font-weight: 700; border-radius: var(--radius-md);">
                        Verify & Sign In to Portal
                    </button>
                </form>

                <!-- Security Compliance Footer -->
                <div class="auth-security-notice">
                    <span style="display: inline-flex; align-items: center; gap: 6px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        Official Institutional Access
                    </span>
                    <span>FERPA & Student Privacy Protected</span>
                </div>

            </section>

        </main>

    </div>

    <!-- Interactive Password Visibility Toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.getElementById('togglePasswordBtn');
            const pwdInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (toggleBtn && pwdInput) {
                toggleBtn.addEventListener('click', () => {
                    const isPassword = pwdInput.getAttribute('type') === 'password';
                    pwdInput.setAttribute('type', isPassword ? 'text' : 'password');
                    eyeIcon.innerHTML = isPassword
                        ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>'
                        : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
                });
            }
        });
    </script>
</body>
</html>
