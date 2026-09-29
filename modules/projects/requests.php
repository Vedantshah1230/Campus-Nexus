<?php

session_start();

require_once "../../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION["user_id"];
$message = "";

/*
|--------------------------------------------------------------------------
| Accept / Reject request
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $projectId = (int) ($_POST["project_id"] ?? 0);
    $requesterId = (int) ($_POST["requester_id"] ?? 0);
    $action = $_POST["action"] ?? "";

    if (
        $projectId <= 0 ||
        $requesterId <= 0 ||
        !in_array($action, ["accepted", "rejected"], true)
    ) {

        $message = "Invalid request.";

    } else {

        /*
        | Make sure the logged-in user owns the project
        */

        $sql = "SELECT id
                FROM projects
                WHERE id = ?
                  AND creator_id = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "ii",
            $projectId,
            $userId
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $project = $result->fetch_assoc();

        $stmt->close();

        if (!$project) {

            $message = "You are not allowed to manage this project.";

        } else {

            /*
            | Update request
            */

            $sql = "UPDATE project_members
                    SET status = ?
                    WHERE project_id = ?
                      AND user_id = ?
                      AND status = 'pending'";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "sii",
                $action,
                $projectId,
                $requesterId
            );

            if ($stmt->execute() && $stmt->affected_rows === 1) {

                $message = "Request updated successfully.";

            } else {

                $message = "Request could not be updated.";
            }

            $stmt->close();
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
            projects.title,
            users.name,
            users.email
        FROM project_members
        JOIN projects
            ON project_members.project_id = projects.id
        JOIN users
            ON project_members.user_id = users.id
        WHERE projects.creator_id = ?
          AND project_members.status = 'pending'
        ORDER BY project_members.joined_at ASC";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $userId
);

$stmt->execute();

$result = $stmt->get_result();

$requests = [];

while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Project Requests - Campus Nexus</title>

</head>

<body>

    <h1>Project Join Requests</h1>

    <?php if ($message !== ""): ?>

        <p>
            <?= htmlspecialchars($message) ?>
        </p>

    <?php endif; ?>


    <?php if (empty($requests)): ?>

        <p>
            There are no pending join requests.
        </p>

    <?php else: ?>

        <?php foreach ($requests as $request): ?>

            <div>

                <h2>
                    <?= htmlspecialchars($request["title"]) ?>
                </h2>

                <p>
                    <strong>Student:</strong>
                    <?= htmlspecialchars($request["name"]) ?>
                </p>

                <p>
                    <strong>Email:</strong>
                    <?= htmlspecialchars($request["email"]) ?>
                </p>

                <p>
                    <strong>Requested Role:</strong>
                    <?= htmlspecialchars($request["role"]) ?>
                </p>


                <form method="POST" style="display:inline;">

                    <input
                        type="hidden"
                        name="project_id"
                        value="<?= $request["project_id"] ?>"
                    >

                    <input
                        type="hidden"
                        name="requester_id"
                        value="<?= $request["user_id"] ?>"
                    >

                    <input
                        type="hidden"
                        name="action"
                        value="accepted"
                    >

                    <button type="submit">
                        Accept
                    </button>

                </form>


                <form method="POST" style="display:inline;">

                    <input
                        type="hidden"
                        name="project_id"
                        value="<?= $request["project_id"] ?>"
                    >

                    <input
                        type="hidden"
                        name="requester_id"
                        value="<?= $request["user_id"] ?>"
                    >

                    <input
                        type="hidden"
                        name="action"
                        value="rejected"
                    >

                    <button type="submit">
                        Reject
                    </button>

                </form>

                <hr>

            </div>

        <?php endforeach; ?>

    <?php endif; ?>


    <a href="my-projects.php">
        My Projects
    </a>
     <li>
    <a href="../modules/projects/requests.php">
        Project Requests
    </a>
</li>

    <br><br>

    <a href="../../public/dashboard.php">
        Back to Dashboard
    </a>

</body>

</html>