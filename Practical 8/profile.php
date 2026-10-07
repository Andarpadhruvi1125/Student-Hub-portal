<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "studenthub";

try {

    $conn = new PDO(
        "mysql:host=$host;dbname=$database",
        $username,
        $password
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $name = $_POST["name"];
    $enrollment = $_POST["enrollment"];
    $email = $_POST["email"];
    $branch = $_POST["branch"];
    $semester = $_POST["semester"];
    $phone = $_POST["phone"];
    $dob = $_POST["dob"];
    $address = $_POST["address"];

    $sql = "INSERT INTO profiles
            (name, enrollment, email, branch, semester, phone, dob, address)
            VALUES
            (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        $name,
        $enrollment,
        $email,
        $branch,
        $semester,
        $phone,
        $dob,
        $address
    ]);

    echo "<h1>Profile Saved Successfully!</h1>";
    echo "<p>Student information has been saved in MySQL database.</p>";
    echo '<a href="Profile.html">Go Back to Profile</a>';

} catch (PDOException $e) {

    echo "Database Error: " . $e->getMessage();

}

?>