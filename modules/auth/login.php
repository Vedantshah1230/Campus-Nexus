<?php

session_start();

require_once "../../config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if ($email === "" || $password === "") {
        $message = "Email and password are required.";
    } else {

        $sql = "SELECT id, name, email, password, role
                FROM users
                WHERE email = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {

                session_regenerate_id(true);

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_name"] = $user["name"];
                $_SESSION["user_email"] = $user["email"];
                $_SESSION["user_role"] = $user["role"];

                header("Location: ../../public/dashboard.php");
                exit;

            } else {
                $message = "Invalid email or password.";
            }

        } else {
            $message = "Invalid email or password.";
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

    <title>Campus Nexus - Login</title>
</head>

<body>

    <h1>Campus Nexus Login</h1>

    <?php if ($message !== ""): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST">

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
            Login
        </button>

    </form>

</body>

</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Campus Nexus</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>

<body class="auth-page">

    <main class="auth-card">

        <a class="auth-brand" href="../../public/index.php">
            Campus Nexus
        </a>

        <h1>Welcome back</h1>

        <p class="auth-subtitle">
            Sign in to connect, collaborate, and create.
        </p>

        <?php if ($message !== ""): ?>
            <p class="form-message" role="alert">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="">

            <label for="email">Email address</label>
            <input
                type="email"
                id="email"
                name="email"
                autocomplete="email"
                required
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                autocomplete="current-password"
                required
            >

            <button type="submit">Sign In</button>

        </form>

        <p class="auth-footer">
            Don't have an account?
            <a href="register.php">Create one</a>
        </p>

    </main>

</body>
</html>
