<?php
require_once 'config.php';
header('Content-Type: application/json');
$user = $_SESSION['user'] ?? null;
if (!$user) { http_response_code(401); exit; }

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    $forum_id = $_GET['forum_id'] ?? null;
    if (!$forum_id) { http_response_code(400); exit; }
    $stmt = $pdo->prepare("SELECT m.*, u.name AS user_name FROM forum_messages m JOIN users u ON m.user_id = u.id WHERE m.forum_id = ? ORDER BY m.created_at");
    $stmt->execute([$forum_id]);
    echo json_encode($stmt->fetchAll());
    exit;
}
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT INTO forum_messages (forum_id, user_id, content) VALUES (?,?,?)");
    $stmt->execute([$data['forum_id'], $user['id'], $data['content']]);
    echo json_encode(['success'=>true]);
    exit;
}
http_response_code(405);