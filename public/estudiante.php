<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') { header('Location: login.html'); exit; }
$user = $_SESSION['user'];
$course_id = $_GET['course_id'] ?? null;
if (!$course_id) { header('Location: dashboard.php'); exit; }
// Obtener nombre del curso para el topbar
require_once 'api/config.php';
$stmt = $pdo->prepare("SELECT name FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();
$course_name = $course ? $course['name'] : 'Curso';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Curso - Estudiante</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/estilos.css">
</head>
<body>

<!-- TOPBAR -->
<header class="topbar">
  <div class="topbar-left">
    <div class="logo-mark">M</div>
    <span class="course-title"><?php echo htmlspecialchars($course_name); ?></span>
  </div>
  <div class="topbar-right">
    <span class="role-badge-static">Estudiante</span>
    <div class="user-dropdown">
      <div class="username">
        <div class="avatar"><?php echo strtoupper($user['name'][0]); ?></div>
        <span><?php echo explode(' ', $user['name'])[0]; ?></span>
        <i class="fa-solid fa-caret-down" style="font-size:12px;opacity:.7"></i>
      </div>
      <div class="dropdown-menu" id="dropdownMenu">
          <button onclick="window.location.href='dashboard.php'"><i class="fa-solid fa-home"></i> Dashboard</button>
          <button onclick="window.location.href='perfil.php'"><i class="fa-solid fa-user"></i> Mi Perfil</button>
          <button onclick="logout()" style="color:var(--red)"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</button>
      </div>
    </div>
    <i id="openForumBtn" class="fa-solid fa-comments forum-icon" title="Foros"></i>
  </div>
</header>

<!-- NAV TABS -->
<nav class="nav-area">
  <button class="nav-btn active" onclick="showScreen('course')"><i class="fa-solid fa-book"></i> Curso</button>
  <button class="nav-btn" onclick="showScreen('participants')"><i class="fa-solid fa-users"></i> Participantes</button>
  <button class="nav-btn" onclick="showScreen('grades')"><i class="fa-solid fa-graduation-cap"></i> Calificaciones</button>
</nav>

<!-- FORUM DRAWER -->
<aside id="forumDrawer" class="drawer">
  <div class="drawer-header">
    <h2 class="drawer-title">Foros del Curso</h2>
    <i id="closeForumBtn" class="fa-solid fa-xmark close-icon"></i>
  </div>
  <div class="forum-list" id="forumList"></div>
  <button class="add-forum-btn" id="addForumBtn" style="display:none"><i class="fa-solid fa-plus"></i> Nuevo Foro</button>
</aside>
<div id="overlay" class="overlay"></div>

<!-- MAIN -->
<main class="page-wrap">
  <div id="screen-course" class="screen active">
    <div class="card" id="courseIntro">
      <div class="card-header"><h2 class="section-title">Presentación del Curso</h2></div>
      <p class="section-text" id="introText">Cargando...</p>
    </div>
    <div class="card">
      <div class="card-header"><h2 class="section-title">Unidades del Curso</h2></div>
      <div id="unitsAccordion"></div>
    </div>
  </div>
  <div id="screen-participants" class="screen">
    <div class="card">
      <div class="card-header"><h2 class="section-title">Participantes</h2></div>
      <div class="participants-list" id="participantsList"></div>
    </div>
  </div>
  <div id="screen-grades" class="screen">
    <div class="card">
      <div class="card-header"><h2 class="section-title">Mis Calificaciones</h2></div>
      <div style="overflow-x:auto"><table class="grades-table" id="gradesTable"></table></div>
    </div>
  </div>
</main>

<script src="js/app.js"></script>
<script>
const courseId = <?php echo json_encode($course_id); ?>;
const userId = <?php echo $user['id']; ?>;
const basePath = 'api/';

// ── CARGAR DATOS DEL CURSO ──
async function loadCourseIntro() {
  const res = await fetch(`api/courses.php?id=${courseId}`);
  if (!res.ok) return;
  const course = await res.json();
  document.getElementById('introText').innerHTML = course.description || 'Bienvenido al curso.';
}

// ── CARGAR UNIDADES ──
async function loadUnits() {
  const res = await fetch(`api/units.php?course_id=${courseId}`);
  const units = await res.json();
  const container = document.getElementById('unitsAccordion');
  container.innerHTML = units.map(unit => `
    <div class="unit-acc-item">
      <button class="unit-acc-header" onclick="toggleUnit(this)">
        <div class="unit-acc-left">
          <div class="unit-icon-sm ${unit.icon_class}"><i class="fa-solid fa-book"></i></div>
          <div>
            <div class="unit-acc-title">${unit.title}</div>
            <div class="unit-acc-desc">${unit.description || ''}</div>
          </div>
        </div>
        <div class="unit-acc-right">
          <span class="unit-badge badge-pending">Pendiente</span>
          <i class="fa-solid fa-chevron-down unit-chevron"></i>
        </div>
      </button>
      <div class="unit-acc-body">
        <div class="unit-section">
          <div class="unit-section-title"><i class="fa-solid fa-paperclip"></i> Recursos</div>
          <div class="resources-container" data-unit-id="${unit.id}">Cargando...</div>
        </div>
        <div class="unit-section">
          <div class="unit-section-title"><i class="fa-solid fa-pen-to-square"></i> Actividad</div>
          <div class="activity-container" data-unit-id="${unit.id}">Cargando...</div>
        </div>
      </div>
    </div>
  `).join('');
  // Cargar recursos y actividades para cada unidad
  units.forEach(unit => {
    loadResources(unit.id);
    loadActivity(unit.id);
  });
}

async function loadResources(unitId) {
  const container = document.querySelector(`.resources-container[data-unit-id="${unitId}"]`);
  const res = await fetch(`api/resources.php?unit_id=${unitId}`);
  const resources = await res.json();
  if (!resources.length) {
    container.innerHTML = '<p style="font-size:13px;color:var(--gray-400)">Sin recursos cargados.</p>';
    return;
  }
  container.innerHTML = resources.map(r => {
    const iconClass = { pdf: 'fa-file-pdf res-pdf', video: 'fa-circle-play res-video', doc: 'fa-file-lines res-doc' }[r.type] || 'fa-file';
    const actionIcon = r.type === 'video' ? 'fa-play' : 'fa-download';
    return `<div class="resource-item">
      <div class="res-icon ${iconClass.split(' ')[1]}"><i class="fa-solid ${iconClass.split(' ')[0]}"></i></div>
      <div class="res-info"><div class="res-name">${r.name}</div><div class="res-meta">${r.meta || ''}</div></div>
      <i class="fa-solid ${actionIcon}" style="color:var(--gray-400);font-size:13px"></i>
    </div>`;
  }).join('');
}

async function loadActivity(unitId) {
  const container = document.querySelector(`.activity-container[data-unit-id="${unitId}"]`);
  const res = await fetch(`api/activities.php?unit_id=${unitId}`);
  const activities = await res.json();
  if (!activities.length) {
    container.innerHTML = '<p style="font-size:14px;color:var(--gray-500)">Sin actividad asignada.</p>';
    return;
  }
  const act = activities[0]; // Mostramos la primera actividad
  // Verificar si ya entregó
  const subRes = await fetch(`api/submissions.php?activity_id=${act.id}`);
  const submissions = await subRes.json();
  const submitted = submissions.some(s => s.user_id == userId);
  container.innerHTML = `
    <div class="activity-box">
      <div class="activity-due"><i class="fa-solid fa-clock"></i> Fecha límite: ${act.due_date || 'Sin fecha'}</div>
      <p class="section-text">${act.description}</p>
      <br>
      ${submitted ? '<button class="btn btn-primary btn-sm" disabled><i class="fa-solid fa-check"></i> Actividad entregada</button>' :
      `<button class="btn btn-primary btn-sm" onclick="submitActivity(${act.id})"><i class="fa-solid fa-upload"></i> Entregar actividad</button>`}
    </div>
  `;
}

async function submitActivity(activityId) {
  const content = prompt('Escribe tu respuesta o contenido de la entrega:');
  if (content === null) return;
  const formData = new FormData();
  formData.append('activity_id', activityId);
  formData.append('content', content);
  await fetch('api/submissions.php', { method: 'POST', body: formData });
  alert('Actividad entregada.');
  loadUnits(); // Recargar para actualizar estado
}

// ── PARTICIPANTES ──
async function loadParticipants() {
  const res = await fetch(`api/enroll.php?course_id=${courseId}`);
  const participants = await res.json();
  document.getElementById('participantsList').innerHTML = participants.map(p => `
    <div class="participant-row">
      <span class="p-name">${p.name}</span>
      <span class="p-role-badge role-estudiante">Estudiante</span>
    </div>
  `).join('');
}

// ── CALIFICACIONES ──
async function loadGrades() {
  const res = await fetch(`api/grades.php?course_id=${courseId}`);
  const grades = await res.json();
  const table = document.getElementById('gradesTable');
  if (!grades.length) {
    table.innerHTML = '<tr><td colspan="3" style="text-align:center;color:var(--gray-400)">Sin calificaciones aún.</td></tr>';
    return;
  }
  table.innerHTML = `
    <thead><tr><th>Actividad</th><th>Unidad</th><th>Nota</th></tr></thead>
    <tbody>${grades.map(g => `
      <tr>
        <td>${g.actividad}</td>
        <td>${g.unidad}</td>
        <td>${g.grade ? `<span class="grade-chip grade-${g.grade >= 9 ? 'a' : g.grade >= 7 ? 'b' : 'c'}">${g.grade}</span>` : '<span class="grade-chip grade-pending">Pendiente</span>'}</td>
      </tr>`).join('')}
    </tbody>
  `;
}

// ── FOROS ──
async function loadForums() {
  const res = await fetch(`api/forums.php?course_id=${courseId}`);
  const forums = await res.json();
  const container = document.getElementById('forumList');
  container.innerHTML = forums.map(f => `
    <div class="forum-item">
      <button class="forum-header" onclick="toggleForum(this)">
        <span>${f.title}</span>
        <i class="fa-solid fa-chevron-down chevron"></i>
      </button>
      <div class="forum-body" data-forum-id="${f.id}">
        <div class="forum-meta-title">${f.title}</div>
        <div class="forum-meta-desc">${f.description || ''}</div>
        <div class="forum-sep"></div>
        <div class="messages-container" id="messages-${f.id}">Cargando...</div>
        <div class="forum-input">
          <textarea class="forum-textarea" placeholder="Escribe un mensaje..."></textarea>
          <button class="send-btn" onclick="sendMsg(this)"><i class="fa-solid fa-paper-plane"></i></button>
        </div>
      </div>
    </div>
  `).join('');
  forums.forEach(f => loadForumMessages(f.id));
}

async function loadForumMessages(forumId, targetBody = null) {
  const container = document.getElementById('messages-' + forumId);
  if (!container) return;
  const res = await fetch(`api/messages.php?forum_id=${forumId}`);
  const messages = await res.json();
  container.innerHTML = messages.map(m => `
    <div class="message"><p class="msg-user">${m.user_name}</p><p class="msg-text">${m.content}</p></div>
  `).join('');
}

// Iniciar todo
loadCourseIntro();
loadUnits();
loadForums();
</script>
</body>
</html>