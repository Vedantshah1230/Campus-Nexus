<?php

session_start();

require_once "../../config/database.php";
require_once "../gamification/helpers.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

// Ensure CSRF token exists
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

/*
|--------------------------------------------------------------------------
| Create project handler
|--------------------------------------------------------------------------
*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Verify CSRF token
    if (!hash_equals($_SESSION["csrf_token"] ?? "", $_POST["csrf_token"] ?? "")) {
        $_SESSION["flash_error"] = "Security validation failed. Please submit the form again.";
        header("Location: create.php");
        exit;
    }

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $selectedSkills = $_POST["skills"] ?? [];

    if ($title === "" || $description === "") {
        $_SESSION["flash_error"] = "Project title and description are required.";
    } elseif (empty($selectedSkills) || !is_array($selectedSkills)) {
        $_SESSION["flash_error"] = "Please select at least one required skill so teammates can match with your project.";
    } else {
        $conn->begin_transaction();

        try {
            /*
            | 1. Insert Project
            */
            $sql = "INSERT INTO projects (creator_id, title, description, status) VALUES (?, ?, ?, 'open')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $userId, $title, $description);
            $stmt->execute();
            $projectId = $conn->insert_id;
            $stmt->close();

            /*
            | 2. Insert Required Skills
            */
            $skillSql = "INSERT INTO project_required_skills (project_id, skill_id, importance) VALUES (?, ?, 'required')";
            $skillStmt = $conn->prepare($skillSql);

            foreach ($selectedSkills as $skillId) {
                $sId = (int) $skillId;
                if ($sId > 0) {
                    $skillStmt->bind_param("ii", $projectId, $sId);
                    $skillStmt->execute();
                }
            }
            $skillStmt->close();

            /*
            | 3. Gamification: Check if this is the user's first created project
            */
            $countSql = "SELECT COUNT(*) AS project_count FROM projects WHERE creator_id = ?";
            $countStmt = $conn->prepare($countSql);
            $countStmt->bind_param("i", $userId);
            $countStmt->execute();
            $countRes = $countStmt->get_result()->fetch_assoc();
            $countStmt->close();

            $awardedBadge = false;
            if ((int) ($countRes["project_count"] ?? 0) === 1) {
                // Badge ID 1 = First Project (50 points)
                $badgeAdded = awardBadge($conn, $userId, 1);
                if ($badgeAdded) {
                    awardPoints($conn, $userId, 50);
                    $awardedBadge = true;
                }
            }

            $conn->commit();

            if ($awardedBadge) {
                $_SESSION["flash_success"] = "Project launched! 🏅 You unlocked the 'First Project' badge (+50 points)!";
            } else {
                $_SESSION["flash_success"] = "Project launched successfully! Students matching your required skills can now request to join.";
            }

            header("Location: my-projects.php");
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION["flash_error"] = "An error occurred while creating the project. Please try again.";
        }
    }
}

/*
|--------------------------------------------------------------------------
| Get available skills grouped by category
|--------------------------------------------------------------------------
*/
$sql = "SELECT id, name, category FROM skills ORDER BY category ASC, name ASC";
$result = $conn->query($sql);
$skillsByCategory = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $cat = !empty($row["category"]) ? $row["category"] : "General";
        $skillsByCategory[$cat][] = $row;
    }
}

$pageTitle = "Create New Project";
$breadcrumbs = [
    ["label" => "Dashboard", "url" => "../../public/dashboard.php"],
    ["label" => "Team Finder", "url" => "team-finder.php"],
    ["label" => "Create Project"]
];

require_once __DIR__ . "/../../includes/header.php";

?>

<main class="container">

    <div class="page-header" style="max-width: 760px; margin: 0 auto 24px;">
        <div>
            <h1 class="page-header-title">Create a Project</h1>
            <p class="page-header-subtitle">
                Assemble a student dream team. Specify required technologies to trigger algorithmic skill-compatibility matching.
            </p>
        </div>
        <div class="page-header-actions">
            <a class="btn btn-secondary" href="team-finder.php">← Back to Team Finder</a>
        </div>
    </div>

    <div class="card card-interactive" style="max-width: 760px; margin: 0 auto; padding: 32px; background: white; border: 1px solid var(--border);">

        <form method="POST" action="create.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">

            <div style="margin-bottom: 22px;">
                <label for="title" style="display: block; font-weight: 700; margin-bottom: 8px;">
                    Project Title <span style="color: var(--danger);">*</span>
                </label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    maxlength="150"
                    placeholder="e.g., Campus Autonomous Drone Navigation System"
                    value="<?= htmlspecialchars($_POST["title"] ?? "") ?>"
                    required
                >
                <small style="display: block; color: var(--muted); margin-top: 6px; font-size: 0.82rem;">
                    A concise, descriptive name for your project or hackathon build.
                </small>
            </div>

            <div style="margin-bottom: 24px;">
                <label for="description" style="display: block; font-weight: 700; margin-bottom: 8px;">
                    Project Description & Goals <span style="color: var(--danger);">*</span>
                </label>
                <textarea
                    id="description"
                    name="description"
                    rows="6"
                    placeholder="Describe what you are building, the project's milestones, and the roles you need filled..."
                    required
                ><?= htmlspecialchars($_POST["description"] ?? "") ?></textarea>
                <small style="display: block; color: var(--muted); margin-top: 6px; font-size: 0.82rem;">
                    Explain the problem you are solving, tech stack, and what teammates can expect to learn.
                </small>
            </div>

            <div style="margin-bottom: 28px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px;">
                    Required Teammate Skills <span style="color: var(--danger);">*</span>
                </label>
                <p style="color: var(--muted); font-size: 0.85rem; margin-top: 0; margin-bottom: 14px;">
                    Select skills that prospective collaborators need to possess. Our matching engine will calculate compatibility scores based on these tags.
                </p>

                <?php foreach ($skillsByCategory as $category => $categorySkills): ?>
                    <div style="margin-bottom: 16px;">
                        <span style="display: block; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--muted); margin-bottom: 8px;">
                            <?= htmlspecialchars($category) ?>
                        </span>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px;">
                            <?php foreach ($categorySkills as $skill): ?>
                                <?php
                                $isChecked = (isset($_POST["skills"]) && is_array($_POST["skills"]) && in_array((string)$skill["id"], $_POST["skills"], true));
                                ?>
                                <label style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; background: var(--surface-muted); border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background var(--transition-fast);">
                                    <input
                                        type="checkbox"
                                        name="skills[]"
                                        value="<?= (int) $skill["id"] ?>"
                                        <?= $isChecked ? "checked" : "" ?>
                                        style="margin: 0; cursor: pointer;"
                                    >
                                    <span><?= htmlspecialchars($skill["name"]) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 12px; padding-top: 20px; border-top: 1px solid var(--border);">
                <a class="btn btn-secondary" href="team-finder.php">Cancel</a>
                <button type="submit" class="btn" style="min-width: 160px;">
                    Launch Project 🚀
                </button>
            </div>
        </form>

    </div>

</main>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>