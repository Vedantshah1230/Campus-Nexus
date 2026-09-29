<?php

session_start();

require_once "../../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION["user_id"];

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $department = trim($_POST["department"]);
    $semester = (int) $_POST["semester"];
    $bio = trim($_POST["bio"]);

    if ($department === "" || $semester < 1 || $semester > 12) {

        $message = "Please enter valid profile information.";

    } else {

        $sql = "INSERT INTO student_profiles
                    (user_id, department, semester, bio)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    department = VALUES(department),
                    semester = VALUES(semester),
                    bio = VALUES(bio)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "isis",
            $userId,
            $department,
            $semester,
            $bio
        );

        if ($stmt->execute()) {
            $message = "Profile updated successfully.";
        } else {
            $message = "Failed to update profile.";
        }

        $stmt->close();
    }
}

$sql = "SELECT department, semester, bio
        FROM student_profiles
        WHERE user_id = ?";

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

    <title>Edit Profile - Campus Nexus</title>

</head>

<body>

    <h1>Edit Profile</h1>

    <?php if ($message !== ""): ?>

        <p>
            <?= htmlspecialchars($message) ?>
        </p>

    <?php endif; ?>

    <form method="POST">

        <label>Department</label>
        <br>

        <input
            type="text"
            name="department"
            value="<?= htmlspecialchars($profile["department"] ?? "") ?>"
            required
        >

        <br><br>

        <label>Semester</label>
        <br>

        <input
            type="number"
            name="semester"
            min="1"
            max="12"
            value="<?= htmlspecialchars($profile["semester"] ?? "") ?>"
            required
        >

        <br><br>

        <label>Bio</label>
        <br>

        <textarea
            name="bio"
            rows="5"
            cols="40"
        ><?= htmlspecialchars($profile["bio"] ?? "") ?></textarea>

        <br><br>

        <button type="submit">
            Save Profile
        </button>

    </form>

    <br>

    <a href="profile.php">
        View Profile
    </a>

</body>

</html>