<?php
header('Content-Type: application/json');
require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

$fields = [
    'title', 'description', 'location', 'salary', 'company_name', 'employment_type', 'experience_level', 'industry', 'remote_option', 'job_function', 'required_skills', 'education_level', 'application_deadline', 'benefits', 'company_website', 'how_to_apply', 'company_size', 'hiring_manager_contact', 'work_schedule', 'job_duration', 'languages_required', 'posted_by'
];

$bulk = isset($_GET['bulk']) && $_GET['bulk'] == '1';

switch ($method) {
    case 'GET':
        $result = $conn->query('SELECT * FROM jobs ORDER BY created_at DESC');
        $jobs = [];
        while ($row = $result->fetch_assoc()) {
            $jobs[] = $row;
        }
        echo json_encode($jobs);
        break;
    case 'POST':
        if ($bulk) {
            $data = json_decode(file_get_contents('php://input'), true);
            $jobs = $data['jobs'] ?? [];
            $inserted = 0;
            foreach ($jobs as $job) {
                $placeholders = implode(',', array_fill(0, count($fields), '?'));
                $columns = implode(',', $fields);
                $types = str_repeat('s', count($fields));
                $stmt = $conn->prepare("INSERT INTO jobs ($columns) VALUES ($placeholders)");
                $values = [];
                foreach ($fields as $f) {
                    $values[] = $job[$f] ?? '';
                }
                $stmt->bind_param($types, ...$values);
                $stmt->execute();
                if ($stmt->affected_rows > 0) $inserted++;
            }
            echo json_encode(['success' => true, 'count' => $inserted]);
            break;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $placeholders = implode(',', array_fill(0, count($fields), '?'));
        $columns = implode(',', $fields);
        $types = str_repeat('s', count($fields));
        $stmt = $conn->prepare("INSERT INTO jobs ($columns) VALUES ($placeholders)");
        $values = [];
        foreach ($fields as $f) {
            $values[] = $data[$f] ?? '';
        }
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
        echo json_encode(['success' => $stmt->affected_rows > 0]);
        break;
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $set = implode(',', array_map(fn($f) => "$f=?", $fields));
        $types = str_repeat('s', count($fields)) . 'i';
        $stmt = $conn->prepare("UPDATE jobs SET $set WHERE id=?");
        $values = [];
        foreach ($fields as $f) {
            $values[] = $data[$f] ?? '';
        }
        $values[] = $data['job-id'] ?? $data['id'];
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
        echo json_encode(['success' => $stmt->affected_rows > 0]);
        break;
    case 'DELETE':
        $id = intval($_GET['id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM jobs WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        echo json_encode(['success' => $stmt->affected_rows > 0]);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
}
$conn->close();
?> 