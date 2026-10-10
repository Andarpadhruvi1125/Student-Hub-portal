
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if (time() - ($_SESSION["last_activity"] ?? time()) > 300) {
    $_SESSION = [];
    session_destroy();

    header("Location: login.php?timeout=1");
    exit;
}

$_SESSION["last_activity"] = time();

if (($_SESSION["role"] ?? "") !== "admin") {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Hub - Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #e9ecef;
            text-align: center;
            padding: 40px;
        }

        .box {
            background: white;
            padding: 30px;
            max-width: 600px;
            margin: auto;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="box">
        <h1>Student Hub Portal</h1>
        <h2>Admin Dashboard</h2>

        <p>
            Welcome,
            <?= htmlspecialchars($_SESSION["username"]) ?>!
        </p>

        <p>You are logged in as an Admin.</p>

        <a href="logout.php">Logout</a>
    </div>
</body>
</html>
