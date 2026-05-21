<?php
require_once 'config.php';
header('Content-Type: application/json');
$user = $_SESSION['user'] ?? null;
if (!$user) { http_response_code(401); exit; }

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    $course_id = $_GET['course_id'] ?? null;
    if (!$course_id) { http_response_code(400); exit; }
    $stmt = $pdo->prepare("SELECT * FROM forums WHERE course_id = ?");
    $stmt->execute([$course_id]);
    echo json_encode($stmt->fetchAll());
    exit;
}
if ($method === 'POST' && in_array($user['role'], ['teacher','admin'])) {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT INTO forums (course_id, title, description, created_by) VALUES (?,?,?,?)");
    $stmt->execute([$data['course_id'], $data['title'], $data['description']??'', $user['id']]);
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