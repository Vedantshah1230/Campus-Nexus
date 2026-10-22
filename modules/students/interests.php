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
| Save interests
|--------------------------------------------------------------------------
*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $submittedToken = $_POST["csrf_token"] ?? "";
    if (!hash_equals($_SESSION["csrf_token"], $submittedToken)) {
        $message = "Invalid security token. Please try again.";
        $messageType = "error";
    } else {

        $selectedInterests = $_POST["interests"] ?? [];

        if (empty($selectedInterests)) {
            $message = "Please select at least one interest to help peers discover you.";
            $messageType = "error";
        } else {

            $conn->begin_transaction();

            try {
                // Delete previous selections
                $sql = "DELETE FROM user_interests WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $stmt->close();

                // Insert selected
                $sql = "INSERT INTO user_interests (user_id, interest_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);

                foreach ($selectedInterests as $interestId) {
                    $intId = (int) $interestId;
                    if ($intId > 0) {
                        $stmt->bind_param("ii", $userId, $intId);
                        $stmt->execute();
                    }
                }

                $stmt->close();
                $conn->commit();

                $message = "Your collaboration interests were saved successfully.";
                $messageType = "success";

            } catch (Exception $e) {
                $conn->rollback();
                $message = "Failed to update interests. Please try again.";
                $messageType = "error";
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| Get all available interests catalog
|--------------------------------------------------------------------------
*/
$sql = "SELECT id, name FROM interests ORDER BY name ASC";
$result = $conn->query($sql);
$interests = [];
while ($row = $result->fetch_assoc()) {
    $interests[] = $row;
}

/*
|--------------------------------------------------------------------------
| Get current student's selected interests
|--------------------------------------------------------------------------
*/
$sql = "SELECT interest_id FROM user_interests WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$currentInterests = [];
while ($row = $result->fetch_assoc()) {
    $currentInterests[] = (int) $row["interest_id"];
}
$stmt->close();

$pageTitle = "My Interests";
$breadcrumbs = [
    ["label" => "Dashboard", "url" => "../../public/dashboard.php"],
    ["label" => "Profile", "url" => "profile.php"],
    ["label" => "Interests"]
];

require_once __DIR__ . "/../../includes/header.php";

?>

<main class="container">

    <div class="page-header">
        <div>
            <h1 class="page-header-title">Collaboration Interests</h1>
            <p class="page-header-subtitle">Select fields and domains you are passionate about exploring with peers.</p>
        </div>
        <div class="page-header-actions">
            <a class="btn btn-secondary" href="profile.php">Back to Profile</a>
            <a class="btn btn-secondary" href="skills.php">Manage Skills</a>
        </div>
    </div>

    <?php if ($message !== ""): ?>
        <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'error' ?>" role="alert" style="margin-bottom: 24px;">
            <span class="alert-icon" aria-hidden="true"><?= $messageType === 'success' ? '✓' : '✕' ?></span>
            <div class="alert-content"><?= htmlspecialchars($message) ?></div>
        </div>
    <?php endif; ?>

    <div class="card card-interactive" style="max-width: 800px; margin: 0 auto; background: white; padding: 32px;">

        <form method="POST" action="interests.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">

            <p style="color: var(--muted); margin-top: 0; margin-bottom: 20px;">
                Check all the topics that interest you. These appear as tags on your profile card.
            </p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-bottom: 28px;">
                <?php foreach ($interests as $interest): ?>
                    <?php $isChecked = in_array((int) $interest["id"], $currentInterests, true); ?>
                    <label style="display: flex; align-items: center; gap: 10px; padding: 12px 14px; border: 1px solid <?= $isChecked ? 'var(--primary)' : 'var(--border)' ?>; background: <?= $isChecked ? 'var(--primary-light)' : 'var(--surface)' ?>; border-radius: var(--radius-sm); cursor: pointer; transition: all var(--transition-fast);">
                        <input
                            type="checkbox"
                            name="interests[]"
                            value="<?= (int) $interest["id"] ?>"
                            <?= $isChecked ? "checked" : "" ?>
                            style="width: 18px; height: 18px; accent-color: var(--primary);"
                        >
                        <span style="font-weight: <?= $isChecked ? '700' : '500' ?>; color: var(--text-primary); font-size: 0.95rem;">
                            <?= htmlspecialchars($interest["name"]) ?>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>

            <div style="display: flex; gap: 12px; align-items: center;">
                <button type="submit" class="btn btn-glow">
                    Save Selected Interests
                </button>
                <a class="btn btn-secondary" href="profile.php">
                    Cancel
                </a>
            </div>

        </form>

    </div>

</main>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>