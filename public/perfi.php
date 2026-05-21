<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: login.html'); exit; }
$user = $_SESSION['user'];
require_once 'api/config.php';

// Obtener estadísticas reales
$stmt = $pdo->prepare("SELECT COUNT(*) FROM forum_messages WHERE user_id = ?");
$stmt->execute([$user['id']]);
$mensajes = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT AVG(g.grade) FROM grades g JOIN submissions s ON g.submission_id = s.id WHERE s.user_id = ?");
$stmt->execute([$user['id']]);
$promedio = $stmt->fetchColumn();
$promedio = $promedio ? number_format($promedio, 1) : '—';

$stmt = $pdo->prepare("SELECT COUNT(*) FROM submissions WHERE user_id = ?");
$stmt->execute([$user['id']]);
$entregas = $stmt->fetchColumn();

// Calcular progreso (ejemplo simple: % de unidades con actividad entregada)
$stmt = $pdo->prepare("SELECT COUNT(DISTINCT a.unit_id) FROM activities a JOIN submissions s ON s.activity_id = a.id WHERE s.user_id = ?");
$stmt->execute([$user['id']]);
$unidadesCompletadas = $stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) FROM units");
$totalUnidades = $stmt->fetchColumn();
$progreso = $totalUnidades > 0 ? round(($unidadesCompletadas / $totalUnidades) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mi Perfil — Mini Moodle</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/estilos.css">
</head>
<body>

<header class="topbar">
  <div class="topbar-left">
    <div class="logo-mark">M</div>
    <button class="back-btn" onclick="window.location.href='dashboard.php'">
      <i class="fa-solid fa-arrow-left"></i> Volver al dashboard
    </button>
  </div>
  <div class="topbar-right">
    <div class="avatar" id="topAvatar"><?php echo strtoupper($user['name'][0]); ?></div>
    <span class="top-name" id="topName"><?php echo explode(' ', $user['name'])[0]; ?></span>
  </div>
</header>

<div class="page-wrap">
  <p class="page-heading">Mi Perfil</p>

  <div class="card">
    <div class="profile-hero">
      <div class="profile-avatar-lg" id="profileAvatar"><?php echo strtoupper(substr($user['name'], 0, 2)); ?></div>
      <div>
        <div class="profile-name" id="displayName"><?php echo htmlspecialchars($user['name']); ?></div>
        <div class="profile-sub">
          <span class="role-pill" id="displayRole"><?php echo ucfirst($user['role']); ?></span>
          <span>Mini Moodle</span>
        </div>
      </div>
    </div>

    <div class="stats-row">
      <div class="stat-card"><div class="stat-num"><?php echo $progreso; ?>%</div><div class="stat-lbl">Progreso</div></div>
      <div class="stat-card"><div class="stat-num"><?php echo $promedio; ?></div><div class="stat-lbl">Promedio</div></div>
      <div class="stat-card"><div class="stat-num"><?php echo $mensajes; ?></div><div class="stat-lbl">Mensajes</div></div>
    </div>

    <div class="card-section-title">Información personal</div>
    <div class="fields-grid" id="viewMode">
      <div class="field-group">
        <span class="field-label">Nombre completo</span>
        <div class="field-value" id="view-name"><?php echo htmlspecialchars($user['name']); ?></div>
      </div>
      <div class="field-group">
        <span class="field-label">Correo electrónico</span>
        <div class="field-value" id="view-email"><?php echo htmlspecialchars($user['email']); ?></div>
      </div>
      <div class="field-group">
        <span class="field-label">Rol</span>
        <div class="field-value" id="view-role"><?php echo ucfirst($user['role']); ?></div>
      </div>
      <div class="field-group">
        <span class="field-label">ID de usuario</span>
        <div class="field-value">#<?php echo $user['id']; ?></div>
      </div>
    </div>

    <div class="fields-grid" id="editMode" style="display:none">
      <div class="field-group">
        <label class="field-label" for="inp-name">Nombre completo</label>
        <input class="field-input" id="inp-name" value="<?php echo htmlspecialchars($user['name']); ?>">
      </div>
      <div class="field-group">
        <label class="field-label" for="inp-email">Correo electrónico</label>
        <input class="field-input" id="inp-email" value="<?php echo htmlspecialchars($user['email']); ?>">
      </div>
    </div>

    <div class="actions-row">
      <button class="btn btn-primary" id="editBtn" onclick="startEdit()"><i class="fa-solid fa-pen"></i> Editar perfil</button>
      <button class="btn btn-primary" id="saveBtn" onclick="saveProfile()" style="display:none"><i class="fa-solid fa-floppy-disk"></i> Guardar cambios</button>
      <button class="btn btn-ghost"   id="cancelBtn" onclick="cancelEdit()" style="display:none">Cancelar</button>
    </div>
  </div>

  <div class="card">
    <div class="card-section-title">Cambiar contraseña</div>
    <div class="pwd-row">
      <div class="field-group">
        <label class="field-label" for="pwd-current">Contraseña actual</label>
        <input class="field-input" id="pwd-current" type="password" placeholder="••••••••">
      </div>
      <div class="field-group">
        <label class="field-label" for="pwd-new">Nueva contraseña</label>
        <input class="field-input" id="pwd-new" type="password" placeholder="••••••••">
      </div>
      <div class="field-group">
        <label class="field-label" for="pwd-confirm">Confirmar contraseña</label>
        <input class="field-input" id="pwd-confirm" type="password" placeholder="••••••••">
      </div>
    </div>
    <div class="actions-row">
      <button class="btn btn-ghost" onclick="changePwd()"><i class="fa-solid fa-key"></i> Actualizar contraseña</button>
    </div>
  </div>

  <div class="card">
    <div class="card-section-title">Sesión</div>
    <button class="btn btn-danger" onclick="logout()"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</button>
  </div>
</div>

<div class="toast" id="toast"><i class="fa-solid fa-circle-check"></i> <span id="toastMsg"></span></div>

<script src="js/app.js"></script>
</body>
</html>