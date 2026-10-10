
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

if (($_SESSION["role"] ?? "") !== "student") {
    header("Location: admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Hub - Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f3f5;
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

        a {
            display: inline-block;
            margin: 10px;
            color: #284b63;
        }
    </style>
</head>
<body>
    <div class="box">
        <h1>Student Hub Portal</h1>
        <h2>Welcome to Student Dashboard!</h2>

        <p>
            Hello,
            <?= htmlspecialchars($_SESSION["username"]) ?>
        </p>

        <p>You are logged in as a Student.</p>

        <a href="profile.php">Profile</a>
        <a href="attendance.php">Attendance</a>
        <a href="assignment.php">Assignments</a>

        <br><br>
        <a href="logout.php">Logout</a>
    </div>
</body>
</html>
