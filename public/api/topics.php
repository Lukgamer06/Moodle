<?php
require_once 'config.php';
header('Content-Type: application/json');
$user = $_SESSION['user'] ?? null;
if (!$user) { http_response_code(401); exit; }

function canAccessForum(PDO $pdo, array $user, $forum_id): bool {
    if ($user['role'] === 'admin') return true;
    $stmt = $pdo->prepare("
        SELECT 1 FROM forums f
        JOIN courses c ON f.course_id = c.id
        WHERE f.id = ? AND (
            c.teacher_id = ? OR 
            EXISTS (SELECT 1 FROM enrollments WHERE course_id = c.id AND user_id = ?)
        )
    ");
    $stmt->execute([$forum_id, $user['id'], $user['id']]);
    return (bool) $stmt->fetch();
}

$method = $_SERVER['REQUEST_METHOD'];

// GET: Obtener temas de un foro
if ($method === 'GET') {
    $forum_id = $_GET['forum_id'] ?? null;
    if (!$forum_id) { http_response_code(400); exit; }
    if (!canAccessForum($pdo, $user, $forum_id)) { http_response_code(403); exit; }
    
    $stmt = $pdo->prepare("
        SELECT t.id, t.forum_id, t.title, t.description, t.created_by, u.name as created_by_name, t.created_at, t.updated_at
        FROM forum_topics t
        LEFT JOIN users u ON t.created_by = u.id
        WHERE t.forum_id = ?
        ORDER BY t.updated_at DESC
    ");
    $stmt->execute([$forum_id]);
    echo json_encode($stmt->fetchAll());
    exit;
}

// POST: Crear nuevo tema
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $forum_id = $data['forum_id'] ?? null;
    $title = trim($data['title'] ?? '');
    $description = trim($data['description'] ?? '');
    
    if (!$forum_id || $title === '') { 
        http_response_code(400); 
        echo json_encode(['error' => 'Faltan datos']);
        exit;
    }
    
    if (!canAccessForum($pdo, $user, $forum_id)) { 
        http_response_code(403); 
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO forum_topics (forum_id, title, description, created_by)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$forum_id, $title, $description, $user['id']]);
    
    echo json_encode([
        'success' => true, 
        'id' => $pdo->lastInsertId(),
        'created_by' => $user['name'],
        'created_at' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// DELETE: Eliminar tema (solo creador o admin)
if ($method === 'DELETE' && in_array($user['role'], ['teacher','admin'])) {
    $id = $_GET['id'] ?? null;
    $topic_id = $_GET['topic_id'] ?? null;
    
    if (!$topic_id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de tema requerido']);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT created_by, forum_id FROM forum_topics WHERE id = ?");
    $stmt->execute([$topic_id]);
    $topic = $stmt->fetch();
    
    if (!$topic) {
        http_response_code(404);
        echo json_encode(['error' => 'Tema no encontrado']);
        exit;
    }
    
    if ($user['role'] !== 'admin' && $topic['created_by'] != $user['id']) {
        http_response_code(403);
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }
    
    if (!canAccessForum($pdo, $user, $topic['forum_id'])) {
        http_response_code(403);
        exit;
    }
    
    $stmt = $pdo->prepare("DELETE FROM forum_topics WHERE id = ?");
    $stmt->execute([$topic_id]);
    
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
