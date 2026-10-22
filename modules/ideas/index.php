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
| Create idea
|--------------------------------------------------------------------------
*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $submittedToken = $_POST["csrf_token"] ?? "";
    if (!hash_equals($_SESSION["csrf_token"], $submittedToken)) {
        $message = "Invalid security token. Please try again.";
        $messageType = "error";
    } else {

        $title = trim($_POST["title"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $category = trim($_POST["category"] ?? "");

        if ($title === "" || $description === "" || $category === "") {
            $message = "All idea proposal fields are required.";
            $messageType = "error";
        } else {

            $sql = "INSERT INTO ideas (user_id, title, description, category, status)
                    VALUES (?, ?, ?, ?, 'active')";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isss", $userId, $title, $description, $category);

            if ($stmt->execute()) {
                $stmt->close();
                $message = "Your idea proposal has been published to the campus board!";
                $messageType = "success";

                // Check whether this is the user's first idea
                $countSql = "SELECT COUNT(*) AS idea_count FROM ideas WHERE user_id = ?";
                $countStmt = $conn->prepare($countSql);
                $countStmt->bind_param("i", $userId);
                $countStmt->execute();
                $countRow = $countStmt->get_result()->fetch_assoc();
                $countStmt->close();

                if ((int) $countRow["idea_count"] === 1) {
                    // Badge ID 4 = Idea Contributor (20 points)
                    $badgeAdded = awardBadge($conn, $userId, 4);
                    if ($badgeAdded) {
                        awardPoints($conn, $userId, 20);
                    }
                }
            } else {
                $stmt->close();
                $message = "Failed to post idea. Please try again.";
                $messageType = "error";
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| Search & Filter Parameters
|--------------------------------------------------------------------------
*/
$search = trim($_GET["search"] ?? "");
$sort = $_GET["sort"] ?? "votes";

$orderBy = "vote_count DESC, ideas.created_at DESC";
if ($sort === "newest") {
    $orderBy = "ideas.created_at DESC";
}

$whereClause = "WHERE ideas.status != 'archived'";
$params = [$userId];
$types = "i";

if ($search !== "") {
    $whereClause .= " AND (ideas.title LIKE ? OR ideas.category LIKE ? OR ideas.description LIKE ?)";
    $like = "%" . $search . "%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "sss";
}

$sql = "SELECT
            ideas.id,
            ideas.user_id,
            ideas.title,
            ideas.description,
            ideas.category,
            ideas.status,
            ideas.created_at,
            users.name AS creator,
            COUNT(DISTINCT idea_votes.user_id) AS vote_count,
            MAX(CASE WHEN my_vote.user_id IS NOT NULL THEN 1 ELSE 0 END) AS has_voted
        FROM ideas
        JOIN users ON ideas.user_id = users.id
        LEFT JOIN idea_votes ON ideas.id = idea_votes.idea_id
        LEFT JOIN idea_votes AS my_vote ON ideas.id = my_vote.idea_id AND my_vote.user_id = ?
        $whereClause
        GROUP BY
            ideas.id,
            ideas.user_id,
            ideas.title,
            ideas.description,
            ideas.category,
            ideas.status,
            ideas.created_at,
            users.name
        ORDER BY $orderBy";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$ideas = [];
while ($row = $result->fetch_assoc()) {
    $ideas[] = $row;
}
$stmt->close();

$pageTitle = "Campus Idea Board";
$breadcrumbs = [
    ["label" => "Dashboard", "url" => "../../public/dashboard.php"],
    ["label" => "Idea Board"]
];

require_once __DIR__ . "/../../includes/header.php";

?>

<main class="container">

    <div class="page-header">
        <div>
            <h1 class="page-header-title">Campus Idea Board</h1>
            <p class="page-header-subtitle">Share project concepts, explore community proposals, and vote for ideas you want to see built.</p>
        </div>
        <div class="page-header-actions">
            <a class="btn btn-glow" href="#new-idea">+ Submit Idea</a>
            <a class="btn btn-secondary" href="../projects/team-finder.php">Browse Projects</a>
        </div>
    </div>

    <?php if ($message !== ""): ?>
        <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'error' ?>" role="alert" style="margin-bottom: 24px;">
            <span class="alert-icon" aria-hidden="true"><?= $messageType === 'success' ? '✓' : '✕' ?></span>
            <div class="alert-content"><?= htmlspecialchars($message) ?></div>
        </div>
    <?php endif; ?>

    <!-- Search & Filter Controls -->
    <div class="card card-interactive" style="margin-bottom: 28px; padding: 18px 24px; background: white;">
        <form method="GET" action="index.php" style="margin: 0; display: flex; gap: 14px; flex-wrap: wrap; align-items: center;">
            <div style="flex: 2; min-width: 240px;">
                <input
                    type="text"
                    name="search"
                    value="<?= htmlspecialchars($search) ?>"
                    placeholder="Search ideas by title, keyword, or category..."
                    style="margin: 0;"
                >
            </div>

            <div style="flex: 1; min-width: 170px;">
                <select name="sort" style="margin: 0;">
                    <option value="votes" <?= $sort === 'votes' ? 'selected' : '' ?>>Most Upvoted</option>
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                </select>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-glow" style="padding: 10px 18px;">
                    Search
                </button>
                <?php if ($search !== "" || $sort !== "votes"): ?>
                    <a class="btn btn-secondary" href="index.php" style="padding: 10px 14px;">
                        Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1.8fr; gap: 28px; align-items: start;">

        <!-- Left Column: Post Idea -->
        <div id="new-idea" class="card card-interactive card-peach" style="padding: 28px;">
            <h2 class="font-display" style="margin-top: 0; font-size: 1.35rem; color: var(--text-primary); margin-bottom: 16px;">
                ✦ Propose a Project Idea
            </h2>

            <form method="POST" action="index.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">

                <div style="margin-bottom: 16px;">
                    <label for="title" style="display: block; font-weight: 600; margin-bottom: 6px;">Idea Title</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        maxlength="150"
                        placeholder="e.g. Campus Peer Tutoring Portal"
                        required
                    >
                </div>

                <div style="margin-bottom: 16px;">
                    <label for="category" style="display: block; font-weight: 600; margin-bottom: 6px;">Category</label>
                    <input
                        type="text"
                        id="category"
                        name="category"
                        maxlength="100"
                        placeholder="e.g. AI / Machine Learning, Campus Utility, Web"
                        required
                    >
                </div>

                <div style="margin-bottom: 22px;">
                    <label for="description" style="display: block; font-weight: 600; margin-bottom: 6px;">Description & Goals</label>
                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                        placeholder="Describe the problem, your solution, and the skills needed..."
                        required
                    ></textarea>
                </div>

                <button type="submit" class="btn btn-glow" style="width: 100%;">
                    Submit Proposal (+20 pts)
                </button>
            </form>
        </div>

        <!-- Right Column: Community Ideas -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
                <h2 class="font-display" style="margin: 0; font-size: 1.35rem; color: var(--text-primary);">
                    Active Proposals (<?= count($ideas) ?>)
                </h2>
                <span class="status status-peach">Community Voted</span>
            </div>

            <?php if (empty($ideas)): ?>
                <div class="card card-interactive" style="text-align: center; padding: 48px 20px; color: var(--muted); background: white;">
                    <div style="font-size: 2.5rem; margin-bottom: 10px;">💡</div>
                    <h3 style="font-family: var(--font-display); color: var(--text-primary); margin: 0 0 6px;">No Matching Ideas Found</h3>
                    <p style="margin: 0;">Try adjusting your search terms or be the first to submit a proposal using the form.</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 18px;">
                    <?php foreach ($ideas as $idea): ?>
                        <article class="card card-interactive" style="background: white;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 14px; margin-bottom: 8px;">
                                <h3 style="margin: 0; font-family: var(--font-display); font-size: 1.25rem; color: var(--text-primary);">
                                    <?= htmlspecialchars($idea["title"]) ?>
                                </h3>
                                <span class="status <?= (int)$idea["vote_count"] > 0 ? 'status-peach' : '' ?>" style="white-space: nowrap; font-weight: 700;">
                                    ▲ <?= (int) $idea["vote_count"] ?> <?= ((int)$idea["vote_count"] === 1) ? 'vote' : 'votes' ?>
                                </span>
                            </div>

                            <p style="color: var(--text-secondary); line-height: 1.6; font-size: 0.95rem; margin-bottom: 16px;">
                                <?= nl2br(htmlspecialchars($idea["description"])) ?>
                            </p>

                            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 14px; border-top: 1px solid var(--border); font-size: 0.88rem; color: var(--muted);">
                                <div>
                                    <span class="status status-peach" style="margin-right: 8px; font-size: 0.78rem;">
                                        <?= htmlspecialchars($idea["category"]) ?>
                                    </span>
                                    <span>By <strong><?= htmlspecialchars($idea["creator"]) ?></strong></span>
                                </div>

                                <div>
                                    <?php if ((int) $idea["user_id"] === $userId): ?>
                                        <span class="status" style="background: var(--surface-muted); color: var(--muted);">
                                            Your Proposal
                                        </span>
                                    <?php elseif ((int) $idea["has_voted"] === 1): ?>
                                        <span class="status status-success" style="font-weight: 700;">
                                            ✓ Upvoted
                                        </span>
                                    <?php else: ?>
                                        <form method="POST" action="vote.php" style="margin: 0;">
                                            <input type="hidden" name="idea_id" value="<?= (int) $idea["id"] ?>">
                                            <button type="submit" class="btn btn-sm btn-glow">
                                                ▲ Upvote
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

</main>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>