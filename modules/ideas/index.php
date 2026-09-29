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
| Create idea
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $category = trim($_POST["category"] ?? "");

    if (
        $title === "" ||
        $description === "" ||
        $category === ""
    ) {

        $message = "All idea fields are required.";

    } else {

        $sql = "INSERT INTO ideas
                    (user_id, title, description, category)
                VALUES (?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "isss",
            $userId,
            $title,
            $description,
            $category
        );

        if ($stmt->execute()) {

            $message = "Idea posted successfully.";

        } else {

            $message = "Failed to post idea.";
        }

        $stmt->close();
    }
}

/*
|--------------------------------------------------------------------------
| Get ideas with vote counts
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            ideas.id,
            ideas.title,
            ideas.description,
            ideas.category,
            ideas.status,
            ideas.created_at,
            users.name AS creator,
            COUNT(idea_votes.user_id) AS vote_count
        FROM ideas
        JOIN users
            ON ideas.user_id = users.id
        LEFT JOIN idea_votes
            ON ideas.id = idea_votes.idea_id
        WHERE ideas.status != 'archived'
        GROUP BY
            ideas.id,
            ideas.title,
            ideas.description,
            ideas.category,
            ideas.status,
            ideas.created_at,
            users.name
        ORDER BY vote_count DESC, ideas.created_at DESC";

$result = $conn->query($sql);

$ideas = [];

while ($row = $result->fetch_assoc()) {
    $ideas[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Idea Board - Campus Nexus</title>

</head>

<body>

    <h1>Campus Idea Board</h1>

    <p>
        Share ideas and discover what other students are building.
    </p>


    <?php if ($message !== ""): ?>

        <p>
            <?= htmlspecialchars($message) ?>
        </p>

    <?php endif; ?>


    <hr>


    <h2>Post a New Idea</h2>

    <form method="POST">

        <label>
            Idea Title
        </label>

        <br>

        <input
            type="text"
            name="title"
            maxlength="150"
            required
        >

        <br><br>


        <label>
            Category
        </label>

        <br>

        <input
            type="text"
            name="category"
            maxlength="100"
            placeholder="e.g. Campus Utility"
            required
        >

        <br><br>


        <label>
            Description
        </label>

        <br>

        <textarea
            name="description"
            rows="6"
            cols="50"
            required
        ></textarea>

        <br><br>

        <button type="submit">
            Post Idea
        </button>

    </form>


    <hr>


    <h2>Ideas</h2>

    <?php if (empty($ideas)): ?>

        <p>
            No ideas have been posted yet.
        </p>

    <?php else: ?>

        <?php foreach ($ideas as $idea): ?>

            <article>

                <h3>
                    <?= htmlspecialchars($idea["title"]) ?>
                </h3>

                <p>
                    <?= nl2br(
                        htmlspecialchars(
                            $idea["description"]
                        )
                    ) ?>
                </p>

                <p>

                    <strong>Category:</strong>

                    <?= htmlspecialchars(
                        $idea["category"]
                    ) ?>

                </p>

                <p>

                    <strong>Posted by:</strong>

                    <?= htmlspecialchars(
                        $idea["creator"]
                    ) ?>

                </p>

                <p>

                    <strong>Status:</strong>

                    <?= htmlspecialchars(
                        $idea["status"]
                    ) ?>

                </p>

                <p>

                    <strong>Votes:</strong>

                    <?= $idea["vote_count"] ?>

                </p>


                <?php if (
                    $idea["creator"]
                    !== $_SESSION["user_name"]
                ): ?>

                    <form
                        method="POST"
                        action="vote.php"
                    >

                        <input
                            type="hidden"
                            name="idea_id"
                            value="<?= $idea["id"] ?>"
                        >

                        <button type="submit">
                            Vote
                        </button>

                    </form>

                <?php endif; ?>

            </article>

            <hr>

        <?php endforeach; ?>

    <?php endif; ?>


    <a href="../../public/dashboard.php">
        Back to Dashboard
    </a>

</body>

</html>