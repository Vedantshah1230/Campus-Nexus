<?php
// public/index.php - Main entry point for Campus Nexus

session_start();
require_once __DIR__ . '/../config/database.php';

// Campus Nexus - Student Collaboration Hub
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Nexus - Student Collaboration Hub</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <h2>Campus Nexus</h2>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="../modules/projects/">Projects</a></li>
                <li><a href="../modules/skills/">Skills</a></li>
                <li><a href="../modules/events/">Events</a></li>
                <li><a href="../modules/ideas/">Ideas</a></li>
                <li><a href="../modules/auth/login.php">Login</a></li>
            </ul>
        </nav>
    </header>

    <main class="hero-section">
        <div class="container">
            <h1>Welcome to Campus Nexus</h1>
            <p>Connect, collaborate, and innovate with peers across campus.</p>
        </div>
    </main>

    <script src="../js/main.js"></script>
</body>
</html>
