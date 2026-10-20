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

// Generate CSRF token if empty
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";
$messageType = "error";

// Academic Departments Catalog
$departments = [
    "Computer Science & Engineering",
    "Information Technology",
    "Artificial Intelligence & Data Science",
    "Electronics & Communication Engineering",
    "Electrical Engineering",
    "Mechanical Engineering",
    "Civil & Environmental Engineering",
    "Business Administration & Management",
    "Applied Sciences & Design"
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // 1. Verify CSRF Token
    $submittedToken = $_POST["csrf_token"] ?? "";
    if (!hash_equals($_SESSION["csrf_token"], $submittedToken)) {
        $message = "Institutional session expired or invalid. Please refresh and try again.";
    } else {

        $name = trim($_POST["name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $rollNumber = strtoupper(trim($_POST["roll_number"] ?? ""));
        $department = trim($_POST["department"] ?? "");
        $semester = (int) ($_POST["semester"] ?? 1);
        $password = $_POST["password"] ?? "";
        $confirmPassword = $_POST["confirm_password"] ?? "";
        $honorCode = isset($_POST["honor_code"]);

        if ($name === "" || $email === "" || $rollNumber === "" || $department === "" || $password === "") {
            $message = "All academic registration fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Please enter a valid educational or personal email address.";
        } elseif (strlen($rollNumber) < 3 || strlen($rollNumber) > 30) {
            $message = "Please enter a valid Student Enrollment / Roll ID (3–30 characters).";
        } elseif ($semester < 1 || $semester > 8) {
            $message = "Please select a valid academic semester between 1 and 8.";
        } elseif (strlen($password) < 8) {
            $message = "Password must be at least 8 characters long for institutional security.";
        } elseif ($password !== $confirmPassword) {
            $message = "Password confirmation does not match the entered password.";
        } elseif (!$honorCode) {
            $message = "You must acknowledge the Academic Code of Conduct and Computing Policy.";
        } else {

            // 2. Check if email already registered
            $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $checkEmail->bind_param("s", $email);
            $checkEmail->execute();
            $emailResult = $checkEmail->get_result();

            if ($emailResult->num_rows > 0) {
                $message = "An account with this email address already exists in the institutional directory.";
                $checkEmail->close();
            } else {
                $checkEmail->close();

                // 3. Check if roll number already registered
                $checkRoll = $conn->prepare("SELECT user_id FROM student_profiles WHERE roll_number = ? LIMIT 1");
                $checkRoll->bind_param("s", $rollNumber);
                $checkRoll->execute();
                $rollResult = $checkRoll->get_result();

                if ($rollResult->num_rows > 0) {
                    $message = "The Student Roll / Enrollment ID ({$rollNumber}) is already registered in the system.";
                    $checkRoll->close();
                } else {
                    $checkRoll->close();

                    // 4. Begin registration transaction
                    $conn->begin_transaction();

                    try {
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                        // Insert into users
                        $sqlUser = "INSERT INTO users (name, email, password, role, last_login_at) VALUES (?, ?, ?, 'student', NOW())";
                        $stmtUser = $conn->prepare($sqlUser);
                        $stmtUser->bind_param("sss", $name, $email, $hashedPassword);
                        $stmtUser->execute();
                        $newUserId = (int) $stmtUser->insert_id;
                        $stmtUser->close();

                        // Insert into student_profiles
                        $initialBio = "Student of {$department}, Semester {$semester} at Campus Nexus.";
                        $sqlProfile = "INSERT INTO student_profiles (user_id, department, semester, roll_number, bio) VALUES (?, ?, ?, ?, ?)";
                        $stmtProfile = $conn->prepare($sqlProfile);
                        $stmtProfile->bind_param("isiss", $newUserId, $department, $semester, $rollNumber, $initialBio);
                        $stmtProfile->execute();
                        $stmtProfile->close();

                        // Initialize user points with 20 welcome points
                        $sqlPoints = "INSERT INTO user_points (user_id, points) VALUES (?, 20) ON DUPLICATE KEY UPDATE points = points";
                        $stmtPoints = $conn->prepare($sqlPoints);
                        $stmtPoints->bind_param("i", $newUserId);
                        $stmtPoints->execute();
                        $stmtPoints->close();

                        // Commit transaction
                        $conn->commit();

                        // 5. Record institutional audit trail
                        logAuthActivity($conn, $newUserId, $email, 'REGISTRATION');
                        logAuthActivity($conn, $newUserId, $email, 'LOGIN_SUCCESS');

                        // 6. Establish student session
                        session_regenerate_id(true);
                        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
                        $_SESSION["user_id"] = $newUserId;
                        $_SESSION["user_name"] = $name;
                        $_SESSION["user_email"] = $email;
                        $_SESSION["user_role"] = "student";
                        $_SESSION["user_department"] = $department;
                        $_SESSION["user_roll_number"] = $rollNumber;
                        $_SESSION["flash_success"] = "Welcome to Campus Nexus, " . htmlspecialchars($name) . "! Your verified institutional record is active.";

                        header("Location: ../../public/dashboard.php");
                        exit;

                    } catch (Throwable $e) {
                        $conn->rollback();
                        $message = "Registration failed due to a database exception. Please try again.";
                    }
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
    <title>New Student Enrollment | Campus Nexus</title>
    <meta name="description" content="Enroll for an official student account on Campus Nexus.">
    <!-- Official Favicon -->
    <link rel="icon" type="image/png" href="../../images/favicon.png">
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body class="auth-page">

    <div class="auth-portal-wrapper">

        <main class="auth-portal-card" role="main" style="max-width: 1080px;">

            <!-- Left Institutional Showcase Panel -->
            <aside class="auth-portal-sidebar" aria-label="Campus Nexus Student Enrollment Overview">
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
                        <span>Official Student Enrollment</span>
                    </div>

                    <h2 class="font-display" style="font-size: 1.45rem; line-height: 1.3; margin: 0 0 10px; color: #ffffff;">
                        Create Your Academic Collaboration Profile
                    </h2>
                    <p style="font-size: 0.85rem; color: #94a3b8; line-height: 1.55; margin: 0;">
                        Join thousands of student innovators. Build verified technical portfolios, find hackathon teammates, trade peer skills, and pitch campus proposals.
                    </p>

                    <ul class="auth-feature-list">
                        <li class="auth-feature-item">
                            <div class="auth-feature-icon" aria-hidden="true">📋</div>
                            <div class="auth-feature-text">
                                <h4>Academic Enrollment Verification</h4>
                                <p>Your Roll Number and department create a permanent record of your projects and milestones.</p>
                            </div>
                        </li>
                        <li class="auth-feature-item">
                            <div class="auth-feature-icon" aria-hidden="true">⭐</div>
                            <div class="auth-feature-text">
                                <h4>+20 Welcome XP</h4>
                                <p>Immediately unlocks access to the Student Leaderboard and project matchmaking.</p>
                            </div>
                        </li>
                        <li class="auth-feature-item">
                            <div class="auth-feature-icon" aria-hidden="true">🤝</div>
                            <div class="auth-feature-text">
                                <h4>Peer-to-Peer Mentorship</h4>
                                <p>Trade programming skills and academic mentoring across engineering branches.</p>
                            </div>
                        </li>
                    </ul>

                </div>

                <div class="auth-portal-sidebar-footer">
                    <span>Institutional Registry 2026</span>
                    <span style="display: inline-flex; align-items: center; gap: 5px;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        SSL 256-Bit
                    </span>
                </div>
            </aside>

            <!-- Right Interactive Registration Form Panel -->
            <section class="auth-portal-main">

                <!-- Navigation Tabs (Sign In vs Student Enrollment) -->
                <nav class="auth-nav-tabs" aria-label="Portal Mode">
                    <a href="login.php" class="auth-nav-tab">Student Sign In</a>
                    <a href="register.php" class="auth-nav-tab active">New Enrollment</a>
                </nav>

                <h1 class="auth-form-title">Student Registration</h1>
                <p class="auth-form-subtitle">
                    Enter your academic details to register in the Campus Nexus student directory.
                </p>

                <?php if ($message !== ""): ?>
                    <div class="alert alert-error" role="alert" style="margin-bottom: 22px;">
                        <span class="alert-icon" aria-hidden="true">✕</span>
                        <div class="alert-content"><?= htmlspecialchars($message) ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="register.php" novalidate id="registrationForm">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">

                    <!-- Full Name & Roll Number Grid -->
                    <div class="form-group-grid">
                        <div class="form-field">
                            <label for="name" class="form-field-label">
                                <span>Full Legal Name<span class="required-star">*</span></span>
                            </label>
                            <div class="form-input-container">
                                <span class="input-lead-icon" aria-hidden="true">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                </span>
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
                        </div>

                        <div class="form-field">
                            <label for="roll_number" class="form-field-label">
                                <span>Student Roll / Enrollment ID<span class="required-star">*</span></span>
                            </label>
                            <div class="form-input-container">
                                <span class="input-lead-icon" aria-hidden="true">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"></rect><line x1="7" y1="8" x2="17" y2="8"></line><line x1="7" y1="12" x2="13" y2="12"></line></svg>
                                </span>
                                <input
                                    type="text"
                                    id="roll_number"
                                    name="roll_number"
                                    value="<?= htmlspecialchars($_POST["roll_number"] ?? "") ?>"
                                    placeholder="e.g. 2024CS1049"
                                    style="text-transform: uppercase;"
                                    required
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Email Address -->
                    <div class="form-field">
                        <label for="email" class="form-field-label">
                            <span>University or College Email<span class="required-star">*</span></span>
                        </label>
                        <div class="form-input-container">
                            <span class="input-lead-icon" aria-hidden="true">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            </span>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="<?= htmlspecialchars($_POST["email"] ?? "") ?>"
                                autocomplete="email"
                                placeholder="e.g. alex.johnson@university.edu"
                                required
                            >
                        </div>
                    </div>

                    <!-- Department & Semester Grid -->
                    <div class="form-group-grid">
                        <div class="form-field">
                            <label for="department" class="form-field-label">
                                <span>Academic Department<span class="required-star">*</span></span>
                            </label>
                            <div class="form-input-container">
                                <span class="input-lead-icon" aria-hidden="true">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                                </span>
                                <select id="department" name="department" required>
                                    <option value="" disabled <?= empty($_POST["department"]) ? "selected" : "" ?>>Select Department / Major</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= htmlspecialchars($dept) ?>" <?= (($_POST["department"] ?? "") === $dept) ? "selected" : "" ?>>
                                            <?= htmlspecialchars($dept) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="semester" class="form-field-label">
                                <span>Academic Semester / Year<span class="required-star">*</span></span>
                            </label>
                            <div class="form-input-container">
                                <span class="input-lead-icon" aria-hidden="true">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                </span>
                                <select id="semester" name="semester" required>
                                    <option value="1" <?= (($_POST["semester"] ?? "1") == "1") ? "selected" : "" ?>>Semester 1 (Year 1)</option>
                                    <option value="2" <?= (($_POST["semester"] ?? "") == "2") ? "selected" : "" ?>>Semester 2 (Year 1)</option>
                                    <option value="3" <?= (($_POST["semester"] ?? "") == "3") ? "selected" : "" ?>>Semester 3 (Year 2)</option>
                                    <option value="4" <?= (($_POST["semester"] ?? "") == "4") ? "selected" : "" ?>>Semester 4 (Year 2)</option>
                                    <option value="5" <?= (($_POST["semester"] ?? "") == "5") ? "selected" : "" ?>>Semester 5 (Year 3)</option>
                                    <option value="6" <?= (($_POST["semester"] ?? "") == "6") ? "selected" : "" ?>>Semester 6 (Year 3)</option>
                                    <option value="7" <?= (($_POST["semester"] ?? "") == "7") ? "selected" : "" ?>>Semester 7 (Year 4)</option>
                                    <option value="8" <?= (($_POST["semester"] ?? "") == "8") ? "selected" : "" ?>>Semester 8 (Year 4)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Password & Confirm Password Grid -->
                    <div class="form-group-grid">
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
                                    autocomplete="new-password"
                                    placeholder="Min. 8 characters"
                                    required
                                >
                                <button type="button" class="pwd-toggle-btn" id="togglePasswordBtn" aria-label="Toggle password visibility">
                                    <svg id="eyeIcon1" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                            </div>
                            <div class="pwd-strength-container" id="strengthMeter" aria-live="polite">
                                <div class="pwd-strength-bar">
                                    <div class="pwd-strength-segment" id="seg1"></div>
                                    <div class="pwd-strength-segment" id="seg2"></div>
                                    <div class="pwd-strength-segment" id="seg3"></div>
                                </div>
                                <div class="pwd-strength-label">
                                    <span id="strengthText">Password Strength</span>
                                    <span>Min 8 characters</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-field">
                            <div class="form-field-label">
                                <label for="confirm_password" style="margin: 0;">Confirm Password<span class="required-star">*</span></label>
                            </div>
                            <div class="form-input-container">
                                <span class="input-lead-icon" aria-hidden="true">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                </span>
                                <input
                                    type="password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    autocomplete="new-password"
                                    placeholder="Re-enter password"
                                    required
                                >
                            </div>
                            <div style="font-size: 0.72rem; color: var(--muted); margin-top: 6px;" id="matchNotice">
                                Passwords must match exactly.
                            </div>
                        </div>
                    </div>

                    <!-- Honor Code & Institutional Computing Agreement -->
                    <div style="margin: 18px 0 24px;">
                        <label class="form-checkbox-label">
                            <input type="checkbox" name="honor_code" id="honorCode" required <?= isset($_POST["honor_code"]) ? "checked" : "" ?>>
                            <span>
                                I certify that I am a bonafide student and agree to uphold the <strong>Academic Code of Conduct</strong> and <strong>Institutional Computing Policies</strong>.
                            </span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-glow" style="width: 100%; padding: 13px; font-size: 1rem; font-weight: 700; border-radius: var(--radius-md);">
                        Complete Enrollment & Create Student Record
                    </button>
                </form>

                <!-- Security Compliance Footer -->
                <div class="auth-security-notice">
                    <span style="display: inline-flex; align-items: center; gap: 6px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        Official Institutional Registry
                    </span>
                    <span>Immutable Audit Log & Data Protection</span>
                </div>

            </section>

        </main>

    </div>

    <!-- Password Strength & Toggle Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Password toggle
            const toggleBtn = document.getElementById('togglePasswordBtn');
            const pwdInput = document.getElementById('password');
            const eyeIcon1 = document.getElementById('eyeIcon1');

            if (toggleBtn && pwdInput) {
                toggleBtn.addEventListener('click', () => {
                    const isPwd = pwdInput.getAttribute('type') === 'password';
                    pwdInput.setAttribute('type', isPwd ? 'text' : 'password');
                    eyeIcon1.innerHTML = isPwd
                        ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>'
                        : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
                });
            }

            // Real-time password strength
            const seg1 = document.getElementById('seg1');
            const seg2 = document.getElementById('seg2');
            const seg3 = document.getElementById('seg3');
            const strengthText = document.getElementById('strengthText');
            const confirmInput = document.getElementById('confirm_password');
            const matchNotice = document.getElementById('matchNotice');

            if (pwdInput) {
                pwdInput.addEventListener('input', () => {
                    const val = pwdInput.value;
                    let score = 0;
                    if (val.length >= 8) score++;
                    if (/[A-Z]/.test(val) && /[0-9]/.test(val)) score++;
                    if (/[^A-Za-z0-9]/.test(val) && val.length >= 10) score++;

                    // Reset segments
                    seg1.style.background = '#e2e8f0';
                    seg2.style.background = '#e2e8f0';
                    seg3.style.background = '#e2e8f0';

                    if (val.length === 0) {
                        strengthText.textContent = 'Password Strength';
                        strengthText.style.color = 'var(--muted)';
                    } else if (score === 1 || val.length < 8) {
                        seg1.style.background = '#ef4444';
                        strengthText.textContent = 'Weak Password';
                        strengthText.style.color = '#ef4444';
                    } else if (score === 2) {
                        seg1.style.background = '#f59e0b';
                        seg2.style.background = '#f59e0b';
                        strengthText.textContent = 'Good Password';
                        strengthText.style.color = '#d97706';
                    } else {
                        seg1.style.background = '#10b981';
                        seg2.style.background = '#10b981';
                        seg3.style.background = '#10b981';
                        strengthText.textContent = 'Strong Institutional Password';
                        strengthText.style.color = '#10b981';
                    }
                });
            }

            // Real-time confirm check
            if (confirmInput && pwdInput) {
                confirmInput.addEventListener('input', () => {
                    if (confirmInput.value === '') {
                        matchNotice.textContent = 'Passwords must match exactly.';
                        matchNotice.style.color = 'var(--muted)';
                    } else if (confirmInput.value === pwdInput.value) {
                        matchNotice.textContent = '✓ Passwords match.';
                        matchNotice.style.color = '#10b981';
                    } else {
                        matchNotice.textContent = '✕ Passwords do not match yet.';
                        matchNotice.style.color = '#ef4444';
                    }
                });
            }
        });
    </script>
</body>
</html>