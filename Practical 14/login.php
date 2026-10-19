
<?php
session_start();

$error = "";

try {
    $conn = new PDO(
        "mysql:host=localhost;dbname=studenthub;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $username = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";

        $stmt = $conn->prepare(
            "SELECT id, username, password, role
             FROM users WHERE username = ? LIMIT 1"
        );
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password"])) {
            if (!in_array($user["role"], ["admin", "student"], true)) {
                $error = "Invalid user role.";
            } else {
                session_regenerate_id(true);

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role"];

                $log = $conn->prepare(
                    "INSERT INTO audit_logs (username, action)
                     VALUES (?, ?)"
                );
                $log->execute([$user["username"], "Login"]);

                if ($user["role"] === "admin") {
                    header("Location: admin-dashboard.php");
                } else {
                    header("Location: student-dashboard.php");
                }
                exit;
            }
        } else {
            $error = "Invalid username or password.";
        }
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-box">
    <h1>StudentHub</h1>
    <h2>Login</h2>

    <form method="POST" action="login.php">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>

    <p class="error">
        <?php echo htmlspecialchars($error, ENT_QUOTES, "UTF-8"); ?>
    </p>
</div>

</body>
</html>
