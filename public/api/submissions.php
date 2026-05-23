<?php
require_once 'config.php';
header('Content-Type: application/json');
$user = $_SESSION['user'] ?? null;
if (!$user) { http_response_code(401); exit; }

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST' && $user['role'] === 'student') {
    $activity_id = $_POST['activity_id'] ?? null;
    $content = $_POST['content'] ?? '';
    $file_path = '';
    if (!$activity_id) { http_response_code(400); echo json_encode(['error'=>'Actividad requerida']); exit; }
    $stmt = $pdo->prepare("
        SELECT a.activity_type, u.course_id
        FROM activities a
        JOIN units u ON a.unit_id = u.id
        WHERE a.id = ?
    ");
    $stmt->execute([$activity_id]);
    $activity = $stmt->fetch();
    if (!$activity) { http_response_code(404); echo json_encode(['error'=>'Actividad no encontrada']); exit; }
    $stmt = $pdo->prepare("SELECT 1 FROM enrollments WHERE course_id = ? AND user_id = ?");
    $stmt->execute([$activity['course_id'], $user['id']]);
    if (!$stmt->fetch()) { http_response_code(403); echo json_encode(['error'=>'No matriculado']); exit; }
    if (!empty($_FILES['file']['name'])) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['file']['name']);
        move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $filename);
        $file_path = 'uploads/' . $filename;
    }
    if (in_array($activity['activity_type'], ['quiz','evaluation'])) {
        $answers = json_decode($_POST['answers'] ?? '{}', true);
        if (!is_array($answers)) $answers = [];
        $content = json_encode(['answers' => $answers], JSON_UNESCAPED_UNICODE);
    }
    $pdo->prepare("DELETE g FROM grades g JOIN submissions s ON g.submission_id = s.id WHERE s.activity_id = ? AND s.user_id = ?")->execute([$activity_id, $user['id']]);
    $pdo->prepare("DELETE FROM submissions WHERE activity_id = ? AND user_id = ?")->execute([$activity_id, $user['id']]);
    $stmt = $pdo->prepare("INSERT INTO submissions (activity_id, user_id, content, file_path) VALUES (?,?,?,?)");
    $stmt->execute([$activity_id, $user['id'], $content, $file_path]);
    $submission_id = $pdo->lastInsertId();
    $grade = null;
    if (in_array($activity['activity_type'], ['quiz','evaluation'])) {
        $stmt = $pdo->prepare("SELECT id, correct_option FROM quiz_questions WHERE activity_id = ?");
        $stmt->execute([$activity_id]);
        $questions = $stmt->fetchAll();
        $correct = 0;
        foreach ($questions as $q) {
            if (($answers[$q['id']] ?? '') === $q['correct_option']) $correct++;
        }
        $grade = count($questions) ? round(($correct / count($questions)) * 10, 1) : 0;
        $stmt = $pdo->prepare("INSERT INTO grades (submission_id, grade) VALUES (?, ?)");
        $stmt->execute([$submission_id, $grade]);
    }
    echo json_encode(['success'=>true, 'grade'=>$grade]);
    exit;
}
if ($method === 'GET') {
    $activity_id = $_GET['activity_id'] ?? null;
    if (!$activity_id) { http_response_code(400); exit; }
    if (in_array($user['role'], ['teacher','admin'])) {
        $stmt = $pdo->prepare("SELECT s.*, u.name AS student_name FROM submissions s JOIN users u ON s.user_id = u.id WHERE s.activity_id = ?");
        $stmt->execute([$activity_id]);
        echo json_encode($stmt->fetchAll());
        exit;
    }
    if ($user['role'] === 'student') {
        $stmt = $pdo->prepare("SELECT s.*, u.name AS student_name FROM submissions s JOIN users u ON s.user_id = u.id WHERE s.activity_id = ? AND s.user_id = ?");
        $stmt->execute([$activity_id, $user['id']]);
        echo json_encode($stmt->fetchAll());
        exit;
    }
    http_response_code(403);
    exit;
}
http_response_code(405);
