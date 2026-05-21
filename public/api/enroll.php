<?php
require_once 'config.php';
header('Content-Type: application/json');

$user = $_SESSION['user'] ?? null;
if (!$user) { http_response_code(401); exit; }

$method = $_SERVER['REQUEST_METHOD'];

// POST: Matricular estudiante (admin o profesor del curso)
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $course_id = $data['course_id'] ?? null;
    $student_id = $data['student_id'] ?? null;

    if (!$course_id || !$student_id) { http_response_code(400); echo json_encode(['error'=>'Faltan datos']); exit; }

    // Verificar autorización: admin siempre puede; profesor solo si es dueño del curso
    if ($user['role'] === 'admin') {
        // OK
    } elseif ($user['role'] === 'teacher') {
        $stmt = $pdo->prepare("SELECT teacher_id FROM courses WHERE id = ?");
        $stmt->execute([$course_id]);
        $course = $stmt->fetch();
        if (!$course || $course['teacher_id'] != $user['id']) {
            http_response_code(403); echo json_encode(['error'=>'No autorizado']); exit;
        }
    } else {
        http_response_code(403); exit;
    }

    // Insertar matrícula
    try {
        $stmt = $pdo->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)");
        $stmt->execute([$student_id, $course_id]);
        echo json_encode(['success'=>true]);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo json_encode(['error'=>'Ya está matriculado']);
        } else {
            http_response_code(500); echo json_encode(['error'=>'Error']);
        }
    }
    exit;
}

// GET: Listar estudiantes matriculados en un curso (admin/profesor)
if ($method === 'GET') {
    $course_id = $_GET['course_id'] ?? null;
    if (!$course_id) { http_response_code(400); exit; }

    if ($user['role'] === 'admin' || $user['role'] === 'teacher') {
        $stmt = $pdo->prepare("SELECT u.id, u.name, u.email FROM enrollments e JOIN users u ON e.user_id = u.id WHERE e.course_id = ?");
        $stmt->execute([$course_id]);
        echo json_encode($stmt->fetchAll());
    } else {
        http_response_code(403);
    }
    exit;
}