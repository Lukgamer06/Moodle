<?php
require_once 'config.php';
header('Content-Type: application/json');

$user = $_SESSION['user'] ?? null;
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

$allowedColors = ['#FFF9C7', '#C7D1FF', '#FFF8E8', '#C2FFCB', '#FEE3FF', '#FFFFFF'];

function normalizeColor($color, $allowedColors) {
    return in_array($color, $allowedColors) ? $color : '#FFFFFF';
}

function canEditCourse(PDO $pdo, array $user, $course_id): bool {
    if ($user['role'] === 'admin') {
        return true;
    }

    if ($user['role'] !== 'teacher') {
        return false;
    }

    $stmt = $pdo->prepare("SELECT 1 FROM courses WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$course_id, $user['id']]);

    return (bool) $stmt->fetch();
}

function canEditUnit(PDO $pdo, array $user, $unit_id): bool {
    if ($user['role'] === 'admin') {
        return true;
    }

    if ($user['role'] !== 'teacher') {
        return false;
    }

    $stmt = $pdo->prepare("
        SELECT 1
        FROM units u
        JOIN courses c ON u.course_id = c.id
        WHERE u.id = ? AND c.teacher_id = ?
    ");
    $stmt->execute([$unit_id, $user['id']]);

    return (bool) $stmt->fetch();
}

// GET: listar unidades de un curso
if ($method === 'GET') {
    $course_id = $_GET['course_id'] ?? null;

    if (!$course_id) {
        http_response_code(400);
        echo json_encode(['error' => 'course_id requerido']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM units WHERE course_id = ? ORDER BY order_index, id");
    $stmt->execute([$course_id]);

    echo json_encode($stmt->fetchAll());
    exit;
}

// POST: crear unidad
if ($method === 'POST' && in_array($user['role'], ['teacher', 'admin'])) {
    $data = json_decode(file_get_contents('php://input'), true);

    $course_id = $data['course_id'] ?? null;
    $title = trim($data['title'] ?? '');
    $description = $data['description'] ?? '';
    $icon_class = $data['icon_class'] ?? 'gen';
    $order_index = $data['order_index'] ?? 0;
    $card_color = normalizeColor($data['card_color'] ?? '#FFFFFF', $allowedColors);

    if (!$course_id || $title === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Faltan datos']);
        exit;
    }

    if (!canEditCourse($pdo, $user, $course_id)) {
        http_response_code(403);
        echo json_encode(['error' => 'No autorizado para editar este curso']);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO units 
        (course_id, title, description, icon_class, order_index, card_color) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $course_id,
        $title,
        $description,
        $icon_class,
        $order_index,
        $card_color
    ]);

    echo json_encode([
        'success' => true,
        'id' => $pdo->lastInsertId()
    ]);
    exit;
}

// PUT: editar unidad
if ($method === 'PUT' && in_array($user['role'], ['teacher', 'admin'])) {
    $data = json_decode(file_get_contents('php://input'), true);

    $id = $data['id'] ?? null;
    $title = trim($data['title'] ?? '');
    $description = $data['description'] ?? '';
    $icon_class = $data['icon_class'] ?? 'gen';
    $order_index = $data['order_index'] ?? 0;
    $card_color = normalizeColor($data['card_color'] ?? '#FFFFFF', $allowedColors);

    if (!$id || $title === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Faltan datos']);
        exit;
    }

    if (!canEditUnit($pdo, $user, $id)) {
        http_response_code(403);
        echo json_encode(['error' => 'No autorizado para editar esta unidad']);
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE units 
        SET title = ?, description = ?, icon_class = ?, order_index = ?, card_color = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $title,
        $description,
        $icon_class,
        $order_index,
        $card_color,
        $id
    ]);

    echo json_encode(['success' => true]);
    exit;
}

// DELETE: eliminar unidad
if ($method === 'DELETE' && in_array($user['role'], ['teacher', 'admin'])) {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID requerido']);
        exit;
    }

    if (!canEditUnit($pdo, $user, $id)) {
        http_response_code(403);
        echo json_encode(['error' => 'No autorizado para eliminar esta unidad']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM units WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);