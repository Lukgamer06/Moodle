<?php
require_once 'config.php';
header('Content-Type: application/json');

$user = $_SESSION['user'] ?? null;
if (!$user) { http_response_code(401); exit; }

$method = $_SERVER['REQUEST_METHOD'];

function canEnrollInCourse(PDO $pdo, array $user, $course_id): bool {
    if ($user['role'] === 'admin') return true;
    if ($user['role'] !== 'teacher') return false;
    $stmt = $pdo->prepare("SELECT 1 FROM courses WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$course_id, $user['id']]);
    return (bool) $stmt->fetch();
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $course_id = $data['course_id'] ?? null;
    $student_id = $data['student_id'] ?? null;
    $emails = $data['emails'] ?? [];

    if (!$course_id || (!$student_id && empty($emails))) {
        http_response_code(400);
        echo json_encode(['error'=>'Faltan datos']);
        exit;
    }
    if (!canEnrollInCourse($pdo, $user, $course_id)) {
        http_response_code(403);
        echo json_encode(['error'=>'No autorizado']);
        exit;
    }

    if (!empty($emails)) {
        if (is_string($emails)) $emails = preg_split('/[\s,;]+/', $emails);
        $emails = array_values(array_unique(array_filter(array_map('trim', $emails))));
        $added = [];
        $missing = [];
        $already = [];
        $find = $pdo->prepare("SELECT id, name, email FROM users WHERE email = ? AND role = 'student'");
        $insert = $pdo->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)");
        foreach ($emails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $missing[] = $email; continue; }
            $find->execute([$email]);
            $student = $find->fetch();
            if (!$student) { $missing[] = $email; continue; }
            try {
                $insert->execute([$student['id'], $course_id]);
                $added[] = $student;
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) $already[] = $email;
                else throw $e;
            }
        }
        echo json_encode(['success'=>true, 'added'=>$added, 'missing'=>$missing, 'already'=>$already]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)");
        $stmt->execute([$student_id, $course_id]);
        echo json_encode(['success'=>true]);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) echo json_encode(['error'=>'Ya está matriculado']);
        else { http_response_code(500); echo json_encode(['error'=>'Error']); }
    }
    exit;
}

if ($method === 'GET') {
    $course_id = $_GET['course_id'] ?? null;
    if (!$course_id) { http_response_code(400); exit; }
    if ($user['role'] === 'student') {
        $stmt = $pdo->prepare("SELECT 1 FROM enrollments WHERE course_id = ? AND user_id = ?");
        $stmt->execute([$course_id, $user['id']]);
        if (!$stmt->fetch()) { http_response_code(403); exit; }
    } elseif (!canEnrollInCourse($pdo, $user, $course_id)) {
        http_response_code(403);
        exit;
    }

    $stmt = $pdo->prepare("SELECT u.id, u.name, u.email FROM enrollments e JOIN users u ON e.user_id = u.id WHERE e.course_id = ? ORDER BY u.name");
    $stmt->execute([$course_id]);
    echo json_encode($stmt->fetchAll());
    exit;
}

http_response_code(405);
