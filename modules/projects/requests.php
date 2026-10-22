<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";
require_once "../gamification/helpers.php";

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
| Accept / Reject request
|--------------------------------------------------------------------------
*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $submittedToken = $_POST["csrf_token"] ?? "";
    if (!hash_equals($_SESSION["csrf_token"], $submittedToken)) {
        $message = "Invalid security token. Please try again.";
        $messageType = "error";
    } else {

        $projectId = (int) ($_POST["project_id"] ?? 0);
        $requesterId = (int) ($_POST["requester_id"] ?? 0);
        $action = $_POST["action"] ?? "";

        if ($projectId <= 0 || $requesterId <= 0 || !in_array($action, ["accepted", "rejected"], true)) {
            $message = "Invalid action requested.";
            $messageType = "error";
        } else {

            // Make sure the logged-in user owns the project
            $sql = "SELECT id, title FROM projects WHERE id = ? AND creator_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $projectId, $userId);
            $stmt->execute();
            $project = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$project) {
                $message = "You are not authorized to manage requests for this project.";
                $messageType = "error";
            } else {

                // Update request
                $sql = "UPDATE project_members
                        SET status = ?
                        WHERE project_id = ?
                          AND user_id = ?
                          AND status = 'pending'";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sii", $action, $projectId, $requesterId);

                if ($stmt->execute() && $stmt->affected_rows === 1) {
                    $stmt->close();
                    $message = "Team join request " . ($action === "accepted" ? "accepted" : "rejected") . " successfully.";
                    $messageType = "success";

                    if ($action === "accepted") {
                        // Badge ID 2 = Team Builder (30 points awarded to requester)
                        $badgeAdded = awardBadge($conn, $requesterId, 2);
                        if ($badgeAdded) {
                            awardPoints($conn, $requesterId, 30);
                        }
                    }
                } else {
                    $stmt->close();
                    $message = "Request could not be updated or was already processed.";
                    $messageType = "error";
                }
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| Get pending requests for leader's projects
|--------------------------------------------------------------------------
*/
$sql = "SELECT
            project_members.project_id,
            project_members.user_id,
            project_members.role,
            project_members.status,
            project_members.joined_at,
            projects.title,
            users.name,
            users.email
        FROM project_members
        JOIN projects ON project_members.project_id = projects.id
        JOIN users ON project_members.user_id = users.id
        WHERE projects.creator_id = ?
          AND project_members.status = 'pending'
        ORDER BY project_members.joined_at ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$requests = [];
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}
$stmt->close();

$pageTitle = "Project Join Requests";
$breadcrumbs = [
    ["label" => "Dashboard", "url" => "../../public/dashboard.php"],
    ["label" => "Projects", "url" => "team-finder.php"],
    ["label" => "My Projects", "url" => "my-projects.php"],
    ["label" => "Join Requests"]
];

require_once __DIR__ . "/../../includes/header.php";

?>

<main class="container">

    <div class="page-header">
        <div>
            <h1 class="page-header-title">Project Join Requests</h1>
            <p class="page-header-subtitle">Review peer applications and accept students into your project team workspaces.</p>
        </div>
        <div class="page-header-actions">
            <a class="btn btn-secondary" href="my-projects.php">My Projects</a>
            <a class="btn btn-glow" href="create.php">+ Create Project</a>
        </div>
    </div>

    <?php if ($message !== ""): ?>
        <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'error' ?>" role="alert" style="margin-bottom: 24px;">
            <span class="alert-icon" aria-hidden="true"><?= $messageType === 'success' ? '✓' : '✕' ?></span>
            <div class="alert-content"><?= htmlspecialchars($message) ?></div>
        </div>
    <?php endif; ?>

    <?php if (empty($requests)): ?>
        <div class="card card-interactive" style="text-align: center; padding: 48px 24px; color: var(--muted); background: white;">
            <div style="font-size: 2.5rem; margin-bottom: 12px;">📬</div>
            <h3 style="font-family: var(--font-display); color: var(--text-primary); margin: 0 0 8px;">No Pending Requests</h3>
            <p style="margin: 0; font-size: 0.95rem;">You have reviewed all incoming student requests for your open projects.</p>
        </div>
    <?php else: ?>
        <div class="card-grid">
            <?php foreach ($requests as $request): ?>
                <article class="card card-interactive card-peach">
                    <span class="kicker-label kicker-peach" style="margin-bottom: 10px;">
                        Application for Team
                    </span>

                    <h3 style="margin: 0 0 10px; font-family: var(--font-display); color: var(--text-primary);">
                        <?= htmlspecialchars($request["title"]) ?>
                    </h3>

                    <div style="background: white; border: 1px solid var(--peach-200); border-radius: var(--radius-sm); padding: 14px; margin-bottom: 16px;">
                        <p style="margin: 0 0 6px; font-size: 0.95rem;">
                            <strong>Applicant:</strong> <?= htmlspecialchars($request["name"]) ?>
                        </p>
                        <p style="margin: 0 0 6px; font-size: 0.9rem; color: var(--muted);">
                            <strong>Email:</strong> <?= htmlspecialchars($request["email"]) ?>
                        </p>
                        <p style="margin: 0; font-size: 0.88rem; color: var(--muted);">
                            <strong>Requested:</strong> <?= date("M j, Y - g:i A", strtotime($request["joined_at"])) ?>
                        </p>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: auto;">
                        <form method="POST" action="requests.php" style="margin: 0; flex: 1;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">
                            <input type="hidden" name="project_id" value="<?= (int) $request["project_id"] ?>">
                            <input type="hidden" name="requester_id" value="<?= (int) $request["user_id"] ?>">
                            <input type="hidden" name="action" value="accepted">
                            <button type="submit" class="btn btn-glow" style="width: 100%; padding: 9px 12px; font-size: 0.9rem;">
                                ✓ Accept Member
                            </button>
                        </form>

                        <form method="POST" action="requests.php" style="margin: 0; flex: 1;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">
                            <input type="hidden" name="project_id" value="<?= (int) $request["project_id"] ?>">
                            <input type="hidden" name="requester_id" value="<?= (int) $request["user_id"] ?>">
                            <input type="hidden" name="action" value="rejected">
                            <button type="submit" class="btn btn-secondary" style="width: 100%; padding: 9px 12px; font-size: 0.9rem; color: var(--danger); border-color: #fecaca;">
                                ✕ Reject
                            </button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>