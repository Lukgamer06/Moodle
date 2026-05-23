<?php
$host = getenv('DB_HOST') ?: 'mysql';
$db   = getenv('DB_NAME') ?: 'mini_moodle';
$dbUser = getenv('DB_USER') ?: 'moodleuser';
$pass = getenv('DB_PASS') ?: 'moodlepass';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $dbUser, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

if (session_status() === PHP_SESSION_NONE) session_start();

try {
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'activities' AND COLUMN_NAME = 'activity_type'
    ");
    $stmt->execute();
    if ((int)$stmt->fetchColumn() === 0) {
        $pdo->exec("ALTER TABLE activities ADD COLUMN activity_type ENUM('activity','quiz','evaluation') NOT NULL DEFAULT 'activity' AFTER unit_id");
    }
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS quiz_questions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            activity_id INT NOT NULL,
            question TEXT NOT NULL,
            option_a VARCHAR(500) NOT NULL,
            option_b VARCHAR(500) NOT NULL,
            option_c VARCHAR(500) NOT NULL,
            option_d VARCHAR(500) NOT NULL,
            correct_option ENUM('a','b','c','d') NOT NULL,
            order_index INT NOT NULL DEFAULT 0,
            FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");
    try {
        $pdo->exec("ALTER TABLE grades ADD UNIQUE KEY unique_submission_grade (submission_id)");
    } catch (PDOException $ignored) {
    }
} catch (PDOException $ignored) {
}
