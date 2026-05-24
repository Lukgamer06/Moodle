<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$role = $data['role'] ?? 'student';

if (empty($name) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Todos los campos son obligatorios']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Correo inválido']);
    exit;
}

// Validar que el rol sea permitido
$allowedRoles = ['student', 'teacher', 'admin'];

if (!in_array($role, $allowedRoles)) {
    $role = 'student';
}

// Verificar si el correo ya existe
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['error' => 'El correo ya está registrado']);
    exit;
}

try {
    $pdo->beginTransaction();

    /*
      Generar NRC automático.

      La lógica es:
      - Buscar el NRC más alto registrado.
      - Quitarle la letra U.
      - Convertir el número a entero.
      - Sumar 1.
      - Volver a formar el NRC con la letra U y 8 dígitos.

      Ejemplo:
      U00010000 -> U00010001
    */

    $stmt = $pdo->query("
        SELECT MAX(CAST(SUBSTRING(nrc, 2) AS UNSIGNED)) AS last_nrc_number
        FROM users
        WHERE nrc IS NOT NULL
          AND nrc LIKE 'U%'
    ");

    $lastNumber = $stmt->fetchColumn();

    if ($lastNumber) {
        $nextNumber = intval($lastNumber) + 1;
    } else {
        $nextNumber = 10000;
    }

    $nrc = 'U' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);

    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, nrc, password_hash, role) 
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([$name, $email, $nrc, $hash, $role]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Registro exitoso',
        'nrc' => $nrc
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'error' => 'No se pudo registrar el usuario',
        'detail' => $e->getMessage()
    ]);
}