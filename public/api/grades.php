<?php
require_once 'config.php';
header('Content-Type: application/json');
$user = $_SESSION['user'] ?? null;
if (!$user) { http_response_code(401); exit; }

$method = $_SERVER['REQUEST_METHOD'];

// GET: estudiante ve sus notas en un curso, profesor ve las de sus cursos
if ($method === 'GET') {
    if ($user['role'] === 'student') {
        $course_id = $_GET['course_id'] ?? null;
        if (!$course_id) { http_response_code(400); exit; }
        $stmt = $pdo->prepare("
            SELECT a.title AS actividad, u.title AS unidad, g.grade, g.graded_at
            FROM grades g
            JOIN submissions s ON g.submission_id = s.id
            JOIN activities a ON s.activity_id = a.id
            JOIN units u ON a.unit_id = u.id
            WHERE s.user_id = ? AND u.course_id = ?
            ORDER BY g.graded_at DESC
        ");
        $stmt->execute([$user['id'], $course_id]);
        echo json_encode($stmt->fetchAll());
        exit;
    }
    // Profesor/admin: listar estudiantes con notas por curso
    $course_id = $_GET['course_id'] ?? null;
    if (!$course_id) { http_response_code(400); exit; }
    $stmt = $pdo->prepare("
        SELECT u.id AS student_id, u.name AS student_name, 
               a.id AS activity_id, a.title AS actividad,
               s.id AS submission_id, g.grade
        FROM users u
        JOIN enrollments e ON u.id = e.user_id
        LEFT JOIN submissions s ON s.user_id = u.id
        LEFT JOIN activities a ON s.activity_id = a.id
        LEFT JOIN grades g ON g.submission_id = s.id
        WHERE e.course_id = ? AND u.role='student'
        ORDER BY u.name, a.id
    ");
    $stmt->execute([$course_id]);
    echo json_encode($stmt->fetchAll());
    exit;
}
// POST: profesor/admin califica una entrega
if ($method === 'POST' && in_array($user['role'], ['teacher','admin'])) {
    $data = json_decode(file_get_contents('php://input'), true);
    $submission_id = $data['submission_id'];
    $grade = $data['grade'];
    // upsert
    $stmt = $pdo->prepare("INSERT INTO grades (submission_id, grade) VALUES (?, ?) ON DUPLICATE KEY UPDATE grade = VALUES(grade)");
    $stmt->execute([$submission_id, $grade]);
    echo json_encode(['success'=>true]);
    exit;
}
http_response_code(405);