<?php
require_once 'config.php';
header('Content-Type: application/json');
$user = $_SESSION['user'] ?? null;
if (!$user) { http_response_code(401); exit; }

$method = $_SERVER['REQUEST_METHOD'];

function canEditUnit(PDO $pdo, array $user, $unit_id): bool {
    if ($user['role'] === 'admin') return true;
    if ($user['role'] !== 'teacher') return false;
    $stmt = $pdo->prepare("SELECT c.teacher_id FROM units u JOIN courses c ON u.course_id = c.id WHERE u.id = ?");
    $stmt->execute([$unit_id]);
    $course = $stmt->fetch();
    return $course && $course['teacher_id'] == $user['id'];
}

function saveQuestions(PDO $pdo, $activity_id, array $questions): void {
    $pdo->prepare("DELETE FROM quiz_questions WHERE activity_id = ?")->execute([$activity_id]);
    $stmt = $pdo->prepare("
        INSERT INTO quiz_questions (activity_id, question, option_a, option_b, option_c, option_d, correct_option, order_index)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    foreach ($questions as $index => $q) {
        $question = trim($q['question'] ?? '');
        $correct = strtolower($q['correct_option'] ?? '');
        if ($question === '' || !in_array($correct, ['a','b','c','d'])) continue;
        $stmt->execute([
            $activity_id,
            $question,
            trim($q['option_a'] ?? ''),
            trim($q['option_b'] ?? ''),
            trim($q['option_c'] ?? ''),
            trim($q['option_d'] ?? ''),
            $correct,
            $index
        ]);
    }
}

if ($method === 'GET') {
    $unit_id = $_GET['unit_id'] ?? null;
    if (!$unit_id) { http_response_code(400); exit; }
    $stmt = $pdo->prepare("SELECT * FROM activities WHERE unit_id = ?");
    $stmt->execute([$unit_id]);
    $activities = $stmt->fetchAll();
    if (isset($_GET['include_questions'])) {
        $qStmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE activity_id = ? ORDER BY order_index, id");
        foreach ($activities as &$activity) {
            $qStmt->execute([$activity['id']]);
            $questions = $qStmt->fetchAll();
            if ($user['role'] === 'student') {
                foreach ($questions as &$question) unset($question['correct_option']);
            }
            $activity['questions'] = $questions;
        }
    }
    echo json_encode($activities);
    exit;
}
if ($method === 'POST' && in_array($user['role'], ['teacher','admin'])) {
    $data = json_decode(file_get_contents('php://input'), true);
    $unit_id = $data['unit_id'] ?? null;
    $type = $data['activity_type'] ?? 'activity';
    if (!$unit_id || !canEditUnit($pdo, $user, $unit_id)) { http_response_code(403); exit; }
    if (!in_array($type, ['activity','quiz','evaluation'])) $type = 'activity';
    $stmt = $pdo->prepare("INSERT INTO activities (unit_id, activity_type, title, description, due_date) VALUES (?,?,?,?,?)");
    $stmt->execute([$unit_id, $type, $data['title'], $data['description']??'', $data['due_date']??null]);
    $id = $pdo->lastInsertId();
    if (in_array($type, ['quiz','evaluation'])) saveQuestions($pdo, $id, $data['questions'] ?? []);
    echo json_encode(['success'=>true, 'id'=>$id]);
    exit;
}
if ($method === 'PUT' && in_array($user['role'], ['teacher','admin'])) {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("SELECT unit_id FROM activities WHERE id = ?");
    $stmt->execute([$data['id'] ?? null]);
    $activity = $stmt->fetch();
    if (!$activity || !canEditUnit($pdo, $user, $activity['unit_id'])) { http_response_code(403); exit; }
    $type = $data['activity_type'] ?? 'activity';
    if (!in_array($type, ['activity','quiz','evaluation'])) $type = 'activity';
    $stmt = $pdo->prepare("UPDATE activities SET activity_type=?, title=?, description=?, due_date=? WHERE id=?");
    $stmt->execute([$type, $data['title'], $data['description']??'', $data['due_date']??null, $data['id']]);
    if (in_array($type, ['quiz','evaluation'])) saveQuestions($pdo, $data['id'], $data['questions'] ?? []);
    else $pdo->prepare("DELETE FROM quiz_questions WHERE activity_id = ?")->execute([$data['id']]);
    echo json_encode(['success'=>true]);
    exit;
}
if ($method === 'DELETE' && in_array($user['role'], ['teacher','admin'])) {
    $id = $_GET['id'] ?? null;
    if (!$id) { http_response_code(400); exit; }
    $stmt = $pdo->prepare("SELECT unit_id FROM activities WHERE id = ?");
    $stmt->execute([$id]);
    $activity = $stmt->fetch();
    if (!$activity || !canEditUnit($pdo, $user, $activity['unit_id'])) { http_response_code(403); exit; }
    $stmt = $pdo->prepare("DELETE FROM activities WHERE id=?");
    $stmt->execute([$id]);
    echo json_encode(['success'=>true]);
    exit;
}
http_response_code(405);
