<?php

session_start();

require_once "../../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

// 1. Fetch user & profile data
$sql = "SELECT
            users.name,
            users.email,
            users.created_at,
            student_profiles.department,
            student_profiles.semester,
            student_profiles.bio,
            student_profiles.profile_picture
        FROM users
        LEFT JOIN student_profiles
            ON users.id = student_profiles.user_id
        WHERE users.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 2. Fetch logged skills with proficiencies
$skillSql = "SELECT skills.name, user_skills.proficiency
             FROM user_skills
             JOIN skills ON user_skills.skill_id = skills.id
             WHERE user_skills.user_id = ?
             ORDER BY skills.name ASC";
$skillStmt = $conn->prepare($skillSql);
$skillStmt->bind_param("i", $userId);
$skillStmt->execute();
$skillsResult = $skillStmt->get_result();
$userSkills = [];
while ($row = $skillsResult->fetch_assoc()) {
    $userSkills[] = $row;
}
$skillStmt->close();

// 3. Fetch logged interests
$intSql = "SELECT interests.name
           FROM user_interests
           JOIN interests ON user_interests.interest_id = interests.id
           WHERE user_interests.user_id = ?
           ORDER BY interests.name ASC";
$intStmt = $conn->prepare($intSql);
$intStmt->bind_param("i", $userId);
$intStmt->execute();
$intResult = $intStmt->get_result();
$userInterests = [];
while ($row = $intResult->fetch_assoc()) {
    $userInterests[] = $row;
}
$intStmt->close();

// Generate user initials for avatar
$nameParts = explode(" ", trim($profile["name"] ?? "Student"));
$initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ""));

$pageTitle = "My Student Profile";
$breadcrumbs = [
    ["label" => "Dashboard", "url" => "../../public/dashboard.php"],
    ["label" => "Student Profile"]
];

require_once __DIR__ . "/../../includes/header.php";

?>

<main class="container">

    <div class="page-header">
        <div>
            <h1 class="page-header-title">Student Profile</h1>
            <p class="page-header-subtitle">Your academic identity, technical proficiencies, and collaboration preferences.</p>
        </div>
        <div class="page-header-actions">
            <a class="btn btn-glow" href="edit-profile.php">Edit Profile</a>
            <a class="btn btn-secondary" href="skills.php">Manage Skills</a>
            <a class="btn btn-secondary" href="interests.php">Manage Interests</a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 28px; align-items: start;">

        <!-- Left Column: Identity Card -->
        <div class="card card-interactive" style="text-align: center; padding: 32px 24px;">
            <div style="width: 88px; height: 88px; border-radius: 50%; background: linear-gradient(135deg, var(--primary) 0%, #6366f1 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 800; font-family: var(--font-display); margin: 0 auto 18px; box-shadow: 0 4px 18px rgba(99, 102, 241, 0.35);">
                <?= htmlspecialchars($initials) ?>
            </div>

            <h2 style="font-family: var(--font-display); margin: 0 0 6px; font-size: 1.4rem;">
                <?= htmlspecialchars($profile["name"] ?? "Student") ?>
            </h2>

            <p style="color: var(--muted); font-size: 0.92rem; margin: 0 0 16px;">
                <?= htmlspecialchars($profile["email"] ?? "") ?>
            </p>

            <div style="display: flex; justify-content: center; gap: 8px; flex-wrap: wrap; margin-bottom: 20px;">
                <span class="status status-peach">
                    <?= htmlspecialchars($profile["department"] ?? "General Studies") ?>
                </span>
                <span class="status" style="background: var(--surface-muted); color: var(--text-secondary);">
                    Semester <?= htmlspecialchars((string) ($profile["semester"] ?? 1)) ?>
                </span>
            </div>

            <hr style="margin: 20px 0; border: none; border-top: 1px solid var(--border);">

            <div style="text-align: left; font-size: 0.88rem; color: var(--muted); line-height: 1.8;">
                <div>✦ <strong>Member Since:</strong> <?= date("M Y", strtotime($profile["created_at"] ?? "now")) ?></div>
                <div>✦ <strong>Role:</strong> University Student</div>
            </div>
        </div>

        <!-- Right Column: Details, Skills & Interests -->
        <div style="display: flex; flex-direction: column; gap: 24px;">

            <!-- Bio Card -->
            <div class="card card-interactive">
                <h3 style="margin-top: 0; font-family: var(--font-display); font-size: 1.2rem; color: var(--text-primary);">
                    About Me
                </h3>
                <p style="color: var(--text-secondary); line-height: 1.7; margin: 0;">
                    <?= nl2br(htmlspecialchars($profile["bio"] ?? "No biography provided yet. Click 'Edit Profile' to introduce yourself to potential teammates!")) ?>
                </p>
            </div>

            <!-- Skills Card -->
            <div class="card card-interactive card-peach">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h3 style="margin: 0; font-family: var(--font-display); font-size: 1.2rem; color: var(--text-primary);">
                        Technical & Design Skills
                    </h3>
                    <a class="nav-link" style="padding: 0; font-size: 0.88rem; font-weight: 700; color: var(--primary);" href="skills.php">
                        + Update Skills
                    </a>
                </div>

                <?php if (empty($userSkills)): ?>
                    <p style="color: var(--muted); margin: 0; font-size: 0.95rem;">
                        No skills listed yet. Add skills to increase your project matching score!
                    </p>
                <?php else: ?>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <?php foreach ($userSkills as $skill): ?>
                            <span class="status status-success" style="font-size: 0.88rem; padding: 5px 12px;">
                                <strong><?= htmlspecialchars($skill["name"]) ?></strong>
                                <span style="opacity: 0.8; font-size: 0.8rem;">(<?= htmlspecialchars(ucfirst($skill["proficiency"])) ?>)</span>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Interests Card -->
            <div class="card card-interactive">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h3 style="margin: 0; font-family: var(--font-display); font-size: 1.2rem; color: var(--text-primary);">
                        Collaboration Interests
                    </h3>
                    <a class="nav-link" style="padding: 0; font-size: 0.88rem; font-weight: 700; color: var(--primary);" href="interests.php">
                        + Update Interests
                    </a>
                </div>

                <?php if (empty($userInterests)): ?>
                    <p style="color: var(--muted); margin: 0; font-size: 0.95rem;">
                        No interests selected yet. Select interests to discover related ideas and hackathons.
                    </p>
                <?php else: ?>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <?php foreach ($userInterests as $interest): ?>
                            <span class="status status-peach" style="font-size: 0.88rem; padding: 5px 12px;">
                                <?= htmlspecialchars($interest["name"]) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </div>

</main>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>