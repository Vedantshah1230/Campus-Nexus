<?php

session_start();

require_once "../../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| Get projects created by the user
|--------------------------------------------------------------------------
*/
$sql = "SELECT id, title, description, status, created_at
        FROM projects
        WHERE creator_id = ?
        ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$createdProjects = [];
while ($row = $result->fetch_assoc()) {
    $createdProjects[] = $row;
}
$stmt->close();

/*
|--------------------------------------------------------------------------
| Get projects joined/requested by the user
|--------------------------------------------------------------------------
*/
$sql = "SELECT
            projects.id,
            projects.title,
            projects.description,
            project_members.role,
            project_members.status,
            project_members.joined_at,
            users.name AS creator
        FROM project_members
        JOIN projects ON project_members.project_id = projects.id
        JOIN users ON projects.creator_id = users.id
        WHERE project_members.user_id = ?
        ORDER BY project_members.joined_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$joinedProjects = [];
while ($row = $result->fetch_assoc()) {
    $joinedProjects[] = $row;
}
$stmt->close();

$pageTitle = "My Projects";
$breadcrumbs = [
    ["label" => "Dashboard", "url" => "../../public/dashboard.php"],
    ["label" => "Projects", "url" => "team-finder.php"],
    ["label" => "My Projects"]
];

require_once __DIR__ . "/../../includes/header.php";

?>

<main class="container">

    <div class="page-header">
        <div>
            <h1 class="page-header-title">My Projects</h1>
            <p class="page-header-subtitle">Manage the collaboration initiatives you lead and track your team memberships.</p>
        </div>
        <div class="page-header-actions">
            <a class="btn btn-glow" href="create.php">+ Create Project</a>
            <a class="btn btn-secondary" href="requests.php">Manage Join Requests</a>
            <a class="btn btn-secondary" href="team-finder.php">Browse All Projects</a>
        </div>
    </div>

    <!-- Section 1: Created Projects -->
    <div style="margin-bottom: 40px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h2 class="font-display" style="margin: 0; font-size: 1.4rem; color: var(--text-primary);">
                Projects I Created (<?= count($createdProjects) ?>)
            </h2>
        </div>

        <?php if (empty($createdProjects)): ?>
            <div class="card card-interactive" style="text-align: center; padding: 40px 20px; color: var(--muted); background: white;">
                <div style="font-size: 2.4rem; margin-bottom: 10px;">🚀</div>
                <h3 style="font-family: var(--font-display); color: var(--text-primary); margin: 0 0 8px;">No projects created yet</h3>
                <p style="margin: 0 0 18px; font-size: 0.95rem;">Have a project idea? Create your team and recruit students with the right skills.</p>
                <a class="btn btn-glow" href="create.php">+ Start a Project</a>
            </div>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($createdProjects as $project): ?>
                    <article class="card card-interactive card-peach">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                            <h3 style="margin: 0; font-family: var(--font-display); color: var(--text-primary);">
                                <?= htmlspecialchars($project["title"]) ?>
                            </h3>
                            <span class="status <?= $project["status"] === 'open' ? 'status-success' : 'status-peach' ?>">
                                <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $project["status"]))) ?>
                            </span>
                        </div>

                        <p style="color: var(--muted); line-height: 1.6; font-size: 0.95rem; margin-bottom: 16px;">
                            <?= htmlspecialchars($project["description"]) ?>
                        </p>

                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 12px; border-top: 1px solid var(--peach-200); font-size: 0.85rem; color: var(--muted);">
                            <span>Created <?= date("M j, Y", strtotime($project["created_at"])) ?></span>
                            <a class="nav-link" style="padding: 0; font-weight: 700; color: var(--primary);" href="requests.php">
                                View Requests &rarr;
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Section 2: Joined / Requested Projects -->
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h2 class="font-display" style="margin: 0; font-size: 1.4rem; color: var(--text-primary);">
                Projects I Joined or Requested (<?= count($joinedProjects) ?>)
            </h2>
        </div>

        <?php if (empty($joinedProjects)): ?>
            <div class="card card-interactive" style="text-align: center; padding: 40px 20px; color: var(--muted); background: white;">
                <div style="font-size: 2.4rem; margin-bottom: 10px;">👥</div>
                <h3 style="font-family: var(--font-display); color: var(--text-primary); margin: 0 0 8px;">No team memberships yet</h3>
                <p style="margin: 0 0 18px; font-size: 0.95rem;">Browse open projects and request to join teams that match your skills.</p>
                <a class="btn btn-secondary" href="team-finder.php">Find Open Projects</a>
            </div>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($joinedProjects as $project): ?>
                    <article class="card card-interactive">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                            <h3 style="margin: 0; font-family: var(--font-display); color: var(--text-primary);">
                                <?= htmlspecialchars($project["title"]) ?>
                            </h3>
                            <?php
                            $statusClass = 'status-warning';
                            if ($project["status"] === 'accepted') $statusClass = 'status-success';
                            if ($project["status"] === 'rejected') $statusClass = 'status-peach';
                            ?>
                            <span class="status <?= $statusClass ?>">
                                <?= htmlspecialchars(ucfirst($project["status"])) ?>
                            </span>
                        </div>

                        <p style="color: var(--muted); line-height: 1.6; font-size: 0.95rem; margin-bottom: 14px;">
                            <?= htmlspecialchars($project["description"]) ?>
                        </p>

                        <div style="font-size: 0.88rem; color: var(--text-secondary); margin-bottom: 14px;">
                            <strong>Created by:</strong> <?= htmlspecialchars($project["creator"] ?? "Peer") ?>
                        </div>

                        <div style="font-size: 0.85rem; color: var(--muted); padding-top: 10px; border-top: 1px solid var(--border);">
                            Role: <strong><?= htmlspecialchars(ucfirst($project["role"])) ?></strong> · <?= date("M j, Y", strtotime($project["joined_at"])) ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</main>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>