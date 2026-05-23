<?php
require_once 'config.php';
header('Content-Type: application/json');

$user = $_SESSION['user'] ?? null;
if (!$user) { http_response_code(401); echo json_encode(['error'=>'No autenticado']); exit; }

$method = $_SERVER['REQUEST_METHOD'];

// GET
if ($method === 'GET') {
    // Si piden ?teachers=1 devolver profesores
    if (isset($_GET['teachers'])) {
        if ($user['role'] !== 'admin') { http_response_code(403); exit; }
        $stmt = $pdo->query("SELECT id, name, email FROM users WHERE role='teacher' ORDER BY name");
        echo json_encode($stmt->fetchAll());
        exit;
    }
    // Si piden ?id= devolver curso individual con datos del profesor
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT c.*, u.name AS teacher_name FROM courses c LEFT JOIN users u ON c.teacher_id = u.id WHERE c.id = ?");
        $stmt->execute([$_GET['id']]);
        $course = $stmt->fetch();
        if (!$course) { http_response_code(404); echo json_encode(['error'=>'Curso no encontrado']); exit; }
        // Si es estudiante, verificar que esté matriculado
        if ($user['role'] === 'student') {
            $stmt2 = $pdo->prepare("SELECT 1 FROM enrollments WHERE user_id=? AND course_id=?");
            $stmt2->execute([$user['id'], $_GET['id']]);
            if (!$stmt2->fetch()) { http_response_code(403); exit; }
        }
        echo json_encode($course);
        exit;
    }
    // Listar según rol
    if ($user['role'] === 'admin') {
        $stmt = $pdo->query("SELECT c.*, u.name AS teacher_name FROM courses c LEFT JOIN users u ON c.teacher_id = u.id");
    } elseif ($user['role'] === 'teacher') {
        $stmt = $pdo->prepare("SELECT c.*, u.name AS teacher_name FROM courses c LEFT JOIN users u ON c.teacher_id = u.id WHERE c.teacher_id = ?");
        $stmt->execute([$user['id']]);
    } else {
        $stmt = $pdo->prepare("SELECT c.*, u.name AS teacher_name FROM courses c JOIN enrollments e ON c.id = e.course_id LEFT JOIN users u ON c.teacher_id = u.id WHERE e.user_id = ?");
        $stmt->execute([$user['id']]);
    }
    echo json_encode($stmt->fetchAll());
    exit;
}

// POST (crear curso) solo admin
if ($method === 'POST' && $user['role'] === 'admin') {
    $data = json_decode(file_get_contents('php://input'), true);
    $name = trim($data['name'] ?? '');
    $desc = trim($data['description'] ?? '');
    $teacher_id = $data['teacher_id'] ?? null;
    if (empty($name)) { http_response_code(400); echo json_encode(['error'=>'Nombre requerido']); exit; }
    $stmt = $pdo->prepare("INSERT INTO courses (name, description, teacher_id) VALUES (?, ?, ?)");
    $stmt->execute([$name, $desc, $teacher_id]);
    echo json_encode(['success'=>true, 'id'=>$pdo->lastInsertId()]);
    exit;
}

// PUT: actualizar curso (admin o profesor del curso)
if ($method === 'PUT' && in_array($user['role'], ['admin','teacher'])) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    if (!$id) { http_response_code(400); echo json_encode(['error'=>'ID requerido']); exit; }
    $stmt = $pdo->prepare("SELECT teacher_id, name, description, card_color FROM courses WHERE id=?");
    $stmt->execute([$id]);
    $course = $stmt->fetch();
    if (!$course) { http_response_code(404); echo json_encode(['error'=>'Curso no encontrado']); exit; }
    if ($user['role'] === 'teacher' && $course['teacher_id'] != $user['id']) { http_response_code(403); echo json_encode(['error'=>'No autorizado']); exit; }
    $allowedColors = ['#FFF9C7', '#C7D1FF', '#FFF8E8', '#C2FFCB', '#FEE3FF', '#FFFFFF'];

    $name = trim($data['name'] ?? $course['name']);
    $desc = $data['description'] ?? $course['description'];
    $teacher_id = $data['teacher_id'] ?? $course['teacher_id'];

    $card_color = $data['card_color'] ?? $course['card_color'] ?? '#FFFFFF';
    if (!in_array($card_color, $allowedColors)) {
        $card_color = '#FFFFFF';
    }

    $stmt = $pdo->prepare("UPDATE courses SET name=?, description=?, teacher_id=?, card_color=? WHERE id=?");
    $stmt->execute([$name, $desc, $teacher_id, $card_color, $id]);
    echo json_encode(['success'=>true]);
    exit;
}

http_response_code(405);