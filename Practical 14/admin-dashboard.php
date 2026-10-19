
<?php
session_start();

// 1. Check login and admin role
if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"])
) {
    header("Location: login.php");
    exit;
}

if ($_SESSION["role"] !== "admin") {
    header("Location: student-dashboard.php");
    exit;
}

// 2. Current admin details
$username = $_SESSION["username"] ?? "Admin";

// 3. Secure output function
function e($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}

try {
    // 4. Database connection
    $conn = new PDO(
        "mysql:host=localhost;dbname=studenthub;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // 5. Total users
    $userCount = (int)$conn->query(
        "SELECT COUNT(*) FROM users"
    )->fetchColumn();

    // 6. Total students
    $studentCount = (int)$conn->query(
        "SELECT COUNT(*) FROM profiles"
    )->fetchColumn();

    // 7. Total events
    $eventCount = (int)$conn->query(
        "SELECT COUNT(*) FROM events"
    )->fetchColumn();

    // 8. Latest five student registrations
    $latestStudents = $conn->query(
        "SELECT name, enrollment, email, branch
         FROM profiles
         ORDER BY id DESC
         LIMIT 5"
    )->fetchAll();

    // 9. Latest five events
    $latestEvents = $conn->query(
        "SELECT event_id, event_name, event_date, venue
         FROM events
         ORDER BY event_id DESC
         LIMIT 5"
    )->fetchAll();

    // 10. Create audit log table if it does not exist
    $conn->exec(
        "CREATE TABLE IF NOT EXISTS audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL,
            action VARCHAR(255) NOT NULL,
            log_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    );

    // 11. Record dashboard activity
    $log = $conn->prepare(
        "INSERT INTO audit_logs (username, action)
         VALUES (?, ?)"
    );

    $log->execute([
        $username,
        "Viewed Admin Dashboard"
    ]);

    // 12. Get latest audit records
    $auditLogs = $conn->query(
        "SELECT username, action, log_time
         FROM audit_logs
         ORDER BY id DESC
         LIMIT 10"
    )->fetchAll();

} catch (PDOException $error) {
    error_log($error->getMessage());

    exit(
        "Database error. Please check your database tables and columns."
    );
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

    <title>StudentHub | Admin Dashboard</title>

    <link rel="stylesheet" href="style.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #263238;
        }

        .sidebar {
            width: 240px;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: #263b50;
            color: white;
            padding: 25px 15px;
        }

        .sidebar h2 {
            text-align: center;
            margin: 0 0 30px;
        }

        .menu-label {
            font-size: 12px;
            color: #b8c7d5;
            padding: 0 12px;
            margin-bottom: 15px;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 13px 12px;
            margin: 5px 0;
            border-radius: 6px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #3c5875;
        }

        .sidebar-logout {
            margin-top: 30px !important;
            background: #a93226;
        }

        .main-content {
            margin-left: 240px;
            min-height: 100vh;
        }

        .topbar {
            background: white;
            padding: 20px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            box-shadow: 0 2px 8px #0000000a;
        }

        .topbar h2 {
            margin: 0 0 8px;
        }

        .muted {
            color: #77818c;
            margin: 0;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #dceaf7;
            color: #245780;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 20px;
        }

        .admin-name strong,
        .admin-name span {
            display: block;
        }

        .admin-name span {
            color: #77818c;
            font-size: 13px;
            margin-top: 4px;
        }

        .logout-btn {
            text-decoration: none;
            background: #a93226;
            color: white;
            padding: 10px 14px;
            border-radius: 5px;
        }

        main {
            padding: 28px;
            max-width: 1500px;
            margin: auto;
        }

        .welcome-section {
            margin-bottom: 25px;
        }

        .welcome-section h1 {
            margin-bottom: 8px;
        }

        .welcome-section p {
            color: #77818c;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: white;
            padding: 22px;
            border-radius: 9px;
            border-left: 5px solid #2980b9;
            box-shadow: 0 2px 8px #0000000a;
        }

        .stat-card p {
            color: #65717e;
            margin: 10px 0;
        }

        .stat-card h2 {
            font-size: 30px;
            margin: 8px 0;
        }

        .stat-card span {
            font-size: 13px;
            color: #77818c;
        }

        .stat-icon {
            font-size: 25px;
        }

        .blue {
            border-color: #2980b9;
        }

        .green {
            border-color: #239b56;
        }

        .purple {
            border-color: #8854a0;
        }

        .orange {
            border-color: #e67e22;
        }

        .content-card {
            background: white;
            padding: 22px;
            border-radius: 9px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px #0000000a;
            scroll-margin-top: 20px;
        }

        .section-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .section-heading h2 {
            margin: 0 0 7px;
            font-size: 21px;
        }

        .section-heading p {
            color: #77818c;
            margin: 0;
        }

        .count-badge {
            background: #eaf2f8;
            color: #245780;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 13px;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 550px;
        }

        th,
        td {
            padding: 13px;
            border-bottom: 1px solid #e8ebef;
            text-align: left;
        }

        th {
            background: #f4f6f9;
            color: #465363;
        }

        tbody tr:hover {
            background: #f9fbfd;
        }

        .empty-state {
            text-align: center;
            color: #77818c;
            padding: 25px;
        }

        .action-label {
            display: inline-block;
            padding: 6px 10px;
            background: #e8f5e9;
            color: #26733b;
            border-radius: 5px;
            font-size: 13px;
        }

        .footer {
            text-align: center;
            color: #77818c;
            padding: 25px 10px;
        }

        @media (max-width: 1100px) {
            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 700px) {
            .sidebar {
                position: static;
                width: 100%;
                min-height: auto;
            }

            .sidebar h2 {
                margin-bottom: 15px;
            }

            .main-content {
                margin-left: 0;
            }

            .topbar {
                padding: 18px;
            }

            main {
                padding: 16px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<!-- SIDEBAR: ADMIN MENU -->

<div class="sidebar">

    <h2>🎓 StudentHub</h2>

    <p class="menu-label">ADMIN PANEL</p>

    <a href="#overview" class="active">
        ▦ &nbsp; Dashboard
    </a>

    <a href="#students">
        ♙ &nbsp; Students
    </a>

    <a href="#events">
        ▣ &nbsp; Events
    </a>

    <a href="#audit">
        ◷ &nbsp; Audit Log
    </a>

    <a href="profile.php">
        ♙ &nbsp; My Profile
    </a>

    <a href="logout.php" class="sidebar-logout">
        ↪ &nbsp; Logout
    </a>

</div>

<!-- MAIN CONTENT -->

<div class="main-content">

    <header class="topbar">

        <div>
            <h2>Admin Dashboard</h2>
            <p class="muted">
                Welcome back! Here's your portal overview.
            </p>
        </div>

        <div class="admin-info">

            <div class="avatar">
                <?= e(strtoupper(substr($username, 0, 1))) ?>
            </div>

            <div class="admin-name">
                <strong><?= e($username) ?></strong>
                <span>Administrator</span>
            </div>

            <a href="logout.php" class="logout-btn">
                Logout
            </a>

        </div>

    </header>

    <main>

        <!-- OVERVIEW -->

        <section id="overview" class="welcome-section">

            <h1>Dashboard Overview</h1>

            <p>
                Manage and monitor your StudentHub Portal.
            </p>

        </section>

        <!-- STATISTICS -->

        <section class="stats-grid">

            <div class="stat-card blue">

                <div class="stat-icon">♙</div>

                <p>Total Users</p>

                <h2><?= $userCount ?></h2>

                <span>Registered accounts</span>

            </div>

            <div class="stat-card green">

                <div class="stat-icon">▤</div>

                <p>Total Students</p>

                <h2><?= $studentCount ?></h2>

                <span>Student profiles</span>

            </div>

            <div class="stat-card purple">

                <div class="stat-icon">▣</div>

                <p>Total Events</p>

                <h2><?= $eventCount ?></h2>

                <span>Events in the portal</span>

            </div>

            <div class="stat-card orange">

                <div class="stat-icon">◷</div>

                <p>Recent Registrations</p>

                <h2><?= count($latestStudents) ?></h2>

                <span>Latest 5 registrations maximum</span>

            </div>

        </section>

        <!-- LATEST STUDENTS -->

        <section id="students" class="content-card">

            <div class="section-heading">

                <div>
                    <h2>Latest Student Registrations</h2>
                    <p>Recently added student profiles</p>
                </div>

                <span class="count-badge">
                    <?= count($latestStudents) ?> records
                </span>

            </div>

            <div class="table-wrapper">

                <table>

                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Enrollment</th>
                            <th>Email</th>
                            <th>Branch</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if ($latestStudents): ?>

                            <?php foreach ($latestStudents as $s): ?>

                                <tr>
                                    <td>
                                        <strong><?= e($s["name"]) ?></strong>
                                    </td>

                                    <td><?= e($s["enrollment"]) ?></td>

                                    <td><?= e($s["email"]) ?></td>

                                    <td><?= e($s["branch"]) ?></td>
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="4" class="empty-state">
                                    No student registrations found.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

        <!-- LATEST EVENTS -->

        <section id="events" class="content-card">

            <div class="section-heading">

                <div>
                    <h2>Latest Events</h2>
                    <p>Recently added portal events</p>
                </div>

                <span class="count-badge">
                    <?= count($latestEvents) ?> records
                </span>

            </div>

            <div class="table-wrapper">

                <table>

                    <thead>
                        <tr>
                            <th>Event ID</th>
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Venue</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if ($latestEvents): ?>

                            <?php foreach ($latestEvents as $event): ?>

                                <tr>
                                    <td><?= e($event["event_id"]) ?></td>

                                    <td>
                                        <strong>
                                            <?= e($event["event_name"]) ?>
                                        </strong>
                                    </td>

                                    <td><?= e($event["event_date"]) ?></td>

                                    <td><?= e($event["venue"]) ?></td>
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="4" class="empty-state">
                                    No events found.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

        <!-- AUDIT LOG -->

        <section id="audit" class="content-card">

            <div class="section-heading">

                <div>
                    <h2>Audit Log</h2>
                    <p>Recent administrator activity</p>
                </div>

                <span class="count-badge">
                    <?= count($auditLogs) ?> records
                </span>

            </div>

            <div class="table-wrapper">

                <table>

                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Action</th>
                            <th>Date and Time</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if ($auditLogs): ?>

                            <?php foreach ($auditLogs as $entry): ?>

                                <tr>
                                    <td><?= e($entry["username"]) ?></td>

                                    <td>
                                        <span class="action-label">
                                            <?= e($entry["action"]) ?>
                                        </span>
                                    </td>

                                    <td><?= e($entry["log_time"]) ?></td>
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="3" class="empty-state">
                                    No activity found.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

        <footer class="footer">
            StudentHub Admin Panel &copy; <?= date("Y") ?>
        </footer>

    </main>

</div>

</body>
</html>
