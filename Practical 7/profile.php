```php
<?php

// Form માંથી data મેળવવું
$name = $_POST["name"];
$enrollment = $_POST["enrollment"];
$email = $_POST["email"];
$branch = $_POST["branch"];
$semester = $_POST["semester"];
$phone = $_POST["phone"];
$dob = $_POST["dob"];
$address = $_POST["address"];

// CSV file નું નામ
$file = "profiles.csv";

// File open કરવી
$handle = fopen($file, "a");

// જો file નવી છે તો headings add કરવી
if (filesize($file) == 0) {
    fputcsv($handle, array(
        "Name",
        "Enrollment No",
        "Email",
        "Branch",
        "Semester",
        "Phone",
        "Date of Birth",
        "Address"
    ));
}

// Student information CSV માં save કરવી
fputcsv($handle, array(
    $name,
    $enrollment,
    $email,
    $branch,
    $semester,
    $phone,
    $dob,
    $address
));

// File close કરવી
fclose($handle);

?>

<!DOCTYPE html>
<html>

<head>
    <title>Profile Saved</title>
</head>

<body>

    <center>

        <h1>Student Hub Portal</h1>

        <h2>Profile Saved Successfully!</h2>

        <p>Your profile information has been saved.</p>

        <br>

        <a href="Profile.html">Go Back to Profile</a>

        <br><br>

        <a href="profiles.csv">View Saved Data</a>

    </center>

</body>

</html>
```