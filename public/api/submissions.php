<?php
require_once 'config.php';
header('Content-Type: application/json');
$user = $_SESSION['user'] ?? null;
if (!$user) { http_response_code(401); exit; }

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST' && $user['role'] === 'student') {
    $activity_id = $_POST['activity_id'];
    $content = $_POST['content'] ?? '';
    $file_path = '';
    if (!empty($_FILES['file']['name'])) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['file']['name']);
        move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $filename);
        $file_path = 'uploads/' . $filename;
    }
    $stmt = $pdo->prepare("INSERT INTO submissions (activity_id, user_id, content, file_path) VALUES (?,?,?,?)");
    $stmt->execute([$activity_id, $user['id'], $content, $file_path]);
    echo json_encode(['success'=>true]);
    exit;
}
if ($method === 'GET' && in_array($user['role'], ['teacher','admin'])) {
    $activity_id = $_GET['activity_id'] ?? null;
    if (!$activity_id) { http_response_code(400); exit; }
    $stmt = $pdo->prepare("SELECT s.*, u.name AS student_name FROM submissions s JOIN users u ON s.user_id = u.id WHERE s.activity_id = ?");
    $stmt->execute([$activity_id]);
    echo json_encode($stmt->fetchAll());
    exit;
}
http_response_code(405);