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

    $requestId = (int) ($_POST["request_id"] ?? 0);
    $action = $_POST["action"] ?? "";

    if (
        $requestId <= 0 ||
        !in_array($action, ["accepted", "rejected"], true)
    ) {

        $message = "Invalid request.";

    } else {

        /*
        | Update only requests belonging to
        | the logged-in skill owner.
        */

        $sql = "UPDATE skill_exchange_requests AS requests
                JOIN skill_exchanges AS exchanges
                    ON requests.exchange_id = exchanges.id
                SET requests.status = ?
                WHERE requests.id = ?
                  AND exchanges.user_id = ?
                  AND requests.status = 'pending'";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "sii",
            $action,
            $requestId,
            $userId
        );

        if ($stmt->execute() && $stmt->affected_rows === 1) {

            $message = "Request updated successfully.";

        } else {

            $message = "Request could not be updated.";
        }

        $stmt->close();
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
        JOIN skill_exchanges
            ON skill_exchange_requests.exchange_id =
               skill_exchanges.id
        JOIN skills
            ON skill_exchanges.skill_id = skills.id
        JOIN users
            ON skill_exchange_requests.requester_id =
               users.id
        WHERE skill_exchanges.user_id = ?
          AND skill_exchange_requests.status = 'pending'
        ORDER BY skill_exchange_requests.created_at ASC";

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

    <title>Skill Exchange Requests - Campus Nexus</title>

</head>

<body>

    <h1>Skill Exchange Requests</h1>

    <?php if ($message !== ""): ?>

        <p>
            <?= htmlspecialchars($message) ?>
        </p>

    <?php endif; ?>


    <?php if (empty($requests)): ?>

        <p>
            You have no pending skill exchange requests.
        </p>

    <?php else: ?>

        <?php foreach ($requests as $request): ?>

            <div>

                <h2>
                    <?= htmlspecialchars(
                        $request["skill_name"]
                    ) ?>
                </h2>

                <p>
                    <strong>Student:</strong>
                    <?= htmlspecialchars(
                        $request["requester_name"]
                    ) ?>
                </p>

                <p>
                    <strong>Email:</strong>
                    <?= htmlspecialchars(
                        $request["requester_email"]
                    ) ?>
                </p>

                <p>
                    <strong>Message:</strong>
                    <?= htmlspecialchars(
                        $request["message"]
                    ) ?>
                </p>


                <form
                    method="POST"
                    style="display:inline;"
                >

                    <input
                        type="hidden"
                        name="request_id"
                        value="<?= $request["id"] ?>"
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


                <form
                    method="POST"
                    style="display:inline;"
                >

                    <input
                        type="hidden"
                        name="request_id"
                        value="<?= $request["id"] ?>"
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

            </div>

            <hr>

        <?php endforeach; ?>

    <?php endif; ?>


    <a href="exchange.php">
        Skill Exchange
    </a>

    <br><br>

    <a href="../../public/dashboard.php">
        Back to Dashboard
    </a>

</body>

</html>