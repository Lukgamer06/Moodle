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

// GET: admin lista todos; otros obtienen su propio perfil
if ($method === 'GET') {
    $role_filter = $_GET['role'] ?? null;
    if ($user['role'] === 'admin' || ($user['role'] === 'teacher' && $role_filter === 'student')) {
        if ($role_filter) {
            $stmt = $pdo->prepare("SELECT id, name, email, role, created_at FROM users WHERE role = ? ORDER BY name");
            $stmt->execute([$role_filter]);
        } else {
            $stmt = $pdo->query("SELECT id, name, email, role, created_at FROM users ORDER BY name");
        }
        echo json_encode($stmt->fetchAll());
    } else {
        $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        echo json_encode($stmt->fetch());
    }
    exit;
}

// PUT
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Admin cambia el rol de otro usuario
    if ($user['role'] === 'admin' && isset($data['role'], $data['id'])) {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$data['role'], $data['id']]);
        echo json_encode(['success' => true]);
        exit;
    }

    // Actualizar perfil propio (nombre, email, contraseña)
    $id = $user['id'];
    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? null;
    $current_password = $data['current_password'] ?? '';

    if ($name === '' && $email === '' && !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'Nada que actualizar']);
        exit;
    }

    $updates = [];
    $params = [];

    // Actualizar nombre
    if ($name !== '') {
        $updates[] = "name = ?";
        $params[] = $name;
    }

    // Actualizar email
    if ($email !== '') {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Formato de email inválido']);
            exit;
        }
        $updates[] = "email = ?";
        $params[] = $email;
    }

    // Actualizar contraseña
    if ($password) {
        // Verificar contraseña actual obligatoriamente
        if ($current_password === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Debe proporcionar la contraseña actual']);
            exit;
        }
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $currentHash = $stmt->fetchColumn();
        if (!password_verify($current_password, $currentHash)) {
            http_response_code(400);
            echo json_encode(['error' => 'Contraseña actual incorrecta']);
            exit;
        }
        if (strlen($password) < 6) {
            http_response_code(400);
            echo json_encode(['error' => 'Contraseña mínima 6 caracteres']);
            exit;
        }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $updates[] = "password_hash = ?";
        $params[] = $hash;
    }

    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['error' => 'Sin cambios']);
        exit;
    }

    $params[] = $id;
    $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Actualizar la sesión
    if ($name !== '') $_SESSION['user']['name'] = $name;
    if ($email !== '') $_SESSION['user']['email'] = $email;

    echo json_encode(['success' => true, 'message' => 'Perfil actualizado']);
    exit;
}

// DELETE: solo admin puede eliminar usuarios
if ($method === 'DELETE' && $user['role'] === 'admin') {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID requerido']);
        exit;
    }
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);
