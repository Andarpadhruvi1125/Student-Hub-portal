<?php
require_once "student_db_mysqli.php";

$message = "";
$editStudent = null;

if (isset($_POST['add'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $course = trim($_POST['course']);
    $semester = (int)$_POST['semester'];

    if (
        $name == "" ||
        $course == "" ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $semester < 1 ||
        $semester > 8
    ) {
        $message = "Please enter valid student details.";
    } else {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO students (name, email, course, semester)
             VALUES (?, ?, ?, ?)");

        mysqli_stmt_bind_param(
            $stmt, "sssi", $name, $email, $course, $semester
        );

        if (mysqli_stmt_execute($stmt)) {
            $message = "Student added successfully!";
        } else {
            $message = "Failed to add student: " . mysqli_stmt_error($stmt);
        }

        mysqli_stmt_close($stmt);
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmt = mysqli_prepare($conn,
        "DELETE FROM students WHERE student_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
        $message = "Student deleted successfully!";
    } else {
        $message = "Student not found or delete failed.";
    }
    mysqli_stmt_close($stmt);
}

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];

    $stmt = mysqli_prepare($conn,
        "SELECT student_id, name, email, course, semester
         FROM students WHERE student_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $editStudent = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$editStudent) {
        $message = "Student not found.";
    }
}

if (isset($_POST['update'])) {
    $id = (int)$_POST['student_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $course = trim($_POST['course']);
    $semester = (int)$_POST['semester'];

    if ($name == "" || $course == "" ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $semester < 1 || $semester > 8) {
        $message = "Please enter valid details.";
    } else {
        $stmt = mysqli_prepare($conn,
            "UPDATE students
             SET name = ?, email = ?, course = ?, semester = ?
             WHERE student_id = ?");

        mysqli_stmt_bind_param(
            $stmt, "sssii", $name, $email, $course, $semester, $id
        );

        if (mysqli_stmt_execute($stmt)) {
            $message = mysqli_stmt_affected_rows($stmt) >= 0
                ? "Student updated successfully!"
                : "Update failed.";
        } else {
            $message = "Failed to update: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
        $editStudent = null;
    }
}

$search = trim($_GET['search'] ?? '');
$filterCourse = trim($_GET['course'] ?? '');
$filterSemester = (int)($_GET['semester'] ?? 0);

$sql = "SELECT student_id, name, email, course, semester FROM students
        WHERE (name LIKE ? OR email LIKE ?)";

$searchValue = "%" . $search . "%";
$params = [$searchValue, $searchValue];
$types = "ss";

if ($filterCourse !== "") {
    $sql .= " AND course = ?";
    $params[] = $filterCourse;
    $types .= "s";
}

if ($filterSemester >= 1 && $filterSemester <= 8) {
    $sql .= " AND semester = ?";
    $params[] = $filterSemester;
    $types .= "i";
}

$sql .= " ORDER BY student_id DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$coursesResult = mysqli_query($conn,
    "SELECT DISTINCT course FROM students ORDER BY course");
$courses = [];
if ($coursesResult) {
    while ($row = mysqli_fetch_assoc($coursesResult)) {
        $courses[] = $row['course'];
    }
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management Module</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f5f7;
            margin: 0;
            padding: 25px;
            color: #263238;
        }
        .container {
            max-width: 1100px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
        }
        h1, h2 { color: #2e4141; }
        input, select, button {
            padding: 10px;
            margin: 5px 3px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button, .action {
            background: #2e6060;
            color: white;
            border: none;
            padding: 9px 12px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .delete { background: #b83232; }
        .message {
            padding: 12px;
            background: #e5f4e8;
            border-radius: 5px;
            margin: 15px 0;
        }
        .form-box, .search-box {
            background: #f7f9fa;
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 11px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th { background: #2e6060; color: white; }
        tr:nth-child(even) { background: #f7f9fa; }
        @media (max-width: 700px) {
            body { padding: 8px; }
            .container { padding: 12px; overflow-x: auto; }
        }
    </style>
</head>
<body>
<div class="container">

    <h1>Student Management Module</h1>
    <p>Add, view, edit, delete, search and filter student records.</p>

    <?php if ($message !== ""): ?>
        <div class="message"><?= e($message) ?></div>
    <?php endif; ?>

    <div class="form-box">
        <h2><?= $editStudent ? "Edit Student" : "Add New Student" ?></h2>

        <form method="POST" action="student_module.php">
            <?php if ($editStudent): ?>
                <input type="hidden" name="student_id"
                       value="<?= e($editStudent['student_id']) ?>">
            <?php endif; ?>

            <input type="text" name="name" placeholder="Student Name"
                   required maxlength="100"
                   value="<?= e($editStudent['name'] ?? '') ?>">

            <input type="email" name="email" placeholder="Email"
                   required maxlength="100"
                   value="<?= e($editStudent['email'] ?? '') ?>">

            <input type="text" name="course" placeholder="Course"
                   required maxlength="100"
                   value="<?= e($editStudent['course'] ?? '') ?>">

            <select name="semester" required>
                <option value="">Select Semester</option>
                <?php for ($i = 1; $i <= 8; $i++): ?>
                    <option value="<?= $i ?>"
                        <?= isset($editStudent['semester']) &&
                            (int)$editStudent['semester'] === $i
                            ? 'selected' : '' ?>>
                        Semester <?= $i ?>
                    </option>
                <?php endfor; ?>
            </select>

            <?php if ($editStudent): ?>
                <button type="submit" name="update">Update Student</button>
                <a class="action" href="student_module.php">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add">Add Student</button>
            <?php endif; ?>
        </form>
    </div>

    <div class="search-box">
        <h2>Search and Filter Students</h2>

        <form method="GET" action="student_module.php">
            <input type="text" name="search"
                   placeholder="Search name or email"
                   value="<?= e($search) ?>">

            <select name="course">
                <option value="">All Courses</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?= e($c) ?>"
                        <?= $filterCourse === $c ? 'selected' : '' ?>>
                        <?= e($c) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="semester">
                <option value="">All Semesters</option>
                <?php for ($i = 1; $i <= 8; $i++): ?>
                    <option value="<?= $i ?>"
                        <?= $filterSemester === $i ? 'selected' : '' ?>>
                        Semester <?= $i ?>
                    </option>
                <?php endfor; ?>
            </select>

            <button type="submit">Search / Filter</button>
            <a class="action" href="student_module.php">Reset</a>
        </form>
    </div>

    <h2>Student Records</h2>

    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Course</th>
            <th>Semester</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($student = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= e($student['student_id']) ?></td>
                    <td><?= e($student['name']) ?></td>
                    <td><?= e($student['email']) ?></td>
                    <td><?= e($student['course']) ?></td>
                    <td><?= e($student['semester']) ?></td>
                    <td>
                        <a class="action"
                           href="student_module.php?edit=<?= (int)$student['student_id'] ?>">
                           Edit
                        </a>
                        <a class="action delete"
                           href="student_module.php?delete=<?= (int)$student['student_id'] ?>"
                           onclick="return confirm('Are you sure you want to delete this student?')">
                           Delete
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No student records found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

</div>
</body>
</html>

<?php
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>