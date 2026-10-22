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
        $message = "Invalid security session. Please try again.";
        $messageType = "error";
    } else {

        $requestId = (int) ($_POST["request_id"] ?? 0);
        $action = $_POST["action"] ?? "";

        if ($requestId <= 0 || !in_array($action, ["accepted", "rejected"], true)) {
            $message = "Invalid request action.";
            $messageType = "error";
        } else {

            // Update only requests belonging to the logged-in skill owner
            $sql = "UPDATE skill_exchange_requests AS requests
                    JOIN skill_exchanges AS exchanges
                        ON requests.exchange_id = exchanges.id
                    SET requests.status = ?
                    WHERE requests.id = ?
                      AND exchanges.user_id = ?
                      AND requests.status = 'pending'";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sii", $action, $requestId, $userId);

            if ($stmt->execute() && $stmt->affected_rows === 1) {
                $stmt->close();
                $message = "Skill exchange request " . ($action === "accepted" ? "accepted" : "rejected") . " successfully.";
                $messageType = "success";

                if ($action === "accepted") {
                    // Badge ID 3 = Skill Sharer (40 points)
                    $badgeAdded = awardBadge($conn, $userId, 3);
                    if ($badgeAdded) {
                        awardPoints($conn, $userId, 40);
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

/*
|--------------------------------------------------------------------------
| Get pending requests
|--------------------------------------------------------------------------
*/
$sql = "SELECT
            skill_exchange_requests.id,
            skill_exchange_requests.exchange_id,
            skill_exchange_requests.requester_id,
            skill_exchange_requests.message,
            skill_exchange_requests.status,
            skill_exchange_requests.created_at,
            skills.name AS skill_name,
            users.name AS requester_name,
            users.email AS requester_email
        FROM skill_exchange_requests
        JOIN skill_exchanges ON skill_exchange_requests.exchange_id = skill_exchanges.id
        JOIN skills ON skill_exchanges.skill_id = skills.id
        JOIN users ON skill_exchange_requests.requester_id = users.id
        WHERE skill_exchanges.user_id = ?
          AND skill_exchange_requests.status = 'pending'
        ORDER BY skill_exchange_requests.created_at ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$requests = [];
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}
$stmt->close();

$pageTitle = "Exchange Requests";
$breadcrumbs = [
    ["label" => "Dashboard", "url" => "../../public/dashboard.php"],
    ["label" => "Skill Exchange", "url" => "exchange.php"],
    ["label" => "Requests"]
];

require_once __DIR__ . "/../../includes/header.php";

?>

<main class="container">

    <div class="page-header">
        <div>
            <h1 class="page-header-title">Skill Mentoring Requests</h1>
            <p class="page-header-subtitle">Respond to students requesting guidance for skills you offered to teach.</p>
        </div>
        <div class="page-header-actions">
            <a class="btn btn-secondary" href="exchange.php">Browse Skill Exchange</a>
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
            <h3 style="font-family: var(--font-display); color: var(--text-primary); margin: 0 0 8px;">No Pending Mentoring Requests</h3>
            <p style="margin: 0; font-size: 0.95rem;">You have reviewed all incoming peer requests for your skill listings.</p>
        </div>
    <?php else: ?>
        <div class="card-grid">
            <?php foreach ($requests as $request): ?>
                <article class="card card-interactive card-peach">
                    <span class="kicker-label kicker-peach" style="margin-bottom: 10px;">
                        Mentorship Inquiry
                    </span>

                    <h3 style="margin: 0 0 10px; font-family: var(--font-display); color: var(--text-primary);">
                        <?= htmlspecialchars($request["skill_name"]) ?>
                    </h3>

                    <div style="background: white; border: 1px solid var(--peach-200); border-radius: var(--radius-sm); padding: 14px; margin-bottom: 16px;">
                        <p style="margin: 0 0 6px; font-size: 0.95rem;">
                            <strong>Student:</strong> <?= htmlspecialchars($request["requester_name"]) ?>
                        </p>
                        <p style="margin: 0 0 8px; font-size: 0.9rem; color: var(--muted);">
                            <strong>Email:</strong> <?= htmlspecialchars($request["requester_email"]) ?>
                        </p>
                        <p style="margin: 0; font-size: 0.92rem; color: var(--text-secondary); line-height: 1.5; font-style: italic;">
                            "<?= htmlspecialchars($request["message"]) ?>"
                        </p>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: auto;">
                        <form method="POST" action="requests.php" style="margin: 0; flex: 1;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">
                            <input type="hidden" name="request_id" value="<?= (int) $request["id"] ?>">
                            <input type="hidden" name="action" value="accepted">
                            <button type="submit" class="btn btn-glow" style="width: 100%; padding: 9px 12px; font-size: 0.9rem;">
                                ✓ Accept & Connect
                            </button>
                        </form>

                        <form method="POST" action="requests.php" style="margin: 0; flex: 1;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">
                            <input type="hidden" name="request_id" value="<?= (int) $request["id"] ?>">
                            <input type="hidden" name="action" value="rejected">
                            <button type="submit" class="btn btn-secondary" style="width: 100%; padding: 9px 12px; font-size: 0.9rem; color: var(--danger); border-color: #fecaca;">
                                ✕ Decline
                            </button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>