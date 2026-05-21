<?php
require_once 'config.php';
header('Content-Type: application/json');
$user = $_SESSION['user'] ?? null;
if (!$user) { http_response_code(401); exit; }

$method = $_SERVER['REQUEST_METHOD'];

// GET
if ($method === 'GET') {
    $course_id = $_GET['course_id'] ?? null;
    if (!$course_id) { http_response_code(400); exit; }
    $stmt = $pdo->prepare("SELECT * FROM units WHERE course_id = ? ORDER BY order_index");
    $stmt->execute([$course_id]);
    echo json_encode($stmt->fetchAll());
    exit;
}

// POST (profesor o admin)
if ($method === 'POST' && in_array($user['role'], ['teacher','admin'])) {
    $data = json_decode(file_get_contents('php://input'), true);
    if ($user['role'] === 'teacher') {
        $stmt = $pdo->prepare("SELECT teacher_id FROM courses WHERE id=?");
        $stmt->execute([$data['course_id']]);
        $c = $stmt->fetch();
        if (!$c || $c['teacher_id'] != $user['id']) { http_response_code(403); exit; }
    }
    $stmt = $pdo->prepare("INSERT INTO units (course_id, title, description, icon_class, order_index) VALUES (?,?,?,?,?)");
    $stmt->execute([$data['course_id'], $data['title'], $data['description']??'', $data['icon_class']??'gen', $data['order_index']??0]);
    echo json_encode(['success'=>true, 'id'=>$pdo->lastInsertId()]);
    exit;
}

// PUT
if ($method === 'PUT' && in_array($user['role'], ['teacher','admin'])) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    if (!$id) { http_response_code(400); exit; }
    // verificar propiedad
    // ...
    $stmt = $pdo->prepare("UPDATE units SET title=?, description=?, icon_class=?, order_index=? WHERE id=?");
    $stmt->execute([$data['title'], $data['description']??'', $data['icon_class']??'gen', $data['order_index']??0, $id]);
    echo json_encode(['success'=>true]);
    exit;
}

// DELETE
if ($method === 'DELETE' && in_array($user['role'], ['teacher','admin'])) {
    $id = $_GET['id'] ?? null;
    if (!$id) { http_response_code(400); exit; }
    // verificar propiedad...
    $stmt = $pdo->prepare("DELETE FROM units WHERE id=?");
    $stmt->execute([$id]);
    echo json_encode(['success'=>true]);
    exit;
}
http_response_code(405);