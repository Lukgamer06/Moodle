<?php
require_once 'config.php';
header('Content-Type: application/json');
$user = $_SESSION['user'] ?? null;
if (!$user) { http_response_code(401); exit; }

$method = $_SERVER['REQUEST_METHOD'];

// GET: Obtener mensajes de un foro o tema
if ($method === 'GET') {
    $forum_id = $_GET['forum_id'] ?? null;
    $topic_id = $_GET['topic_id'] ?? null;
    
    if (!$forum_id && !$topic_id) { 
        http_response_code(400); 
        exit; 
    }
    
    if ($topic_id) {
        // Obtener mensajes de un tema específico
        $stmt = $pdo->prepare("
            SELECT m.*, u.name AS user_name 
            FROM forum_messages m 
            JOIN users u ON m.user_id = u.id 
            WHERE m.topic_id = ? 
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([$topic_id]);
    } else {
        // Obtener mensajes generales del foro (sin tema)
        $stmt = $pdo->prepare("
            SELECT m.*, u.name AS user_name 
            FROM forum_messages m 
            JOIN users u ON m.user_id = u.id 
            WHERE m.forum_id = ? AND m.topic_id IS NULL
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([$forum_id]);
    }
    
    echo json_encode($stmt->fetchAll());
    exit;
}

// POST: Crear nuevo mensaje
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $forum_id = $data['forum_id'] ?? null;
    $topic_id = $data['topic_id'] ?? null;
    $content = trim($data['content'] ?? '');
    
    if (!$content || !$forum_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Faltan datos']);
        exit;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO forum_messages (forum_id, topic_id, user_id, content)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$forum_id, $topic_id, $user['id'], $content]);
    
    echo json_encode([
        'success' => true,
        'id' => $pdo->lastInsertId(),
        'user_name' => $user['name'],
        'created_at' => date('Y-m-d H:i:s')
    ]);
    exit;
}

http_response_code(405);
