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

function canEditCourse($pdo, $user, $course_id) {
    if ($user['role'] === 'admin') {
        return true;
    }

    if ($user['role'] === 'teacher') {
        $stmt = $pdo->prepare("SELECT id FROM courses WHERE id = ? AND teacher_id = ?");
        $stmt->execute([$course_id, $user['id']]);
        return (bool) $stmt->fetch();
    }

    return false;
}

/* ============================================================
   POST: subir o reemplazar imagen de portada
   ============================================================ */
if ($method === 'POST') {
    $course_id = $_POST['course_id'] ?? null;

    if (!$course_id) {
        http_response_code(400);
        echo json_encode(['error' => 'course_id requerido']);
        exit;
    }

    if (!canEditCourse($pdo, $user, $course_id)) {
        http_response_code(403);
        echo json_encode(['error' => 'No autorizado para modificar este curso']);
        exit;
    }

    if (!isset($_FILES['cover']) || $_FILES['cover']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'No se recibió la imagen']);
        exit;
    }

    $file = $_FILES['cover'];

    // Solo permitir PNG real
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if ($mime !== 'image/png') {
        http_response_code(400);
        echo json_encode(['error' => 'Solo se permiten imágenes PNG']);
        exit;
    }

    // Carpeta donde se guardarán las portadas
    $uploadDir = __DIR__ . '/../../uploads/course_covers';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    // Buscar portada anterior
    $stmt = $pdo->prepare("SELECT cover_image FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch();

    if (!$course) {
        http_response_code(404);
        echo json_encode(['error' => 'Curso no encontrado']);
        exit;
    }

    $oldCover = $course['cover_image'] ?? null;

    // Nombre seguro de la nueva imagen
    $fileName = 'course_' . $course_id . '_' . time() . '.png';
    $destination = $uploadDir . '/' . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo guardar la imagen']);
        exit;
    }

    // Ruta pública que se guarda en MySQL
    $publicPath = 'uploads/course_covers/' . $fileName;

    $stmt = $pdo->prepare("UPDATE courses SET cover_image = ? WHERE id = ?");
    $stmt->execute([$publicPath, $course_id]);

    // Eliminar la portada anterior del servidor si existía
    if ($oldCover) {
        $oldPath = __DIR__ . '/../../' . $oldCover;

        if (file_exists($oldPath) && is_file($oldPath)) {
            unlink($oldPath);
        }
    }

    echo json_encode([
        'success' => true,
        'cover_image' => $publicPath
    ]);
    exit;
}

/* ============================================================
   DELETE: eliminar imagen de portada
   ============================================================ */
if ($method === 'DELETE') {
    $course_id = $_GET['course_id'] ?? null;

    if (!$course_id) {
        http_response_code(400);
        echo json_encode(['error' => 'course_id requerido']);
        exit;
    }

    if (!canEditCourse($pdo, $user, $course_id)) {
        http_response_code(403);
        echo json_encode(['error' => 'No autorizado para modificar este curso']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT cover_image FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch();

    if (!$course) {
        http_response_code(404);
        echo json_encode(['error' => 'Curso no encontrado']);
        exit;
    }

    $oldCover = $course['cover_image'] ?? null;

    if ($oldCover) {
        $oldPath = __DIR__ . '/../../' . $oldCover;

        if (file_exists($oldPath) && is_file($oldPath)) {
            unlink($oldPath);
        }
    }

    $stmt = $pdo->prepare("UPDATE courses SET cover_image = NULL WHERE id = ?");
    $stmt->execute([$course_id]);

    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);