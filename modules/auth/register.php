<?php

require_once "../../config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if ($name === "" || $email === "" || $password === "") {
        $message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
    } else {

        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $sql = "INSERT INTO users (name, email, password)
                VALUES (?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "sss",
            $name,
            $email,
            $hashedPassword
        );

        if ($stmt->execute()) {
            $message = "Registration successful!";
        } else {
            if ($conn->errno === 1062) {
                $message = "Email already registered.";
            } else {
                $message = "Registration failed.";
            }
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Nexus - Register</title>
</head>

<body>

    <h1>Create Campus Nexus Account</h1>

    <?php if ($message !== ""): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST">

        <label>Name</label>
        <br>
        <input
            type="text"
            name="name"
            required
        >

        <br><br>

        <label>Email</label>
        <br>
        <input
            type="email"
            name="email"
            required
        >

        <br><br>

        <label>Password</label>
        <br>
        <input
            type="password"
            name="password"
            required
        >

        <br><br>

        <button type="submit">
            Register
        </button>

    </form>

</body>
</html>