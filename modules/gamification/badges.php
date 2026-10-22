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
| Fetch all badges and whether the current student earned each one
|--------------------------------------------------------------------------
*/
$sql = "SELECT
            badges.id,
            badges.name,
            badges.description,
            badges.points,
            badges.icon,
            user_badges.earned_at
        FROM badges
        LEFT JOIN user_badges
            ON badges.id = user_badges.badge_id
            AND user_badges.user_id = ?
        ORDER BY badges.points DESC, badges.name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$badges = [];
while ($badge = $result->fetch_assoc()) {
    $badges[] = $badge;
}
$stmt->close();

/*
|--------------------------------------------------------------------------
| Count earned badges
|--------------------------------------------------------------------------
*/
$earnedCount = 0;
foreach ($badges as $badge) {
    if ($badge["earned_at"] !== null) {
        $earnedCount++;
    }
}

$pageTitle = "My Badges & Achievements";
$breadcrumbs = [
    ["label" => "Dashboard", "url" => "../../public/dashboard.php"],
    ["label" => "Leaderboard", "url" => "leaderboard.php"],
    ["label" => "Badges"]
];

require_once __DIR__ . "/../../includes/header.php";

?>

<main class="container">

    <div class="page-header">
        <div>
            <h1 class="page-header-title">My Badges & Achievements</h1>
            <p class="page-header-subtitle">Unlock verified recognition tokens by creating projects, sharing skills, and attending events.</p>
        </div>
        <div class="page-header-actions">
            <div class="card card-interactive" style="padding: 10px 18px; display: flex; align-items: center; gap: 10px; background: white;">
                <span class="stat-label" style="margin: 0;">Unlocked</span>
                <span class="stat-value" style="font-size: 1.3rem; margin: 0; color: var(--primary);">
                    🏆 <?= $earnedCount ?> / <?= count($badges) ?>
                </span>
            </div>
            <a class="btn btn-secondary" href="leaderboard.php">View Leaderboard</a>
        </div>
    </div>

    <div class="card-grid">
        <?php foreach ($badges as $badge): ?>
            <?php $isEarned = ($badge["earned_at"] !== null); ?>
            <article class="card card-interactive <?= $isEarned ? 'card-peach' : '' ?>" style="<?= $isEarned ? 'border: 2px solid var(--peach-300);' : 'opacity: 0.8; background: #fafafa;' ?>">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 2rem;"><?= $isEarned ? '🏅' : '🔒' ?></span>
                        <h3 style="margin: 0; font-family: var(--font-display); font-size: 1.2rem; color: var(--text-primary);">
                            <?= htmlspecialchars($badge["name"]) ?>
                        </h3>
                    </div>

                    <span class="status status-peach" style="white-space: nowrap; font-weight: 700;">
                        +<?= (int) $badge["points"] ?> pts
                    </span>
                </div>

                <p style="color: var(--muted); font-size: 0.92rem; line-height: 1.6; margin-bottom: 20px;">
                    <?= htmlspecialchars($badge["description"] ?? "") ?>
                </p>

                <div style="margin-top: auto; padding-top: 12px; border-top: 1px solid var(--border); font-size: 0.85rem;">
                    <?php if ($isEarned): ?>
                        <span class="status status-success" style="font-weight: 700;">
                            ✓ Unlocked on <?= date("M j, Y", strtotime($badge["earned_at"])) ?>
                        </span>
                    <?php else: ?>
                        <span class="status" style="background: var(--surface-muted); color: var(--muted);">
                            Locked · Complete action to unlock
                        </span>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

</main>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>