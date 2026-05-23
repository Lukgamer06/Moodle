<?php
require_once 'config.php';
header('Content-Type: application/json');
$user = $_SESSION['user'] ?? null;
if (!$user) { http_response_code(401); exit; }

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $unit_id = $_GET['unit_id'] ?? null;
    if (!$unit_id) { http_response_code(400); exit; }
    $stmt = $pdo->prepare("SELECT * FROM resources WHERE unit_id = ?");
    $stmt->execute([$unit_id]);
    echo json_encode($stmt->fetchAll());
    exit;
}
if ($method === 'POST' && in_array($user['role'], ['teacher','admin'])) {
    $unit_id = $_POST['unit_id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $type = $_POST['type'] ?? 'doc';
    $meta = $_POST['meta'] ?? '';
    $file_path = '';
    if (!$unit_id || $name === '') { http_response_code(400); echo json_encode(['error'=>'Faltan datos']); exit; }
    if ($type === 'video') {
        $url = trim($_POST['youtube_url'] ?? '');
        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
            http_response_code(400); echo json_encode(['error'=>'Enlace de video inválido']); exit;
        }
        $file_path = $url;
    } elseif (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        if ($type === 'pdf' && strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION)) !== 'pdf') {
            http_response_code(400); echo json_encode(['error'=>'El archivo debe ser PDF']); exit;
        }
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['file']['name']);
        move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $filename);
        $file_path = 'uploads/' . $filename;
    } elseif ($type === 'pdf') {
        http_response_code(400); echo json_encode(['error'=>'Carga un PDF']); exit;
    }
    $stmt = $pdo->prepare("INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES (?,?,?,?,?)");
    $stmt->execute([$unit_id, $type, $name, $file_path, $meta]);
    echo json_encode(['success'=>true]);
    exit;
}
if ($method === 'DELETE' && in_array($user['role'], ['teacher','admin'])) {
    $id = $_GET['id'] ?? null;
    if (!$id) { http_response_code(400); exit; }
    $stmt = $pdo->prepare("DELETE FROM resources WHERE id=?");
    $stmt->execute([$id]);
    echo json_encode(['success'=>true]);
    exit;
}
http_response_code(405);
