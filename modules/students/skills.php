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

/*
|--------------------------------------------------------------------------
| Add or Update Skill
|--------------------------------------------------------------------------
*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $submittedToken = $_POST["csrf_token"] ?? "";
    if (!hash_equals($_SESSION["csrf_token"], $submittedToken)) {
        $message = "Invalid security token. Please try again.";
        $messageType = "error";
    } else {

        $skillId = (int) ($_POST["skill_id"] ?? 0);
        $proficiency = $_POST["proficiency"] ?? "intermediate";

        $allowedProficiency = [
            "beginner",
            "intermediate",
            "advanced",
            "expert"
        ];

        if ($skillId <= 0 || !in_array($proficiency, $allowedProficiency, true)) {
            $message = "Please select a valid skill and proficiency level.";
            $messageType = "error";
        } else {

            $sql = "INSERT INTO user_skills (user_id, skill_id, proficiency)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE proficiency = VALUES(proficiency)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iis", $userId, $skillId, $proficiency);

            if ($stmt->execute()) {
                $message = "Skill saved to your profile successfully.";
                $messageType = "success";
            } else {
                $message = "Failed to save skill. Please try again.";
                $messageType = "error";
            }

            $stmt->close();
        }
    }
}

/*
|--------------------------------------------------------------------------
| Get available skills catalog
|--------------------------------------------------------------------------
*/
$sql = "SELECT id, name, category FROM skills ORDER BY category ASC, name ASC";
$result = $conn->query($sql);
$skills = [];
while ($row = $result->fetch_assoc()) {
    $skills[] = $row;
}

/*
|--------------------------------------------------------------------------
| Get student's logged skills
|--------------------------------------------------------------------------
*/
$sql = "SELECT
            skills.id,
            skills.name,
            skills.category,
            user_skills.proficiency
        FROM user_skills
        JOIN skills ON user_skills.skill_id = skills.id
        WHERE user_skills.user_id = ?
        ORDER BY skills.name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userSkills = [];
while ($row = $result->fetch_assoc()) {
    $userSkills[] = $row;
}
$stmt->close();

$pageTitle = "My Skills";
$breadcrumbs = [
    ["label" => "Dashboard", "url" => "../../public/dashboard.php"],
    ["label" => "Profile", "url" => "profile.php"],
    ["label" => "Skills"]
];

require_once __DIR__ . "/../../includes/header.php";

?>

<main class="container">

    <div class="page-header">
        <div>
            <h1 class="page-header-title">My Skills</h1>
            <p class="page-header-subtitle">List your skills to increase project compatibility scores and discover barter partners.</p>
        </div>
        <div class="page-header-actions">
            <a class="btn btn-secondary" href="profile.php">Back to Profile</a>
            <a class="btn btn-secondary" href="interests.php">Manage Interests</a>
        </div>
    </div>

    <?php if ($message !== ""): ?>
        <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'error' ?>" role="alert" style="margin-bottom: 24px;">
            <span class="alert-icon" aria-hidden="true"><?= $messageType === 'success' ? '✓' : '✕' ?></span>
            <div class="alert-content"><?= htmlspecialchars($message) ?></div>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 28px; align-items: start;">

        <!-- Form Card -->
        <div class="card card-interactive card-peach" style="padding: 28px;">
            <h2 class="font-display" style="margin-top: 0; font-size: 1.35rem; margin-bottom: 18px; color: var(--text-primary);">
                + Add or Update Skill
            </h2>

            <form method="POST" action="skills.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">

                <div style="margin-bottom: 18px;">
                    <label for="skill_id" style="display: block; font-weight: 600; margin-bottom: 6px;">Select Skill</label>
                    <select id="skill_id" name="skill_id" required>
                        <option value="">-- Choose from Catalog --</option>
                        <?php
                        $currentCat = "";
                        foreach ($skills as $s):
                            if ($currentCat !== $s["category"]) {
                                if ($currentCat !== "") echo '</optgroup>';
                                $currentCat = $s["category"] ?? "General";
                                echo '<optgroup label="' . htmlspecialchars($currentCat) . '">';
                            }
                        ?>
                            <option value="<?= (int) $s["id"] ?>">
                                <?= htmlspecialchars($s["name"]) ?>
                            </option>
                        <?php endforeach; ?>
                        <?php if ($currentCat !== "") echo '</optgroup>'; ?>
                    </select>
                </div>

                <div style="margin-bottom: 24px;">
                    <label for="proficiency" style="display: block; font-weight: 600; margin-bottom: 6px;">Proficiency Level</label>
                    <select id="proficiency" name="proficiency" required>
                        <option value="beginner">Beginner (Foundational understanding)</option>
                        <option value="intermediate" selected>Intermediate (Built projects / coursework)</option>
                        <option value="advanced">Advanced (High confidence / production)</option>
                        <option value="expert">Expert (Can mentor peers)</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-glow" style="width: 100%;">
                    Save Skill to Profile
                </button>
            </form>
        </div>

        <!-- Current Skills Card -->
        <div class="card card-interactive" style="padding: 28px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
                <h2 class="font-display" style="margin: 0; font-size: 1.35rem; color: var(--text-primary);">
                    Current Skills (<?= count($userSkills) ?>)
                </h2>
                <span class="status status-peach">Live in Match Engine</span>
            </div>

            <?php if (empty($userSkills)): ?>
                <div style="text-align: center; padding: 36px 20px; color: var(--muted); border: 2px dashed var(--border); border-radius: var(--radius-md);">
                    <div style="font-size: 2.2rem; margin-bottom: 8px;">📚</div>
                    <p style="margin: 0 0 8px; font-weight: 600; color: var(--text-secondary);">No skills added yet.</p>
                    <p style="margin: 0; font-size: 0.9rem;">Select a skill from the form to start matching with open project teams.</p>
                </div>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th>Skill</th>
                            <th>Category</th>
                            <th>Proficiency</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($userSkills as $skill): ?>
                            <tr>
                                <td>
                                    <strong style="color: var(--text-primary);"><?= htmlspecialchars($skill["name"]) ?></strong>
                                </td>
                                <td style="color: var(--muted); font-size: 0.9rem;">
                                    <?= htmlspecialchars($skill["category"] ?? "General") ?>
                                </td>
                                <td>
                                    <span class="status status-success" style="font-size: 0.82rem;">
                                        <?= htmlspecialchars(ucfirst($skill["proficiency"])) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>

</main>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>