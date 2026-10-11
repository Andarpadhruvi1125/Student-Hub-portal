<?php
$conn = new mysqli("localhost", "root", "", "studenthub");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$uploadDir = __DIR__ . "/uploads/";
$uploadUrl = "uploads/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$message = "";

function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add"])) {
    $name = trim($_POST["event_name"] ?? "");
    $date = $_POST["event_date"] ?? "";
    $venue = trim($_POST["venue"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $poster = null;

    if ($name === "" || $date === "" || $venue === "" || $description === "") {
        $message = "Please fill all fields.";
    } else {
        if (isset($_FILES["poster"]) &&
            $_FILES["poster"]["error"] !== UPLOAD_ERR_NO_FILE) {

            $file = $_FILES["poster"];

            if ($file["error"] !== UPLOAD_ERR_OK) {
                $message = "Poster upload failed.";
            } elseif ($file["size"] > 2 * 1024 * 1024) {
                $message = "Poster size must be below 2 MB.";
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($file["tmp_name"]);

                $types = [
                    "image/jpeg" => "jpg",
                    "image/png" => "png",
                    "image/webp" => "webp"
                ];

                if (!isset($types[$mime])) {
                    $message = "Only JPG, PNG and WEBP files are allowed.";
                } else {
                    $poster = bin2hex(random_bytes(16)) . "." . $types[$mime];

                    if (!move_uploaded_file(
                        $file["tmp_name"],
                        $uploadDir . $poster
                    )) {
                        $message = "Could not save poster.";
                        $poster = null;
                    }
                }
            }
        }

        if ($message === "") {
            $stmt = $conn->prepare(
                "INSERT INTO events
                (event_name, event_date, venue, description, poster)
                VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "sssss",
                $name, $date, $venue, $description, $poster
            );

            if ($stmt->execute()) {
                header("Location: event.php?msg=added");
                exit;
            }

            $message = "Unable to add event.";
            if ($poster && is_file($uploadDir . $poster)) {
                unlink($uploadDir . $poster);
            }
            $stmt->close();
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update"])) {
    $id = filter_input(INPUT_POST, "event_id", FILTER_VALIDATE_INT);
    $name = trim($_POST["event_name"] ?? "");
    $date = $_POST["event_date"] ?? "";
    $venue = trim($_POST["venue"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if (!$id || $name === "" || $date === "" ||
        $venue === "" || $description === "") {
        $message = "Please fill all fields correctly.";
    } else {
        $stmt = $conn->prepare(
            "SELECT poster FROM events WHERE event_id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($oldPoster);

        if (!$stmt->fetch()) {
            $message = "Event not found.";
            $stmt->close();
        } else {
            $stmt->close();
            $poster = $oldPoster;
            $newPoster = null;

            if (isset($_FILES["poster"]) &&
                $_FILES["poster"]["error"] !== UPLOAD_ERR_NO_FILE) {

                $file = $_FILES["poster"];

                if ($file["error"] !== UPLOAD_ERR_OK) {
                    $message = "Poster upload failed.";
                } elseif ($file["size"] > 2 * 1024 * 1024) {
                    $message = "Poster must be below 2 MB.";
                } else {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($file["tmp_name"]);

                    $types = [
                        "image/jpeg" => "jpg",
                        "image/png" => "png",
                        "image/webp" => "webp"
                    ];

                    if (!isset($types[$mime])) {
                        $message = "Only JPG, PNG and WEBP files are allowed.";
                    } else {
                        $newPoster = bin2hex(random_bytes(16))
                            . "." . $types[$mime];

                        if (move_uploaded_file(
                            $file["tmp_name"],
                            $uploadDir . $newPoster
                        )) {
                            $poster = $newPoster;
                        } else {
                            $message = "Could not save poster.";
                        }
                    }
                }
            }

            if ($message === "") {
                $stmt = $conn->prepare(
                    "UPDATE events SET event_name=?, event_date=?,
                    venue=?, description=?, poster=? WHERE event_id=?"
                );

                $stmt->bind_param(
                    "sssssi",
                    $name, $date, $venue, $description, $poster, $id
                );

                if ($stmt->execute()) {
                    $stmt->close();

                    if ($newPoster && $oldPoster &&
                        is_file($uploadDir . $oldPoster)) {
                        unlink($uploadDir . $oldPoster);
                    }

                    header("Location: event.php?msg=updated");
                    exit;
                }

                $message = "Unable to update event.";
                $stmt->close();

                if ($newPoster && is_file($uploadDir . $newPoster)) {
                    unlink($uploadDir . $newPoster);
                }
            }
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete"])) {
    $id = filter_input(INPUT_POST, "event_id", FILTER_VALIDATE_INT);

    if ($id) {
        $stmt = $conn->prepare(
            "SELECT poster FROM events WHERE event_id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($poster);

        if ($stmt->fetch()) {
            $stmt->close();

            $stmt = $conn->prepare(
                "DELETE FROM events WHERE event_id = ?"
            );
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                $stmt->close();

                if ($poster && is_file($uploadDir . $poster)) {
                    unlink($uploadDir . $poster);
                }

                header("Location: event.php?msg=deleted");
                exit;
            }

            $stmt->close();
            $message = "Unable to delete event.";
        } else {
            $stmt->close();
            $message = "Event not found.";
        }
    } else {
        $message = "Invalid event ID.";
    }
}

$edit = null;

if (isset($_GET["edit"])) {
    $id = filter_input(INPUT_GET, "edit", FILTER_VALIDATE_INT);

    if ($id) {
        $stmt = $conn->prepare(
            "SELECT * FROM events WHERE event_id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $edit = $result->fetch_assoc();
        $stmt->close();
    }
}

if (isset($_GET["msg"])) {
    $messages = [
        "added" => "Event added successfully!",
        "updated" => "Event updated successfully!",
        "deleted" => "Event deleted successfully!"
    ];
    $message = $messages[$_GET["msg"]] ?? "";
}

$events = $conn->query(
    "SELECT * FROM events ORDER BY event_date DESC, event_id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StudentHub Event Management</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f6f8;
            color: #293744;
            padding: 25px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        .box, .event {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            margin: 8px 0 14px;
            box-sizing: border-box;
        }

        button, .btn {
            padding: 10px 14px;
            border: none;
            border-radius: 5px;
            background: #294c60;
            color: white;
            text-decoration: none;
            cursor: pointer;
        }

        .delete {
            background: #b84040;
        }

        .message {
            padding: 12px;
            background: #e0f3e5;
            margin-bottom: 15px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 15px;
        }

        .poster {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
    </style>
</head>
<body>
<div class="container">

    <h1>🎓 StudentHub Event Management</h1>

    <?php if ($message !== ""): ?>
        <div class="message"><?= clean($message) ?></div>
    <?php endif; ?>

    <div class="box">
        <h2><?= $edit ? "Update Event" : "Add New Event" ?></h2>

        <form method="POST" enctype="multipart/form-data">
            <?php if ($edit): ?>
                <input type="hidden" name="event_id"
                       value="<?= (int)$edit["event_id"] ?>">
            <?php endif; ?>

            <label>Event Name</label>
            <input type="text" name="event_name" maxlength="150"
                   required value="<?= clean($edit["event_name"] ?? "") ?>">

            <label>Event Date</label>
            <input type="date" name="event_date" required
                   value="<?= clean($edit["event_date"] ?? "") ?>">

            <label>Venue</label>
            <input type="text" name="venue" required
                   value="<?= clean($edit["venue"] ?? "") ?>">

            <label>Description</label>
            <textarea name="description" required><?= clean($edit["description"] ?? "") ?></textarea>

            <label>Poster (JPG, PNG, WEBP; maximum 2 MB)</label>
            <input type="file" name="poster"
                   accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">

            <?php if ($edit && !empty($edit["poster"])): ?>
                <p>Current poster:</p>
                <img src="<?= clean($uploadUrl . $edit["poster"]) ?>"
                     alt="Current poster" width="120">
                <p>Leave empty to keep the existing poster.</p>
            <?php endif; ?>

            <button type="submit"
                    name="<?= $edit ? "update" : "add" ?>">
                <?= $edit ? "Update Event" : "Add Event" ?>
            </button>

            <?php if ($edit): ?>
                <a class="btn" href="event.php">Cancel</a>
            <?php endif; ?>
        </form>
    </div>

    <h2>All Events</h2>

    <div class="grid">
        <?php while ($row = $events->fetch_assoc()): ?>
            <div class="event">

                <?php if (!empty($row["poster"]) &&
                    is_file($uploadDir . basename($row["poster"]))): ?>
                    <img class="poster"
                         src="<?= clean($uploadUrl . $row["poster"]) ?>"
                         alt="Event poster">
                <?php else: ?>
                    <p>No poster uploaded</p>
                <?php endif; ?>

                <h3><?= clean($row["event_name"]) ?></h3>
                <p><b>Date:</b> <?= clean($row["event_date"]) ?></p>
                <p><b>Venue:</b> <?= clean($row["venue"] ?? "") ?></p>
                <p><?= nl2br(clean($row["description"] ?? "")) ?></p>

                <a class="btn"
                   href="event.php?edit=<?= (int)$row["event_id"] ?>">
                    Edit
                </a>

                <form method="POST"
                      onsubmit="return confirm('Delete this event?');">
                    <input type="hidden" name="event_id"
                           value="<?= (int)$row["event_id"] ?>">
                    <button class="delete" type="submit" name="delete">
                        Delete
                    </button>
                </form>

            </div>
        <?php endwhile; ?>
    </div>

</div>
</body>
</html>