<?php
/**
 * Test Script - Forum Chat Feature
 * Verifica que la base de datos, APIs y componentes funcionan correctamente
 */

require_once 'api/config.php';

echo "=== TEST: Forum Chat Feature ===\n\n";

// 1. Verificar tablas de BD
echo "1. Verificando tablas de base de datos...\n";
$tables = ['forums', 'forum_topics', 'forum_messages'];
foreach ($tables as $table) {
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$table]);
    if ($stmt->fetch()) {
        echo "   ✓ Tabla $table existe\n";
    } else {
        echo "   ✗ Tabla $table NO existe\n";
    }
}

// 2. Verificar estructura de forum_topics
echo "\n2. Verificando estructura de forum_topics...\n";
$stmt = $pdo->prepare("DESCRIBE forum_topics");
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
$requiredColumns = ['id', 'forum_id', 'title', 'description', 'created_by', 'created_at', 'updated_at'];
foreach ($requiredColumns as $col) {
    if (in_array($col, $columns)) {
        echo "   ✓ Columna $col existe\n";
    } else {
        echo "   ✗ Columna $col NO existe\n";
    }
}

// 3. Verificar estructura de forum_messages
echo "\n3. Verificando estructura de forum_messages...\n";
$stmt = $pdo->prepare("DESCRIBE forum_messages");
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
$requiredColumns = ['id', 'forum_id', 'topic_id', 'user_id', 'content', 'created_at'];
foreach ($requiredColumns as $col) {
    if (in_array($col, $columns)) {
        echo "   ✓ Columna $col existe\n";
    } else {
        echo "   ✗ Columna $col NO existe\n";
    }
}

// 4. Verificar APIs
echo "\n4. Verificando APIs...\n";
$apis = ['topics.php', 'messages.php'];
foreach ($apis as $api) {
    $path = "api/$api";
    if (file_exists($path)) {
        echo "   ✓ API $api existe\n";
    } else {
        echo "   ✗ API $api NO existe\n";
    }
}

// 5. Verificar archivos frontendmodificados
echo "\n5. Verificando archivos frontend modificados...\n";
$files = ['estudiante.php', 'profesor.php', 'admin.php', 'js/app.js', 'css/estilos.css'];
foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        // Verificar si contiene referencias al nuevo sistema de temas
        if (strpos($content, 'topics') !== false && strpos($content, 'topic-item') !== false) {
            echo "   ✓ $file contiene lógica de temas\n";
        } else if (strpos($content, 'forum') !== false) {
            echo "   ~ $file contiene lógica de foros (necesita verificación)\n";
        } else {
            echo "   ? $file no contiene referencias de foros\n";
        }
    } else {
        echo "   ✗ $file NO existe\n";
    }
}

echo "\n=== TEST COMPLETADO ===\n";
?>
