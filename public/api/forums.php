<?php
require_once 'config.php';
header('Content-Type: application/json');
$user = $_SESSION['user'] ?? null;
if (!$user) { http_response_code(401); exit; }

function canAccessCourse(PDO $pdo, array $user, $course_id): bool {
    if ($user['role'] === 'admin') return true;
    if ($user['role'] === 'teacher') {
        $stmt = $pdo->prepare("SELECT 1 FROM courses WHERE id = ? AND teacher_id = ?");
        $stmt->execute([$course_id, $user['id']]);
        return (bool) $stmt->fetch();
    }
    $stmt = $pdo->prepare("SELECT 1 FROM enrollments WHERE course_id = ? AND user_id = ?");
    $stmt->execute([$course_id, $user['id']]);
    return (bool) $stmt->fetch();
}

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    $course_id = $_GET['course_id'] ?? null;
    if (!$course_id) { http_response_code(400); exit; }
    if (!canAccessCourse($pdo, $user, $course_id)) { http_response_code(403); exit; }
    $stmt = $pdo->prepare("SELECT * FROM forums WHERE course_id = ?");
    $stmt->execute([$course_id]);
    echo json_encode($stmt->fetchAll());
    exit;
}
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $course_id = $data['course_id'] ?? null;
    $title = trim($data['title'] ?? '');
    if (!$course_id || $title === '') { http_response_code(400); echo json_encode(['error'=>'Faltan datos']); exit; }
    if (!canAccessCourse($pdo, $user, $course_id)) { http_response_code(403); echo json_encode(['error'=>'No autorizado']); exit; }
    $stmt = $pdo->prepare("INSERT INTO forums (course_id, title, description, created_by) VALUES (?,?,?,?)");
    $stmt->execute([$course_id, $title, $data['description']??'', $user['id']]);
    echo json_encode(['success'=>true, 'id'=>$pdo->lastInsertId()]);
    exit;
}
if ($method === 'DELETE' && in_array($user['role'], ['teacher','admin'])) {
    $id = $_GET['id'] ?? null;
    $stmt = $pdo->prepare("DELETE FROM forums WHERE id=?");
    $stmt->execute([$id]);
    echo json_encode(['success'=>true]);
    exit;
}
http_response_code(405);
