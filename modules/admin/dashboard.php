<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../config/database.php";

// 1. Authentication Check
if (!isset($_SESSION["user_id"])) {
    $_SESSION["flash_error"] = "Please sign in to access institutional administration.";
    header("Location: ../../modules/auth/login.php");
    exit;
}

$currentUserId = (int) $_SESSION["user_id"];

// 2. Real-time Role Sync from Database (Allows immediate access when role is updated in DB)
$roleQuery = $conn->prepare("SELECT role, name, email FROM users WHERE id = ? LIMIT 1");
$roleQuery->bind_param("i", $currentUserId);
$roleQuery->execute();
$currentUserData = $roleQuery->get_result()->fetch_assoc();
$roleQuery->close();

if (!$currentUserData) {
    session_destroy();
    header("Location: ../../modules/auth/login.php");
    exit;
}

$_SESSION["user_role"] = $currentUserData["role"];

// 3. Authorization Guard
if ($_SESSION["user_role"] !== "admin") {
    $_SESSION["flash_error"] = "Access restricted: Administrator credentials are required to view the Admin Console.";
    header("Location: ../../public/dashboard.php");
    exit;
}

// 4. Ensure CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$activeTab = $_GET["tab"] ?? "overview";
$searchQuery = trim($_GET["q"] ?? "");

/*
|--------------------------------------------------------------------------
| POST Actions: Administration Controls
|--------------------------------------------------------------------------
*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate CSRF
    if (!hash_equals($_SESSION["csrf_token"] ?? "", $_POST["csrf_token"] ?? "")) {
        $_SESSION["flash_error"] = "Security validation failed. Please try again.";
        header("Location: dashboard.php?tab=" . urlencode($activeTab));
        exit;
    }

    $action = $_POST["action"] ?? "";

    // Action A: Toggle / Change User Role
    if ($action === "update_user_role") {
        $targetUserId = (int) ($_POST["target_user_id"] ?? 0);
        $newRole = $_POST["new_role"] ?? "";

        if ($targetUserId <= 0 || !in_array($newRole, ["student", "admin"], true)) {
            $_SESSION["flash_error"] = "Invalid user or role specified.";
        } elseif ($targetUserId === $currentUserId && $newRole !== "admin") {
            $_SESSION["flash_warning"] = "Safety Guard: You cannot revoke your own administrator privileges.";
        } else {
            $stmt = $conn->prepare("UPDATE users SET role = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->bind_param("si", $newRole, $targetUserId);
            if ($stmt->execute()) {
                $_SESSION["flash_success"] = "User role successfully changed to " . ucfirst($newRole) . ".";
            } else {
                $_SESSION["flash_error"] = "Failed to update user role: " . $conn->error;
            }
            $stmt->close();
        }
        header("Location: dashboard.php?tab=users");
        exit;
    }

    // Action B: Update Project Status
    if ($action === "update_project_status") {
        $projectId = (int) ($_POST["project_id"] ?? 0);
        $newStatus = $_POST["new_status"] ?? "";
        $allowed = ["open", "in_progress", "completed", "cancelled"];

        if ($projectId > 0 && in_array($newStatus, $allowed, true)) {
            $stmt = $conn->prepare("UPDATE projects SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->bind_param("si", $newStatus, $projectId);
            if ($stmt->execute()) {
                $_SESSION["flash_success"] = "Project status updated to " . ucfirst(str_replace('_', ' ', $newStatus)) . ".";
            } else {
                $_SESSION["flash_error"] = "Failed to update project.";
            }
            $stmt->close();
        }
        header("Location: dashboard.php?tab=projects");
        exit;
    }

    // Action C: Update Event Status
    if ($action === "update_event_status") {
        $eventId = (int) ($_POST["event_id"] ?? 0);
        $newStatus = $_POST["new_status"] ?? "";
        $allowed = ["upcoming", "ongoing", "completed", "cancelled"];

        if ($eventId > 0 && in_array($newStatus, $allowed, true)) {
            $stmt = $conn->prepare("UPDATE events SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->bind_param("si", $newStatus, $eventId);
            if ($stmt->execute()) {
                $_SESSION["flash_success"] = "Event status updated to " . ucfirst($newStatus) . ".";
            } else {
                $_SESSION["flash_error"] = "Failed to update event.";
            }
            $stmt->close();
        }
        header("Location: dashboard.php?tab=events");
        exit;
    }

    // Action D: Update Idea Status
    if ($action === "update_idea_status") {
        $ideaId = (int) ($_POST["idea_id"] ?? 0);
        $newStatus = $_POST["new_status"] ?? "";
        $allowed = ["open", "in_progress", "completed", "archived"];

        if ($ideaId > 0 && in_array($newStatus, $allowed, true)) {
            $stmt = $conn->prepare("UPDATE ideas SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->bind_param("si", $newStatus, $ideaId);
            if ($stmt->execute()) {
                $_SESSION["flash_success"] = "Idea proposal status updated.";
            } else {
                $_SESSION["flash_error"] = "Failed to update idea.";
            }
            $stmt->close();
        }
        header("Location: dashboard.php?tab=ideas");
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| System Telemetry & Statistics Queries
|--------------------------------------------------------------------------
*/
$stats = [
    "total_users" => 0,
    "total_students" => 0,
    "total_admins" => 0,
    "total_projects" => 0,
    "open_projects" => 0,
    "total_events" => 0,
    "total_registrations" => 0,
    "total_ideas" => 0,
    "total_skills" => 0,
    "total_exchanges" => 0,
    "total_audit_logs" => 0,
    "failed_logins_24h" => 0,
];

// Aggregated counts in quick queries
$userStats = $conn->query("
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) AS admins,
        SUM(CASE WHEN role = 'student' THEN 1 ELSE 0 END) AS students
    FROM users
")->fetch_assoc();
$stats["total_users"] = (int)($userStats["total"] ?? 0);
$stats["total_admins"] = (int)($userStats["admins"] ?? 0);
$stats["total_students"] = (int)($userStats["students"] ?? 0);

$projStats = $conn->query("
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) AS open_count
    FROM projects
")->fetch_assoc();
$stats["total_projects"] = (int)($projStats["total"] ?? 0);
$stats["open_projects"] = (int)($projStats["open_count"] ?? 0);

$eventStats = $conn->query("SELECT COUNT(*) AS total FROM events")->fetch_assoc();
$stats["total_events"] = (int)($eventStats["total"] ?? 0);

$regStats = $conn->query("SELECT COUNT(*) AS total FROM event_registrations WHERE status = 'registered'")->fetch_assoc();
$stats["total_registrations"] = (int)($regStats["total"] ?? 0);

$ideaStats = $conn->query("SELECT COUNT(*) AS total FROM ideas")->fetch_assoc();
$stats["total_ideas"] = (int)($ideaStats["total"] ?? 0);

$skillStats = $conn->query("SELECT COUNT(*) AS total FROM skills")->fetch_assoc();
$stats["total_skills"] = (int)($skillStats["total"] ?? 0);

$exchangeStats = $conn->query("SELECT COUNT(*) AS total FROM skill_exchanges")->fetch_assoc();
$stats["total_exchanges"] = (int)($exchangeStats["total"] ?? 0);

$authStats = $conn->query("
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN action = 'LOGIN_FAILED' AND created_at >= NOW() - INTERVAL 1 DAY THEN 1 ELSE 0 END) AS failed_24h
    FROM auth_logs
")->fetch_assoc();
$stats["total_audit_logs"] = (int)($authStats["total"] ?? 0);
$stats["failed_logins_24h"] = (int)($authStats["failed_24h"] ?? 0);

/*
|--------------------------------------------------------------------------
| Data Lists based on Active Tab
|--------------------------------------------------------------------------
*/

// Tab 1: Users List
$usersList = [];
$userSql = "
    SELECT 
        u.id, u.name, u.email, u.role, u.last_login_at, u.created_at,
        sp.department, sp.semester, sp.roll_number,
        COALESCE(up.points, 0) AS points,
        (SELECT COUNT(*) FROM user_badges WHERE user_id = u.id) AS badges_count
    FROM users u
    LEFT JOIN student_profiles sp ON u.id = sp.user_id
    LEFT JOIN user_points up ON u.id = up.user_id
";
if (!empty($searchQuery)) {
    $searchTerm = "%" . $searchQuery . "%";
    $userSql .= " WHERE u.name LIKE ? OR u.email LIKE ? OR sp.department LIKE ? OR sp.roll_number LIKE ?";
    $userSql .= " ORDER BY u.role DESC, u.created_at DESC";
    $stmt = $conn->prepare($userSql);
    $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $usersList[] = $row;
    }
    $stmt->close();
} else {
    $userSql .= " ORDER BY u.role DESC, u.created_at DESC LIMIT 100";
    $res = $conn->query($userSql);
    while ($row = $res->fetch_assoc()) {
        $usersList[] = $row;
    }
}

// Tab 2: Projects List
$projectsList = [];
$projSql = "
    SELECT 
        p.id, p.title, p.status, p.created_at,
        u.name AS creator_name, u.email AS creator_email,
        (SELECT COUNT(*) FROM project_members WHERE project_id = p.id AND status = 'accepted') AS members_count
    FROM projects p
    JOIN users u ON p.creator_id = u.id
    ORDER BY p.created_at DESC LIMIT 50
";
$pRes = $conn->query($projSql);
if ($pRes) {
    while ($r = $pRes->fetch_assoc()) {
        $projectsList[] = $r;
    }
}

// Tab 3: Events List
$eventsList = [];
$eventsSql = "
    SELECT 
        e.id, e.title, e.event_date, e.location, e.max_participants, e.status, e.created_at,
        u.name AS creator_name,
        (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id AND status = 'registered') AS registered_count
    FROM events e
    JOIN users u ON e.creator_id = u.id
    ORDER BY e.event_date DESC LIMIT 50
";
$eRes = $conn->query($eventsSql);
if ($eRes) {
    while ($r = $eRes->fetch_assoc()) {
        $eventsList[] = $r;
    }
}

// Tab 4: Ideas List
$ideasList = [];
$ideasSql = "
    SELECT 
        i.id, i.title, i.category, i.status, i.created_at,
        u.name AS author_name,
        (SELECT COUNT(*) FROM idea_votes WHERE idea_id = i.id) AS votes_count
    FROM ideas i
    JOIN users u ON i.user_id = u.id
    ORDER BY votes_count DESC, i.created_at DESC LIMIT 50
";
$iRes = $conn->query($ideasSql);
if ($iRes) {
    while ($r = $iRes->fetch_assoc()) {
        $ideasList[] = $r;
    }
}

// Tab 5: Security / Audit Logs
$auditLogs = [];
$auditSql = "
    SELECT id, user_id, email, action, ip_address, user_agent, created_at
    FROM auth_logs
    ORDER BY created_at DESC LIMIT 60
";
$aRes = $conn->query($auditSql);
if ($aRes) {
    while ($r = $aRes->fetch_assoc()) {
        $auditLogs[] = $r;
    }
}

$pageTitle = "Institutional Admin Console";
$breadcrumbs = [
    ["label" => "Dashboard", "url" => "../../public/dashboard.php"],
    ["label" => "Admin Console"]
];

require_once __DIR__ . "/../../includes/header.php";

?>

<style>
/* ==========================================================================
   Campus Nexus – Admin Console Dedicated Modern Styling
   ========================================================================== */
.admin-shell {
    margin-bottom: 50px;
}

.admin-hero {
    background: linear-gradient(135deg, #0b1120 0%, #1e1b4b 50%, #0f172a 100%);
    border: 1px solid rgba(99, 102, 241, 0.25);
    border-radius: var(--radius-lg);
    padding: 28px 32px;
    color: #f8fafc;
    box-shadow: 0 12px 32px -4px rgba(7, 10, 19, 0.5);
    position: relative;
    overflow: hidden;
    margin-bottom: 28px;
}

.admin-hero::before {
    content: '';
    position: absolute;
    top: -50px;
    right: -50px;
    width: 250px;
    height: 250px;
    background: radial-gradient(circle, rgba(249, 115, 22, 0.22) 0%, transparent 70%);
    pointer-events: none;
}

.admin-hero-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    position: relative;
    z-index: 1;
}

.admin-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(249, 115, 22, 0.18);
    border: 1px solid rgba(249, 115, 22, 0.4);
    color: #fdba74;
    padding: 4px 12px;
    border-radius: 9999px;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.admin-hero-title {
    font-family: var(--font-display);
    font-size: clamp(1.6rem, 3.2vw, 2.2rem);
    margin: 8px 0 4px;
    color: #ffffff;
    display: flex;
    align-items: center;
    gap: 12px;
}

.admin-hero-sub {
    color: #94a3b8;
    margin: 0;
    font-size: 0.95rem;
}

.admin-nav-tabs {
    display: flex;
    gap: 8px;
    background: var(--surface);
    padding: 8px;
    border-radius: var(--radius-md);
    border: 1px solid var(--border);
    box-shadow: var(--shadow-sm);
    margin-bottom: 24px;
    overflow-x: auto;
}

.admin-tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border-radius: var(--radius-sm);
    border: none;
    background: transparent;
    color: var(--muted);
    font-weight: 600;
    font-size: 0.92rem;
    cursor: pointer;
    text-decoration: none;
    transition: all var(--transition-fast);
    white-space: nowrap;
}

.admin-tab-btn:hover {
    color: var(--text-primary);
    background: var(--surface-muted);
    text-decoration: none;
}

.admin-tab-btn.active {
    background: var(--primary);
    color: #ffffff;
    box-shadow: 0 4px 12px -2px rgba(79, 70, 229, 0.4);
}

.admin-tab-count {
    background: rgba(255, 255, 255, 0.22);
    padding: 2px 7px;
    border-radius: 9999px;
    font-size: 0.75rem;
}

.admin-tab-btn:not(.active) .admin-tab-count {
    background: var(--surface-muted);
    color: var(--text-secondary);
}

/* KPI Telemetry Grid */
.telemetry-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
}

.telemetry-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 20px;
    box-shadow: var(--shadow-sm);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform var(--transition-fast), box-shadow var(--transition-fast);
    position: relative;
    overflow: hidden;
}

.telemetry-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.telemetry-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: var(--muted);
    font-size: 0.85rem;
    font-weight: 600;
}

.telemetry-icon-box {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
}

.telemetry-num {
    font-size: 2.1rem;
    font-weight: 800;
    font-family: var(--font-display);
    color: var(--text-primary);
    margin: 10px 0 4px;
    line-height: 1.1;
}

.telemetry-sub {
    font-size: 0.78rem;
    color: var(--muted);
}

/* Data Table Container */
.admin-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    overflow: hidden;
    margin-bottom: 28px;
}

.admin-card-header {
    padding: 20px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px;
    border-bottom: 1px solid var(--border);
    background: #fafafa;
}

.admin-card-title {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 8px;
}

.admin-search-form {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}

.admin-search-input {
    padding: 8px 12px;
    font-size: 0.88rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    width: 240px;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
    box-shadow: none;
    border-radius: 0;
}

.admin-table th {
    background: #f1f5f9;
    color: #475569;
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 700;
    padding: 12px 18px;
    border-bottom: 1px solid var(--border);
}

.admin-table td {
    padding: 14px 18px;
    border-bottom: 1px solid var(--border-subtle);
    font-size: 0.9rem;
    vertical-align: middle;
}

.admin-table tr:hover td {
    background: #f8fafc;
}

/* User avatar */
.admin-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary) 0%, var(--lavender-glow) 100%);
    color: white;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
    flex-shrink: 0;
}

.admin-user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.admin-user-name {
    font-weight: 600;
    color: var(--text-primary);
    display: block;
}

.admin-user-email {
    font-size: 0.8rem;
    color: var(--muted);
}

/* Badges */
.badge-role-admin {
    background: rgba(249, 115, 22, 0.12);
    color: #ea580c;
    border: 1px solid rgba(249, 115, 22, 0.3);
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 9999px;
    font-size: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.badge-role-student {
    background: rgba(79, 70, 229, 0.1);
    color: var(--primary);
    border: 1px solid rgba(79, 70, 229, 0.25);
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 9999px;
    font-size: 0.75rem;
}

.badge-status-open, .badge-status-upcoming, .badge-status-success {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
    padding: 3px 8px;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-status-in_progress, .badge-status-ongoing {
    background: #eff6ff;
    color: #2563eb;
    border: 1px solid #bfdbfe;
    padding: 3px 8px;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-status-completed {
    background: #f5f3ff;
    color: #7c3aed;
    border: 1px solid #ddd6fe;
    padding: 3px 8px;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-status-cancelled, .badge-status-danger, .badge-status-failed {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
    padding: 3px 8px;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

/* Quick Action Form buttons */
.admin-action-btn {
    padding: 5px 11px;
    font-size: 0.8rem;
    border-radius: 6px;
    border: 1px solid var(--border);
    background: var(--surface);
    color: var(--text-secondary);
    cursor: pointer;
    font-weight: 600;
    transition: all var(--transition-fast);
}

.admin-action-btn:hover {
    background: var(--surface-muted);
    color: var(--text-primary);
}

.admin-action-promote {
    background: #fff7ed;
    border-color: #fdba74;
    color: #c2410c;
}

.admin-action-promote:hover {
    background: #ffedd5;
    color: #9a3412;
}

.admin-action-demote {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #475569;
}

.admin-inline-form {
    margin: 0;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
</style>

<main class="container admin-shell">

    <!-- Top Admin Header Banner -->
    <div class="admin-hero">
        <div class="admin-hero-top">
            <div>
                <span class="admin-hero-badge">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    Master Governance
                </span>
                <h1 class="admin-hero-title">
                    Institutional Admin Console
                </h1>
                <p class="admin-hero-sub">
                    Logged in as <strong><?= htmlspecialchars($_SESSION["user_name"] ?? "Administrator") ?></strong> (<?= htmlspecialchars($_SESSION["user_email"] ?? "") ?>). Full administrative privileges active.
                </p>
            </div>
            <div>
                <a href="<?= htmlspecialchars($baseUrl) ?>/public/dashboard.php" class="btn" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25); color: #fff; font-size: 0.88rem;">
                    ← Return to Student Portal
                </a>
            </div>
        </div>
    </div>

    <!-- Telemetry Cards -->
    <div class="telemetry-grid">

        <div class="telemetry-card">
            <div class="telemetry-header">
                <span>Total Community</span>
                <div class="telemetry-icon-box" style="background: #eef2ff; color: #4f46e5;">👥</div>
            </div>
            <div class="telemetry-num"><?= $stats["total_users"] ?></div>
            <div class="telemetry-sub"><?= $stats["total_students"] ?> Students · <?= $stats["total_admins"] ?> Admins</div>
        </div>

        <div class="telemetry-card">
            <div class="telemetry-header">
                <span>Collaboration Teams</span>
                <div class="telemetry-icon-box" style="background: #ecfdf5; color: #059669;">🚀</div>
            </div>
            <div class="telemetry-num"><?= $stats["total_projects"] ?></div>
            <div class="telemetry-sub"><?= $stats["open_projects"] ?> Currently Seeking Members</div>
        </div>

        <div class="telemetry-card">
            <div class="telemetry-header">
                <span>Campus Workshops</span>
                <div class="telemetry-icon-box" style="background: #fff7ed; color: #ea580c;">📅</div>
            </div>
            <div class="telemetry-num"><?= $stats["total_events"] ?></div>
            <div class="telemetry-sub"><?= $stats["total_registrations"] ?> Total Confirmed Seats</div>
        </div>

        <div class="telemetry-card">
            <div class="telemetry-header">
                <span>Campus Proposals</span>
                <div class="telemetry-icon-box" style="background: #fefce8; color: #ca8a04;">💡</div>
            </div>
            <div class="telemetry-num"><?= $stats["total_ideas"] ?></div>
            <div class="telemetry-sub">Student Pitches & Voting</div>
        </div>

        <div class="telemetry-card">
            <div class="telemetry-header">
                <span>Skill Exchange</span>
                <div class="telemetry-icon-box" style="background: #fdf2f8; color: #db2777;">🤝</div>
            </div>
            <div class="telemetry-num"><?= $stats["total_exchanges"] ?></div>
            <div class="telemetry-sub"><?= $stats["total_skills"] ?> Cataloged Skills</div>
        </div>

        <div class="telemetry-card">
            <div class="telemetry-header">
                <span>Security Audit</span>
                <div class="telemetry-icon-box" style="background: #f8fafc; color: #334155;">🔒</div>
            </div>
            <div class="telemetry-num"><?= $stats["total_audit_logs"] ?></div>
            <div class="telemetry-sub" style="color: <?= $stats["failed_logins_24h"] > 0 ? '#ef4444; font-weight:600;' : 'inherit;' ?>">
                <?= $stats["failed_logins_24h"] ?> Failed Logins (24h)
            </div>
        </div>

    </div>

    <!-- Tab Bar Navigation -->
    <nav class="admin-nav-tabs" aria-label="Admin Navigation Tabs">
        <a class="admin-tab-btn <?= $activeTab === 'overview' || $activeTab === 'users' ? 'active' : '' ?>" href="dashboard.php?tab=users">
            <span>👥 User Accounts</span>
            <span class="admin-tab-count"><?= $stats["total_users"] ?></span>
        </a>
        <a class="admin-tab-btn <?= $activeTab === 'projects' ? 'active' : '' ?>" href="dashboard.php?tab=projects">
            <span>🚀 Projects</span>
            <span class="admin-tab-count"><?= $stats["total_projects"] ?></span>
        </a>
        <a class="admin-tab-btn <?= $activeTab === 'events' ? 'active' : '' ?>" href="dashboard.php?tab=events">
            <span>📅 Events</span>
            <span class="admin-tab-count"><?= $stats["total_events"] ?></span>
        </a>
        <a class="admin-tab-btn <?= $activeTab === 'ideas' ? 'active' : '' ?>" href="dashboard.php?tab=ideas">
            <span>💡 Ideas</span>
            <span class="admin-tab-count"><?= $stats["total_ideas"] ?></span>
        </a>
        <a class="admin-tab-btn <?= $activeTab === 'audit' ? 'active' : '' ?>" href="dashboard.php?tab=audit">
            <span>🔒 Security Logs</span>
            <span class="admin-tab-count"><?= $stats["total_audit_logs"] ?></span>
        </a>
    </nav>

    <!-- TAB 1: USERS MANAGEMENT -->
    <?php if ($activeTab === 'overview' || $activeTab === 'users'): ?>
        <div class="admin-card">
            <div class="admin-card-header">
                <div>
                    <h2 class="admin-card-title">
                        <span>User & Role Governance</span>
                    </h2>
                    <span style="font-size: 0.85rem; color: var(--muted);">Promote students to administrators or inspect institutional roll numbers.</span>
                </div>
                <div>
                    <form class="admin-search-form" method="GET" action="dashboard.php">
                        <input type="hidden" name="tab" value="users">
                        <input class="admin-search-input" type="text" name="q" placeholder="Search name, email, roll..." value="<?= htmlspecialchars($searchQuery) ?>">
                        <button type="submit" class="admin-action-btn">Search</button>
                        <?php if (!empty($searchQuery)): ?>
                            <a href="dashboard.php?tab=users" class="admin-action-btn" style="text-decoration: none;">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Department & Roll No</th>
                            <th>Role</th>
                            <th>Gamification</th>
                            <th>Last Active</th>
                            <th>Account Created</th>
                            <th style="text-align: right;">Role Management</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usersList)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 32px; color: var(--muted);">
                                    No users found matching your search.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usersList as $u): ?>
                                <tr>
                                    <td>
                                        <div class="admin-user-cell">
                                            <div class="admin-avatar">
                                                <?= strtoupper(substr($u["name"], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <span class="admin-user-name"><?= htmlspecialchars($u["name"]) ?></span>
                                                <span class="admin-user-email"><?= htmlspecialchars($u["email"]) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-primary);">
                                            <?= htmlspecialchars($u["department"] ?: "Not specified") ?>
                                        </div>
                                        <div style="font-size: 0.8rem; color: var(--muted);">
                                            <?= htmlspecialchars($u["roll_number"] ? "Roll: " . $u["roll_number"] : "No Roll ID") ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($u["role"] === 'admin'): ?>
                                            <span class="badge-role-admin">
                                                ★ Admin
                                            </span>
                                        <?php else: ?>
                                            <span class="badge-role-student">
                                                Student
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.85rem;">
                                            ⭐ <strong><?= (int)$u["points"] ?></strong> pts
                                        </div>
                                        <div style="font-size: 0.78rem; color: var(--muted);">
                                            🏆 <?= (int)$u["badges_count"] ?> badges
                                        </div>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.82rem; color: var(--text-secondary);">
                                            <?= !empty($u["last_login_at"]) ? date("M j, Y H:i", strtotime($u["last_login_at"])) : "Never" ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.82rem; color: var(--muted);">
                                            <?= date("M j, Y", strtotime($u["created_at"])) ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <?php if ((int)$u["id"] === $currentUserId): ?>
                                            <span style="font-size: 0.78rem; color: var(--muted); font-style: italic;">
                                                Your Account (Active)
                                            </span>
                                        <?php else: ?>
                                            <form class="admin-inline-form" method="POST" action="dashboard.php" onsubmit="return confirm('Change <?= htmlspecialchars(addslashes($u['name'])) ?> to <?= $u['role'] === 'admin' ? 'Student' : 'Admin' ?>?');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">
                                                <input type="hidden" name="action" value="update_user_role">
                                                <input type="hidden" name="target_user_id" value="<?= (int)$u["id"] ?>">

                                                <?php if ($u["role"] === "admin"): ?>
                                                    <input type="hidden" name="new_role" value="student">
                                                    <button type="submit" class="admin-action-btn admin-action-demote" title="Demote to Student">
                                                        Make Student
                                                    </button>
                                                <?php else: ?>
                                                    <input type="hidden" name="new_role" value="admin">
                                                    <button type="submit" class="admin-action-btn admin-action-promote" title="Promote to Administrator">
                                                        Promote to Admin
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- TAB 2: PROJECTS MODERATION -->
    <?php if ($activeTab === 'projects'): ?>
        <div class="admin-card">
            <div class="admin-card-header">
                <div>
                    <h2 class="admin-card-title">🚀 Student Projects Moderation</h2>
                    <span style="font-size: 0.85rem; color: var(--muted);">Review collaboration proposals and update project lifecycles.</span>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Project Title</th>
                            <th>Creator</th>
                            <th>Status</th>
                            <th>Accepted Members</th>
                            <th>Created</th>
                            <th style="text-align: right;">Lifecycle Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($projectsList)): ?>
                            <tr><td colspan="6" style="text-align: center; padding: 32px; color: var(--muted);">No projects recorded yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($projectsList as $p): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: var(--text-primary); font-size: 0.95rem;">
                                            <?= htmlspecialchars($p["title"]) ?>
                                        </div>
                                        <span style="font-size: 0.78rem; color: var(--muted);">ID #<?= $p["id"] ?></span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600;"><?= htmlspecialchars($p["creator_name"]) ?></div>
                                        <div style="font-size: 0.78rem; color: var(--muted);"><?= htmlspecialchars($p["creator_email"]) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge-status-<?= htmlspecialchars($p["status"]) ?>">
                                            <?= ucfirst(str_replace('_', ' ', $p["status"])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?= (int)$p["members_count"] ?></strong> members
                                    </td>
                                    <td>
                                        <span style="font-size: 0.82rem; color: var(--muted);">
                                            <?= date("M j, Y", strtotime($p["created_at"])) ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <form class="admin-inline-form" method="POST" action="dashboard.php?tab=projects">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">
                                            <input type="hidden" name="action" value="update_project_status">
                                            <input type="hidden" name="project_id" value="<?= (int)$p["id"] ?>">

                                            <select name="new_status" style="width: auto; padding: 4px 8px; font-size: 0.8rem; margin: 0;">
                                                <option value="open" <?= $p["status"] === 'open' ? 'selected' : '' ?>>Open</option>
                                                <option value="in_progress" <?= $p["status"] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                                <option value="completed" <?= $p["status"] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                                <option value="cancelled" <?= $p["status"] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                            <button type="submit" class="admin-action-btn">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- TAB 3: EVENTS MANAGEMENT -->
    <?php if ($activeTab === 'events'): ?>
        <div class="admin-card">
            <div class="admin-card-header">
                <div>
                    <h2 class="admin-card-title">📅 Campus Workshops & Event Overseeing</h2>
                    <span style="font-size: 0.85rem; color: var(--muted);">Monitor capacity, registered seats, and cancellation status.</span>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Event Details</th>
                            <th>Organizer</th>
                            <th>Schedule Date</th>
                            <th>Location</th>
                            <th>Registrations</th>
                            <th>Status</th>
                            <th style="text-align: right;">Status Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($eventsList)): ?>
                            <tr><td colspan="7" style="text-align: center; padding: 32px; color: var(--muted);">No events registered.</td></tr>
                        <?php else: ?>
                            <?php foreach ($eventsList as $e): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: var(--text-primary); font-size: 0.95rem;">
                                            <?= htmlspecialchars($e["title"]) ?>
                                        </div>
                                        <span style="font-size: 0.78rem; color: var(--muted);">Event #<?= $e["id"] ?></span>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($e["creator_name"]) ?>
                                    </td>
                                    <td>
                                        <strong><?= date("M j, Y", strtotime($e["event_date"])) ?></strong>
                                        <div style="font-size: 0.78rem; color: var(--muted);"><?= date("h:i A", strtotime($e["event_date"])) ?></div>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.85rem;"><?= htmlspecialchars($e["location"]) ?></span>
                                    </td>
                                    <td>
                                        <strong><?= (int)$e["registered_count"] ?></strong> / <?= (int)$e["max_participants"] ?> seats
                                    </td>
                                    <td>
                                        <span class="badge-status-<?= htmlspecialchars($e["status"]) ?>">
                                            <?= ucfirst($e["status"]) ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <form class="admin-inline-form" method="POST" action="dashboard.php?tab=events">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">
                                            <input type="hidden" name="action" value="update_event_status">
                                            <input type="hidden" name="event_id" value="<?= (int)$e["id"] ?>">

                                            <select name="new_status" style="width: auto; padding: 4px 8px; font-size: 0.8rem; margin: 0;">
                                                <option value="upcoming" <?= $e["status"] === 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
                                                <option value="ongoing" <?= $e["status"] === 'ongoing' ? 'selected' : '' ?>>Ongoing</option>
                                                <option value="completed" <?= $e["status"] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                                <option value="cancelled" <?= $e["status"] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                            <button type="submit" class="admin-action-btn">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- TAB 4: IDEAS MODERATION -->
    <?php if ($activeTab === 'ideas'): ?>
        <div class="admin-card">
            <div class="admin-card-header">
                <div>
                    <h2 class="admin-card-title">💡 Campus Idea Board Proposals</h2>
                    <span style="font-size: 0.85rem; color: var(--muted);">View democratic proposals, vote tallies, and status updates.</span>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Proposal Title</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Votes</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th style="text-align: right;">Status Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ideasList)): ?>
                            <tr><td colspan="7" style="text-align: center; padding: 32px; color: var(--muted);">No ideas posted yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($ideasList as $i): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: var(--text-primary);">
                                            <?= htmlspecialchars($i["title"]) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status status-peach" style="font-size: 0.75rem; padding: 2px 8px;">
                                            <?= htmlspecialchars($i["category"]) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($i["author_name"]) ?></td>
                                    <td>
                                        <strong>⭐ <?= (int)$i["votes_count"] ?></strong> votes
                                    </td>
                                    <td>
                                        <span class="badge-status-<?= htmlspecialchars($i["status"]) ?>">
                                            <?= ucfirst(str_replace('_', ' ', $i["status"])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.82rem; color: var(--muted);">
                                            <?= date("M j, Y", strtotime($i["created_at"])) ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <form class="admin-inline-form" method="POST" action="dashboard.php?tab=ideas">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">
                                            <input type="hidden" name="action" value="update_idea_status">
                                            <input type="hidden" name="idea_id" value="<?= (int)$i["id"] ?>">

                                            <select name="new_status" style="width: auto; padding: 4px 8px; font-size: 0.8rem; margin: 0;">
                                                <option value="open" <?= $i["status"] === 'open' ? 'selected' : '' ?>>Open</option>
                                                <option value="in_progress" <?= $i["status"] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                                <option value="completed" <?= $i["status"] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                                <option value="archived" <?= $i["status"] === 'archived' ? 'selected' : '' ?>>Archived</option>
                                            </select>
                                            <button type="submit" class="admin-action-btn">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- TAB 5: SECURITY AUDIT LOGS -->
    <?php if ($activeTab === 'audit'): ?>
        <div class="admin-card">
            <div class="admin-card-header">
                <div>
                    <h2 class="admin-card-title">🔒 Institutional Security & Access Audit Logs</h2>
                    <span style="font-size: 0.85rem; color: var(--muted);">Immutable authentication activities and threat detection records.</span>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Event Action</th>
                            <th>Account / Email</th>
                            <th>IP Address</th>
                            <th>Client / Browser</th>
                            <th>Recorded Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($auditLogs)): ?>
                            <tr><td colspan="5" style="text-align: center; padding: 32px; color: var(--muted);">No audit logs found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($auditLogs as $log): ?>
                                <tr>
                                    <td>
                                        <?php if ($log["action"] === "LOGIN_SUCCESS"): ?>
                                            <span class="badge-status-success">✓ Login Success</span>
                                        <?php elseif ($log["action"] === "LOGIN_FAILED"): ?>
                                            <span class="badge-status-danger">✕ Login Failed</span>
                                        <?php elseif ($log["action"] === "REGISTRATION"): ?>
                                            <span class="badge-status-in_progress">+ Registration</span>
                                        <?php else: ?>
                                            <span style="font-size: 0.75rem; color: var(--muted); font-weight: 600;"><?= htmlspecialchars($log["action"]) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($log["email"]) ?></strong>
                                        <?php if (!empty($log["user_id"])): ?>
                                            <span style="font-size: 0.78rem; color: var(--muted);">(ID: <?= (int)$log["user_id"] ?>)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <code><?= htmlspecialchars($log["ip_address"]) ?></code>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.78rem; color: var(--muted); max-width: 300px; display: inline-block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($log["user_agent"] ?? "") ?>">
                                            <?= htmlspecialchars($log["user_agent"] ?: "Unknown") ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.82rem; color: var(--text-secondary);">
                                            <?= date("M j, Y H:i:s", strtotime($log["created_at"])) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</main>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>
