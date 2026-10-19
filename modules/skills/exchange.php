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
| Create skill exchange offer
|--------------------------------------------------------------------------
*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $submittedToken = $_POST["csrf_token"] ?? "";
    if (!hash_equals($_SESSION["csrf_token"], $submittedToken)) {
        $message = "Invalid security session. Please try again.";
        $messageType = "error";
    } else {

        $skillId = (int) ($_POST["skill_id"] ?? 0);
        $description = trim($_POST["description"] ?? "");

        if ($skillId <= 0 || $description === "") {
            $message = "Please select a skill and describe what you can teach.";
            $messageType = "error";
        } else {

            $sql = "INSERT INTO skill_exchanges (user_id, skill_id, description, status)
                    VALUES (?, ?, ?, 'open')";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iis", $userId, $skillId, $description);

            if ($stmt->execute()) {
                $message = "Your skill exchange offer was posted successfully.";
                $messageType = "success";
            } else {
                $message = "Failed to post skill exchange offer.";
                $messageType = "error";
            }

            $stmt->close();
        }
    }
}

/*
|--------------------------------------------------------------------------
| Get catalog skills
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
| Get open skill exchanges
|--------------------------------------------------------------------------
*/
$sql = "SELECT
            skill_exchanges.id,
            skill_exchanges.user_id AS owner_id,
            skill_exchanges.description,
            skill_exchanges.created_at,
            skills.name AS skill_name,
            skills.category,
            users.name AS owner_name
        FROM skill_exchanges
        JOIN skills ON skill_exchanges.skill_id = skills.id
        JOIN users ON skill_exchanges.user_id = users.id
        WHERE skill_exchanges.status = 'open'
        ORDER BY skill_exchanges.created_at DESC";

$result = $conn->query($sql);
$exchanges = [];
while ($row = $result->fetch_assoc()) {
    $exchanges[] = $row;
}

$pageTitle = "Skill Barter Exchange";
$breadcrumbs = [
    ["label" => "Dashboard", "url" => "../../public/dashboard.php"],
    ["label" => "Skill Exchange"]
];

require_once __DIR__ . "/../../includes/header.php";

?>

<main class="container">

    <div class="page-header">
        <div>
            <h1 class="page-header-title">Skill Barter Exchange</h1>
            <p class="page-header-subtitle">Teach what you know, request peer mentoring, and expand your technical repertoire.</p>
        </div>
        <div class="page-header-actions">
            <a class="btn btn-secondary" href="requests.php">Exchange Requests</a>
            <a class="btn btn-secondary" href="../students/skills.php">My Profile Skills</a>
        </div>
    </div>

    <?php if ($message !== ""): ?>
        <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'error' ?>" role="alert" style="margin-bottom: 24px;">
            <span class="alert-icon" aria-hidden="true"><?= $messageType === 'success' ? '✓' : '✕' ?></span>
            <div class="alert-content"><?= htmlspecialchars($message) ?></div>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1.8fr; gap: 28px; align-items: start;">

        <!-- Left Column: Post Skill Offer -->
        <div class="card card-interactive card-peach" style="padding: 28px;">
            <h2 class="font-display" style="margin-top: 0; font-size: 1.35rem; color: var(--text-primary); margin-bottom: 16px;">
                ✦ Post Skill Offer
            </h2>

            <form method="POST" action="exchange.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">

                <div style="margin-bottom: 18px;">
                    <label for="skill_id" style="display: block; font-weight: 600; margin-bottom: 6px;">Skill You Can Teach</label>
                    <select id="skill_id" name="skill_id" required>
                        <option value="">-- Select Skill --</option>
                        <?php foreach ($skills as $skill): ?>
                            <option value="<?= (int) $skill["id"] ?>">
                                <?= htmlspecialchars($skill["name"]) ?> (<?= htmlspecialchars($skill["category"] ?? "General") ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="margin-bottom: 22px;">
                    <label for="description" style="display: block; font-weight: 600; margin-bottom: 6px;">What You Can Teach / Help With</label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        placeholder="e.g. Can teach React hooks, state management, SQL query writing, or debugging..."
                        required
                    ></textarea>
                </div>

                <button type="submit" class="btn btn-glow" style="width: 100%;">
                    Publish Skill Offer
                </button>
            </form>
        </div>

        <!-- Right Column: Active Community Offers -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
                <h2 class="font-display" style="margin: 0; font-size: 1.35rem; color: var(--text-primary);">
                    Available Community Offers (<?= count($exchanges) ?>)
                </h2>
                <span class="status status-peach">Peer Learning</span>
            </div>

            <?php if (empty($exchanges)): ?>
                <div class="card card-interactive" style="text-align: center; padding: 48px 20px; color: var(--muted); background: white;">
                    <div style="font-size: 2.5rem; margin-bottom: 10px;">🤝</div>
                    <h3 style="font-family: var(--font-display); color: var(--text-primary); margin: 0 0 6px;">No Active Offers Yet</h3>
                    <p style="margin: 0;">Be the first student to publish a skill offer using the form on the left!</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <?php foreach ($exchanges as $exchange): ?>
                        <article class="card card-interactive" style="background: white;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                <h3 style="margin: 0; font-family: var(--font-display); color: var(--text-primary); font-size: 1.25rem;">
                                    <?= htmlspecialchars($exchange["skill_name"]) ?>
                                </h3>
                                <span class="status status-peach" style="font-size: 0.8rem;">
                                    <?= htmlspecialchars($exchange["category"] ?? "General") ?>
                                </span>
                            </div>

                            <p style="color: var(--text-secondary); line-height: 1.6; font-size: 0.95rem; margin-bottom: 16px;">
                                <?= htmlspecialchars($exchange["description"]) ?>
                            </p>

                            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid var(--border); font-size: 0.88rem; color: var(--muted);">
                                <span>
                                    Offered by: <strong><?= htmlspecialchars($exchange["owner_name"]) ?></strong> · <?= date("M j, Y", strtotime($exchange["created_at"])) ?>
                                </span>

                                <?php if ((int) $exchange["owner_id"] !== $userId): ?>
                                    <form method="POST" action="request.php" style="margin: 0;">
                                        <input type="hidden" name="exchange_id" value="<?= (int) $exchange["id"] ?>">
                                        <button type="submit" class="btn btn-sm btn-glow">
                                            Request Mentorship &rarr;
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="status" style="background: var(--surface-muted); color: var(--muted);">
                                        Your Offer
                                    </span>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

</main>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>