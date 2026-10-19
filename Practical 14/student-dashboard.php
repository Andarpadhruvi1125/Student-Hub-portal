```php
<?php
session_start();

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "student"
) {
    header("Location: login.php");
    exit;
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

try {
    $conn = new PDO(
        "mysql:host=localhost;dbname=studenthub;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Record dashboard visit in audit log
    $stmt = $conn->prepare(
        "INSERT INTO audit_logs (username, action) VALUES (?, ?)"
    );

    $stmt->execute([
        $_SESSION["username"] ?? "Student",
        "Viewed Student Dashboard"
    ]);

    // Get total events from database
    $eventCount = (int)$conn->query(
        "SELECT COUNT(*) FROM events"
    )->fetchColumn();

} catch (PDOException $error) {
    exit("Database error. Please check your database connection and tables.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Dashboard | StudentHub</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f3f6fc;
            color: #24324b;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 240px;
            height: 100vh;
            background: #192951;
            padding: 28px 18px;
            overflow-y: auto;
            z-index: 100;
        }

        .sidebar h3 {
            color: #5b8cff;
            font-size: 21px;
            margin: 4px 0 25px;
            line-height: 1.4;
        }

        .sidebar a {
            display: block;
            color: #ffffff;
            text-decoration: none;
            padding: 13px 14px;
            margin-bottom: 8px;
            border-radius: 7px;
            font-size: 15px;
            line-height: 1.4;
            transition: background 0.2s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #304575;
        }

        /* Top navigation */
        .topbar {
            margin-left: 240px;
            min-height: 80px;
            background: #ffffff;
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        }

        .topbar h2 {
            color: #2457c5;
            font-size: 24px;
            line-height: 1.4;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            font-size: 15px;
        }

        .logout {
            color: #d94848;
            text-decoration: none;
            font-weight: bold;
        }

        .logout:hover {
            text-decoration: underline;
        }

        /* Main content */
        .content {
            margin-left: 240px;
            padding: 32px;
            min-width: 0;
        }

        .content h1 {
            color: #203b68;
            font-size: 32px;
            line-height: 1.4;
            margin-bottom: 10px;
            overflow-wrap: anywhere;
        }

        .welcome-text {
            color: #65738a;
            font-size: 16px;
            line-height: 1.7;
            margin-bottom: 28px;
        }

        /* Dashboard cards */
        .stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
        }

        .stat-card {
            background: #ffffff;
            border: 1px solid #e1e8f2;
            border-radius: 14px;
            padding: 26px;
            min-height: 165px;
            box-shadow: 0 4px 15px rgba(25, 41, 81, 0.05);
        }

        .stat-card h3 {
            color: #52627c;
            font-size: 19px;
            line-height: 1.5;
            margin-bottom: 20px;
        }

        .event-number {
            color: #2457c5;
            font-size: 34px;
            font-weight: bold;
            line-height: 1.4;
        }

        .profile-button {
            display: inline-block;
            padding: 11px 18px;
            background: #2457c5;
            color: #ffffff;
            border-radius: 7px;
            text-decoration: none;
            font-size: 14px;
        }

        .profile-button:hover {
            background: #193f96;
        }

        /* Tablet and mobile layout */
        @media (max-width: 768px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                padding: 16px;
            }

            .sidebar h3 {
                margin-bottom: 12px;
            }

            .sidebar a {
                display: inline-block;
                margin: 3px;
                padding: 10px 12px;
            }

            .topbar {
                margin-left: 0;
                padding: 18px;
            }

            .content {
                margin-left: 0;
                padding: 22px 16px;
            }

            .content h1 {
                font-size: 27px;
            }

            .stats {
                grid-template-columns: 1fr;
                gap: 16px;
            }
        }

        @media (max-width: 420px) {
            .topbar h2 {
                font-size: 21px;
            }

            .content h1 {
                font-size: 25px;
            }

            .stat-card {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <h3>Student Menu</h3>

        <a href="student-dashboard.php" class="active">
            Dashboard
        </a>

        <a href="Profile.html">
            Profile
        </a>

        <!-- Change events.php if your actual filename is different -->
        <a href="events_api.html">
            Events
        </a>

        <a href="logout.php">
            Logout
        </a>
    </aside>

    <!-- Topbar -->
    <header class="topbar">
        <h2>StudentHub</h2>

        <div class="user-info">
            <span>
                Welcome, <?php echo e($_SESSION["username"] ?? "Student"); ?>
            </span>

            <a class="logout" href="logout.php">
                Logout
            </a>
        </div>
    </header>

    <!-- Dashboard content -->
    <main class="content">

        <h1>Student Dashboard</h1>

        <p class="welcome-text">
            Welcome to your StudentHub Portal.
            Manage your profile and explore available events.
        </p>

        <div class="stats">

            <section class="stat-card">
                <h3>My Profile</h3>

                <a href="Profile.html" class="profile-button">
                    View Profile
                </a>
            </section>

            <section class="stat-card">
                <h3>Total Events</h3>

                <p class="event-number">
                    <?php echo $eventCount; ?>
                </p>
            </section>

        </div>

    </main>

</body>
</html>
```