<?php

session_start();

require_once "../../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION["user_id"];

$sql = "SELECT
            users.name,
            users.email,
            student_profiles.department,
            student_profiles.semester,
            student_profiles.bio,
            student_profiles.profile_picture
        FROM users
        LEFT JOIN student_profiles
            ON users.id = student_profiles.user_id
        WHERE users.id = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $userId);

$stmt->execute();

$result = $stmt->get_result();

$profile = $result->fetch_assoc();

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Profile - Campus Nexus</title>

</head>

<body>

    <h1>My Profile</h1>

    <p>
        <strong>Name:</strong>
        <?= htmlspecialchars($profile["name"]) ?>
    </p>

    <p>
        <strong>Email:</strong>
        <?= htmlspecialchars($profile["email"]) ?>
    </p>

    <p>
        <strong>Department:</strong>
        <?= htmlspecialchars($profile["department"] ?? "Not added") ?>
    </p>

    <p>
        <strong>Semester:</strong>
        <?= htmlspecialchars($profile["semester"] ?? "Not added") ?>
    </p>

    <p>
        <strong>Bio:</strong>
        <?= htmlspecialchars($profile["bio"] ?? "Not added") ?>
    </p>

    <br>

    <a href="edit-profile.php">
        Edit Profile
    </a>

    <br><br>

    <a href="../../public/dashboard.php">
        Back to Dashboard
    </a>

</body>

</html>