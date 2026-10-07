<?php
include "db.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirmPassword"];

    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {

        $message = "All fields are required.";
        $messageType = "error";

    } elseif (!preg_match("/^[a-zA-Z0-9_]{3,50}$/", $username)) {

        $message = "Username must contain 3-50 letters, numbers or underscore.";
        $messageType = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "error";

    } elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";
        $messageType = "error";

    } elseif ($password !== $confirmPassword) {

        $message = "Passwords do not match.";
        $messageType = "error";

    } else {

        $check = $conn->prepare(
            "SELECT id FROM users WHERE username = ? OR email = ?"
        );

        $check->bind_param("ss", $username, $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "Username or email already exists.";
            $messageType = "error";

        } else {

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users (username, email, password)
                 VALUES (?, ?, ?)"
            );

            $stmt->bind_param(
                "sss",
                $username,
                $email,
                $hashedPassword
            );

            if ($stmt->execute()) {

                $message = "Registration successful!";
                $messageType = "success";

            } else {

                $message = "Registration failed. Please try again.";
                $messageType = "error";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>StudentHub Registration</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f4f7;
        }

        .container {
            width: 400px;
            margin: 70px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }

        h2 {
            text-align: center;
            color: #2e4141;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            margin-top: 20px;
            padding: 12px;
            background-color: #2e4141;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #1d2b2b;
        }

        .success {
            color: green;
            text-align: center;
            margin-bottom: 15px;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

<div class="container">

    <h2>StudentHub Registration</h2>

    <?php if (!empty($message)) { ?>
        <div class="<?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php } ?>

    <form method="POST"
          onsubmit="return validateForm()">

        <label>Username</label>
        <input type="text"
               name="username"
               id="username"
               required
               minlength="3"
               maxlength="50">

        <label>Email</label>
        <input type="email"
               name="email"
               id="email"
               required>

        <label>Password</label>
        <input type="password"
               name="password"
               id="password"
               required
               minlength="6">

        <label>Confirm Password</label>
        <input type="password"
               name="confirmPassword"
               id="confirmPassword"
               required
               minlength="6">

        <button type="submit">Register</button>

    </form>

</div>

<script>
function validateForm() {

    let username = document.getElementById("username").value.trim();
    let email = document.getElementById("email").value.trim();
    let password = document.getElementById("password").value;
    let confirmPassword = document.getElementById("confirmPassword").value;

    let usernamePattern = /^[a-zA-Z0-9_]{3,50}$/;
    let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!usernamePattern.test(username)) {
        alert("Username must contain 3-50 letters, numbers or underscore.");
        return false;
    }

    if (!emailPattern.test(email)) {
        alert("Please enter a valid email.");
        return false;
    }

    if (password.length < 6) {
        alert("Password must be at least 6 characters.");
        return false;
    }

    if (password !== confirmPassword) {
        alert("Passwords do not match.");
        return false;
    }

    return true;
}
</script>

</body>
</html>