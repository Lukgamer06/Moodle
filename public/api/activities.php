<?php
require_once 'config.php';
header('Content-Type: application/json');
$user = $_SESSION['user'] ?? null;
if (!$user) { http_response_code(401); exit; }

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $unit_id = $_GET['unit_id'] ?? null;
    if (!$unit_id) { http_response_code(400); exit; }
    $stmt = $pdo->prepare("SELECT * FROM activities WHERE unit_id = ?");
    $stmt->execute([$unit_id]);
    echo json_encode($stmt->fetchAll());
    exit;
}
if ($method === 'POST' && in_array($user['role'], ['teacher','admin'])) {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT INTO activities (unit_id, title, description, due_date) VALUES (?,?,?,?)");
    $stmt->execute([$data['unit_id'], $data['title'], $data['description']??'', $data['due_date']??null]);
    echo json_encode(['success'=>true, 'id'=>$pdo->lastInsertId()]);
    exit;
}
if ($method === 'PUT' && in_array($user['role'], ['teacher','admin'])) {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("UPDATE activities SET title=?, description=?, due_date=? WHERE id=?");
    $stmt->execute([$data['title'], $data['description']??'', $data['due_date']??null, $data['id']]);
    echo json_encode(['success'=>true]);
    exit;
}
if ($method === 'DELETE' && in_array($user['role'], ['teacher','admin'])) {
    $id = $_GET['id'] ?? null;
    if (!$id) { http_response_code(400); exit; }
    $stmt = $pdo->prepare("DELETE FROM activities WHERE id=?");
    $stmt->execute([$id]);
    echo json_encode(['success'=>true]);
    exit;
}
http_response_code(405);