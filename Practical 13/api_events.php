
<?php
header("Content-Type: application/json; charset=UTF-8");

$conn = new mysqli("localhost", "root", "", "studenthub");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed"]);
    exit;
}

$method = $_SERVER["REQUEST_METHOD"];
$data = json_decode(file_get_contents("php://input"), true) ?? [];

if ($method === "GET") {
    $result = $conn->query(
        "SELECT id, title, description, event_date
         FROM events_api ORDER BY id DESC"
    );

    $events = [];

    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }

    echo json_encode($events);
}

elseif ($method === "POST") {
    $title = trim($data["title"] ?? "");
    $description = trim($data["description"] ?? "");
    $date = $data["event_date"] ?? "";

    if ($title === "" || $description === "" ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ||
        !checkdate((int)substr($date, 5, 2),
                   (int)substr($date, 8, 2),
                   (int)substr($date, 0, 4))) {
        http_response_code(400);
        echo json_encode(["message" => "Enter valid event details"]);
        exit;
    }

    $stmt = $conn->prepare(
        "INSERT INTO events_api (title, description, event_date)
         VALUES (?, ?, ?)"
    );
    $stmt->bind_param("sss", $title, $description, $date);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Event added successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Unable to add event"]);
    }
}

elseif ($method === "PUT") {
    $id = (int)($data["id"] ?? 0);
    $title = trim($data["title"] ?? "");
    $description = trim($data["description"] ?? "");
    $date = $data["event_date"] ?? "";

    if ($id <= 0 || $title === "" || $description === "" ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ||
        !checkdate((int)substr($date, 5, 2),
                   (int)substr($date, 8, 2),
                   (int)substr($date, 0, 4))) {
        http_response_code(400);
        echo json_encode(["message" => "Enter valid event details"]);
        exit;
    }

    $stmt = $conn->prepare(
        "UPDATE events_api
         SET title = ?, description = ?, event_date = ?
         WHERE id = ?"
    );
    $stmt->bind_param("sssi", $title, $description, $date, $id);
    $stmt->execute();

    $check = $conn->prepare("SELECT id FROM events_api WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $exists = $check->get_result()->num_rows > 0;

    if (!$exists) {
        http_response_code(404);
        echo json_encode(["message" => "Event not found"]);
    } else {
        echo json_encode(["message" => "Event updated successfully"]);
    }
}

elseif ($method === "DELETE") {
    $id = (int)($data["id"] ?? 0);

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid event ID"]);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM events_api WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(["message" => "Event deleted successfully"]);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Event not found"]);
    }
}

else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
}

$conn->close();
?>
