
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

    $stmt = $conn->prepare(
        "INSERT INTO audit_logs (username, action) VALUES (?, ?)"
    );
    $stmt->execute([
        $_SESSION["username"],
        "Viewed Student Dashboard"
    ]);

    $eventCount = (int)$conn->query(
        "SELECT COUNT(*) FROM events"
    )->fetchColumn();

} catch (PDOException $e) {
    exit("Database connection failed.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | StudentHub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="topbar">
    <h2>StudentHub</h2>
    <div>
        Welcome, <?php echo e($_SESSION["username"]); ?>
        <a class="logout" href="logout.php">Logout</a>
    </div>
</header>

<div class="dashboard-layout">
    <aside class="sidebar">
        <h3>Student Menu</h3>
        <a href="student-dashboard.php">Dashboard</a>
        <a href="Profile.html">Profile</a>
        <a href="#events">Events</a>
        <a href="logout.php">Logout</a>
    </aside>

    <main class="content">
        <h1>Student Dashboard</h1>
        <p>Welcome to your StudentHub Portal.</p>

        <div class="stats">
            <div class="stat-card">
                <h3>My Profile</h3>
                <p><a href="Profile.html">View Profile</a></p>
            </div>

            <div class="stat-card" id="events">
                <h3>Total Events</h3>
                <p><?php echo $eventCount; ?></p>
            </div>
        </div>
    </main>
</div>

</body>
</html>
