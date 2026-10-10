
<?php
session_start();
require_once "db.php";

if (isset($_SESSION["user_id"])) {
    if ($_SESSION["role"] === "admin") {
        header("Location: admin.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {
        $error = "Please enter username and password.";
    } else {
        $stmt = $conn->prepare(
            "SELECT id, username, password, role
             FROM users WHERE username = ?"
        );

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user["password"])) {
            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = $user["role"];
            $_SESSION["last_activity"] = time();

            if ($user["role"] === "admin") {
                header("Location: admin.php");
            } else {
                header("Location: dashboard.php");
            }
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Hub - Secure Login</title>
</head>
<body>
    <h2>Student Hub Portal Login</h2>

    <?php if (isset($_GET["timeout"])): ?>
        <p>Your session expired. Please login again.</p>
    <?php endif; ?>

    <p style="color:red;">
        <?= htmlspecialchars($error) ?>
    </p>

    <form method="POST" action="login.php">
        <label>Username:</label>
        <input type="text" name="username" required>
        <br><br>

        <label>Password:</label>
        <input type="password" name="password" required>
        <br><br>

        <button type="submit">Login</button>
    </form>
</body>
</html>
