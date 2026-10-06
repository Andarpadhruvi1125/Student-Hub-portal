
<!DOCTYPE html>
<html>
<head>
    <title>Student Information</title>
</head>

<body>

    <h1>Student Information</h1>

    <form method="post">

        <label>Name:</label>
        <input type="text" name="name" required>
        <br><br>

        <label>ID:</label>
        <input type="text" name="id" required>
        <br><br>

        <label>Contact Number:</label>
        <input type="text" name="contact" required>
        <br><br>

        <label>Email:</label>
        <input type="email" name="email" required>
        <br><br>

        <input type="submit" name="submit" value="Submit">

    </form>

    <?php

    if (isset($_POST["submit"])) {

        $name = $_POST["name"];
        $id = $_POST["id"];
        $contact = $_POST["contact"];
        $email = $_POST["email"];

        $file = fopen("students.csv", "a");

        fputcsv($file, [$name, $id, $contact, $email]);

        fclose($file);

        echo "<h2>Data Stored Successfully!</h2>";
    }

    ?>

</body>
</html>
