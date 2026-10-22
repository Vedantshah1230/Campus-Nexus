<?php

session_start();

require_once "../../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| Get open projects
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            projects.id,
            projects.title,
            projects.description,
            projects.created_at,
            projects.creator_id,
            users.name AS creator
        FROM projects
        JOIN users
            ON projects.creator_id = users.id
        WHERE projects.status = 'open'
        ORDER BY projects.created_at DESC";

$result = $conn->query($sql);

$projects = [];

while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
}

/*
|--------------------------------------------------------------------------
| Calculate compatibility
|--------------------------------------------------------------------------
*/

foreach ($projects as &$project) {

    /*
    | Count required skills
    */

    $sql = "SELECT COUNT(*) AS total_required
            FROM project_required_skills
            WHERE project_id = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "i",
        $project["id"]
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $requiredData = $result->fetch_assoc();

    $totalRequired = (int) $requiredData["total_required"];

    $stmt->close();


    /*
    | Count matching skills
    */

    $sql = "SELECT COUNT(*) AS matched_skills
            FROM project_required_skills
            JOIN user_skills
                ON project_required_skills.skill_id =
                   user_skills.skill_id
            WHERE project_required_skills.project_id = ?
              AND user_skills.user_id = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "ii",
        $project["id"],
        $userId
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $matchedData = $result->fetch_assoc();

    $matchedSkills = (int) $matchedData["matched_skills"];

    $stmt->close();


    /*
    | Calculate percentage
    */

    if ($totalRequired > 0) {

        $project["compatibility"] =
            round(
                ($matchedSkills / $totalRequired) * 100,
                2
            );

    } else {

        $project["compatibility"] = 0;
    }

    $project["total_required"] = $totalRequired;
    $project["matched_skills"] = $matchedSkills;
}

unset($project);

$pageTitle = "Project Team Finder";
$pageDescription = "Discover open student projects, check skill match scores, and request to join teams.";
$breadcrumbs = [
    ["label" => "Dashboard", "url" => "../../public/dashboard.php"],
    ["label" => "Projects", "url" => "team-finder.php"],
    ["label" => "Team Finder"]
];

require_once __DIR__ . "/../../includes/header.php";

?>

<main class="container">

    <div class="page-header">
        <div>
            <h1 class="page-header-title">Project Team Finder</h1>
            <p class="page-header-subtitle">Find projects matching your skills and collaborate with students.</p>
        </div>
        <div class="page-header-actions">
            <a class="btn btn-glow" href="create.php">+ Create Project</a>
            <a class="btn btn-secondary" href="my-projects.php">My Projects</a>
        </div>
    </div>

    <?php if (empty($projects)): ?>
        <div class="card card-interactive">
            <p>No open projects available at the moment.</p>
        </div>
    <?php else: ?>
        <div class="card-grid">
            <?php foreach ($projects as $project): ?>
                <article class="card card-interactive">
                    <h3 style="margin-bottom: 8px;">
                        <?= htmlspecialchars($project["title"]) ?>
                    </h3>

                    <p style="margin-bottom: 12px;">
                        <?= htmlspecialchars($project["description"]) ?>
                    </p>

                    <p style="margin-bottom: 6px;">
                        <strong>Created by:</strong> <?= htmlspecialchars($project["creator"]) ?>
                    </p>

                    <p style="margin-bottom: 6px;">
                        <strong>Compatibility:</strong>
                        <span class="status"><?= $project["compatibility"] ?>% Match</span>
                    </p>

                    <p style="margin-bottom: 16px;">
                        Skills matched: <?= $project["matched_skills"] ?> / <?= $project["total_required"] ?>
                    </p>

                    <?php if ((int) $project["creator_id"] !== (int) $userId): ?>
                        <form method="POST" action="join.php" style="margin: 0;">
                            <input type="hidden" name="project_id" value="<?= $project["id"] ?>">
                            <button type="submit" class="btn">Request to Join</button>
                        </form>
                    <?php else: ?>
                        <span class="status">Your Project</span>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>