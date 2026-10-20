<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

// CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";
$messageType = "info";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $submittedToken = $_POST["csrf_token"] ?? "";
    if (!hash_equals($_SESSION["csrf_token"], $submittedToken)) {
        $message = "Invalid security token. Please try again.";
        $messageType = "error";
    } else {

        $department = trim($_POST["department"] ?? "");
        $semester = (int) ($_POST["semester"] ?? 1);
        $rollNumber = strtoupper(trim($_POST["roll_number"] ?? ""));
        $bio = trim($_POST["bio"] ?? "");

        if ($department === "") {
            $message = "Please enter your department or major.";
            $messageType = "error";
        } elseif ($semester < 1 || $semester > 12) {
            $message = "Please select a valid semester between 1 and 12.";
            $messageType = "error";
        } else {

            $sql = "INSERT INTO student_profiles
                        (user_id, department, semester, roll_number, bio)
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        department = VALUES(department),
                        semester = VALUES(semester),
                        roll_number = VALUES(roll_number),
                        bio = VALUES(bio)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isiss", $userId, $department, $semester, $rollNumber, $bio);

            if ($stmt->execute()) {
                $_SESSION["user_department"] = $department;
                $_SESSION["user_roll_number"] = $rollNumber;
                $message = "Your institutional student profile has been updated successfully.";
                $messageType = "success";
            } else {
                $message = "Failed to update profile. Please try again.";
                $messageType = "error";
            }

            $stmt->close();
        }
    }
}

// Fetch current values
$sql = "SELECT department, semester, roll_number, bio FROM student_profiles WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

$pageTitle = "Edit Profile";
$breadcrumbs = [
    ["label" => "Dashboard", "url" => "../../public/dashboard.php"],
    ["label" => "Profile", "url" => "profile.php"],
    ["label" => "Edit Profile"]
];

require_once __DIR__ . "/../../includes/header.php";

?>

<main class="container">

    <div class="page-header">
        <div>
            <h1 class="page-header-title">Edit Profile</h1>
            <p class="page-header-subtitle">Keep your academic background and personal bio up to date.</p>
        </div>
        <div class="page-header-actions">
            <a class="btn btn-secondary" href="profile.php">Back to Profile</a>
        </div>
    </div>

    <div class="card card-interactive" style="max-width: 680px; margin: 0 auto; background: white; padding: 32px;">

        <?php if ($message !== ""): ?>
            <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'error' ?>" role="alert" style="margin-bottom: 24px;">
                <span class="alert-icon" aria-hidden="true"><?= $messageType === 'success' ? '✓' : '✕' ?></span>
                <div class="alert-content"><?= htmlspecialchars($message) ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="edit-profile.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">

            <div style="margin-bottom: 20px;">
                <label for="roll_number" style="display: block; font-weight: 600; margin-bottom: 6px;">Student Roll / Enrollment ID</label>
                <input
                    type="text"
                    id="roll_number"
                    name="roll_number"
                    value="<?= htmlspecialchars($profile["roll_number"] ?? "") ?>"
                    placeholder="e.g. 2024CS1049"
                    style="text-transform: uppercase;"
                >
                <span style="font-size: 0.82rem; color: var(--muted); display: block; margin-top: 4px;">
                    Your official university enrollment identifier. Used for direct portal sign-in and academic verification.
                </span>
            </div>

            <div style="margin-bottom: 20px;">
                <label for="department" style="display: block; font-weight: 600; margin-bottom: 6px;">Department / Major</label>
                <input
                    type="text"
                    id="department"
                    name="department"
                    value="<?= htmlspecialchars($profile["department"] ?? "") ?>"
                    placeholder="e.g. Computer Science & Engineering"
                    required
                >
            </div>

            <div style="margin-bottom: 20px;">
                <label for="semester" style="display: block; font-weight: 600; margin-bottom: 6px;">Current Semester (1 - 12)</label>
                <input
                    type="number"
                    id="semester"
                    name="semester"
                    min="1"
                    max="12"
                    value="<?= htmlspecialchars((string) ($profile["semester"] ?? "1")) ?>"
                    required
                >
            </div>

            <div style="margin-bottom: 26px;">
                <label for="bio" style="display: block; font-weight: 600; margin-bottom: 6px;">About You / Collaboration Bio</label>
                <textarea
                    id="bio"
                    name="bio"
                    rows="4"
                    placeholder="Describe your technical interests, projects you want to build, and what you bring to a team..."
                ><?= htmlspecialchars($profile["bio"] ?? "") ?></textarea>
                <span style="font-size: 0.82rem; color: var(--muted); display: block; margin-top: 4px;">
                    This description is shown to other students when browsing profiles or project join requests.
                </span>
            </div>

            <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <button type="submit" class="btn btn-glow">
                    Save Profile Changes
                </button>
                <a class="btn btn-secondary" href="profile.php">
                    Cancel
                </a>
            </div>

        </form>

    </div>

</main>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>