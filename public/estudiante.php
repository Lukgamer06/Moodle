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
<body class="course-page">

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
        <div class="avatar"><?php echo isset($user['name']) ? strtoupper(mb_substr($user['name'], 0, 1)) : '?'; ?></div>
        <span><?php echo isset($user['name']) ? explode(' ', $user['name'])[0] : 'Usuario'; ?></span>
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
  <button class="add-forum-btn" id="addForumBtn" onclick="showNewForumModal()"><i class="fa-solid fa-plus"></i> Nuevo Foro</button>
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

<!-- Entrega de actividad -->
<div class="modal-overlay" id="modal-submitActivity">
  <div class="modal">
    <div class="modal-header"><span class="modal-title" id="submitTitle">Entregar actividad</span><button class="modal-close" onclick="closeModal('submitActivity')"><i class="fa-solid fa-xmark"></i></button></div>
    <input type="hidden" id="submitActivityId">
    <input type="hidden" id="submitActivityType">
    <div class="form-group" id="submitContentGroup"><label class="form-label">Respuesta</label><textarea class="form-textarea" id="submitContent"></textarea></div>
    <div class="form-group" id="submitFileGroup"><label class="form-label">Archivo opcional</label><input type="file" class="form-input" id="submitFile"></div>
    <div id="submitQuestions"></div>
    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button class="btn btn-ghost" onclick="closeModal('submitActivity')">Cancelar</button>
      <button class="btn btn-primary" onclick="sendSubmission()">Enviar</button>
    </div>
  </div>
</div>

<!-- Nuevo Foro -->
<div class="modal-overlay" id="modal-newForum">
  <div class="modal">
    <div class="modal-header"><span class="modal-title">Nuevo Foro</span><button class="modal-close" onclick="closeModal('newForum')"><i class="fa-solid fa-xmark"></i></button></div>
    <div class="form-group"><label class="form-label">Título</label><input class="form-input" id="newForumTitle"></div>
    <div class="form-group"><label class="form-label">Descripción</label><textarea class="form-textarea" id="newForumDesc"></textarea></div>
    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button class="btn btn-ghost" onclick="closeModal('newForum')">Cancelar</button>
      <button class="btn btn-primary" onclick="createForum()">Crear</button>
    </div>
  </div>
</div>

<script src="js/app.js"></script>
<script>
const courseId = <?php echo json_encode($course_id); ?>;
const userId = <?php echo $user['id']; ?>;
const basePath = 'api/';
const activityCache = {};

function escapeAttr(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}
function activityLabel(type) {
  return ({ activity:'Actividad', quiz:'Quiz', evaluation:'Evaluación' })[type] || 'Actividad';
}

// ── CARGAR DATOS DEL CURSO ──
async function loadCourseIntro() {
  const res = await fetch(`api/courses.php?id=${courseId}`);
  if (!res.ok) return;
  const course = await res.json();
  document.getElementById('introText').innerHTML = course.description || 'Bienvenido al curso.';
  document.getElementById('courseIntro').style.background = course.card_color || '#FFFFFF';
}

// ── CARGAR UNIDADES ──
async function loadUnits() {
  const res = await fetch(`api/units.php?course_id=${courseId}`);
  const units = await res.json();
  const container = document.getElementById('unitsAccordion');
  container.innerHTML = units.map(unit => `
    <div class="unit-acc-item customizable-card" style="background:${unit.card_color || '#FFFFFF'}">
      <button class="unit-acc-header" 
              style="background:${unit.card_color || '#FFFFFF'}"
              onclick="toggleUnit(this)">
        <div class="unit-acc-left">
          <div class="unit-icon-sm ${unit.icon_class}"><i class="fa-solid fa-book"></i></div>
          <div>
            <div class="unit-acc-title">${unit.title}</div>
            <div class="unit-acc-desc html-content">${unit.description || ''}</div>
          </div>
        </div>
        <div class="unit-acc-right">
          <span class="unit-badge badge-pending">Pendiente</span>
          <i class="fa-solid fa-chevron-down unit-chevron"></i>
        </div>
      </button>
      <div class="unit-acc-body" style="background:${unit.card_color || '#FFFFFF'}">
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
      ${r.file_path ? `<a href="${escapeAttr(r.file_path)}" target="_blank"><i class="fa-solid ${actionIcon}" style="color:var(--gray-400);font-size:13px"></i></a>` : `<i class="fa-solid ${actionIcon}" style="color:var(--gray-400);font-size:13px"></i>`}
    </div>`;
  }).join('');
}

async function loadActivity(unitId) {
  const container = document.querySelector(`.activity-container[data-unit-id="${unitId}"]`);
  const res = await fetch(`api/activities.php?unit_id=${unitId}&include_questions=1`);
  const activities = await res.json();
  if (!activities.length) {
    container.innerHTML = '<p style="font-size:14px;color:var(--gray-500)">Sin actividad asignada.</p>';
    return;
  }
  const blocks = [];
  for (const act of activities) {
    activityCache[act.id] = act;
    const subRes = await fetch(`api/submissions.php?activity_id=${act.id}`);
    const submissions = await subRes.json();
    const submitted = submissions.some(s => s.user_id == userId);
    blocks.push(`
    <div class="activity-box">
      <div class="activity-due"><i class="fa-solid fa-list-check"></i> ${activityLabel(act.activity_type)}</div>
      <div class="activity-due"><i class="fa-solid fa-clock"></i> Fecha límite: ${act.due_date || 'Sin fecha'}</div>
      <p class="section-text">${escapeAttr(act.description)}</p>
      <br>
      ${submitted ? '<button class="btn btn-primary btn-sm" disabled><i class="fa-solid fa-check"></i> Actividad entregada</button>' :
      `<button class="btn btn-primary btn-sm" onclick="openSubmitActivity(${act.id})"><i class="fa-solid fa-upload"></i> Entregar actividad</button>`}
    </div>
    `);
  }
  container.innerHTML = blocks.join('');
}

function openSubmitActivity(activityId) {
  const activity = activityCache[activityId];
  document.getElementById('submitActivityId').value = activityId;
  document.getElementById('submitActivityType').value = activity.activity_type || 'activity';
  document.getElementById('submitTitle').textContent = `Entregar ${activityLabel(activity.activity_type)}`;
  document.getElementById('submitContent').value = '';
  document.getElementById('submitFile').value = '';
  const isQuiz = ['quiz','evaluation'].includes(activity.activity_type);
  document.getElementById('submitContentGroup').style.display = isQuiz ? 'none' : 'block';
  document.getElementById('submitFileGroup').style.display = isQuiz ? 'none' : 'block';
  document.getElementById('submitQuestions').innerHTML = isQuiz ? (activity.questions || []).map(q => `
    <div class="form-group" data-question-id="${q.id}">
      <label class="form-label">${escapeAttr(q.question)}</label>
      <select class="form-select quiz-answer">
        <option value="">Selecciona una respuesta</option>
        <option value="a">A. ${escapeAttr(q.option_a)}</option>
        <option value="b">B. ${escapeAttr(q.option_b)}</option>
        <option value="c">C. ${escapeAttr(q.option_c)}</option>
        <option value="d">D. ${escapeAttr(q.option_d)}</option>
      </select>
    </div>
  `).join('') : '';
  openModal('submitActivity');
}

async function sendSubmission() {
  const activityId = document.getElementById('submitActivityId').value;
  const activityType = document.getElementById('submitActivityType').value;
  const formData = new FormData();
  formData.append('activity_id', activityId);
  if (['quiz','evaluation'].includes(activityType)) {
    const answers = {};
    document.querySelectorAll('#submitQuestions .form-group').forEach(group => {
      answers[group.dataset.questionId] = group.querySelector('.quiz-answer').value;
    });
    if (Object.values(answers).some(v => !v)) return alert('Responde todas las preguntas');
    formData.append('answers', JSON.stringify(answers));
  } else {
    formData.append('content', document.getElementById('submitContent').value.trim());
    const file = document.getElementById('submitFile').files[0];
    if (file) formData.append('file', file);
  }
  const res = await fetch('api/submissions.php', { method: 'POST', body: formData });
  const data = await res.json();
  if (!res.ok) return alert(data.error || 'No se pudo enviar');
  closeModal('submitActivity');
  alert(data.grade === null || data.grade === undefined ? 'Actividad entregada.' : `Entrega enviada. Nota: ${data.grade}`);
  loadUnits();
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
function showNewForumModal() { openModal('newForum'); }
async function createForum() {
  const title = document.getElementById('newForumTitle').value.trim();
  const description = document.getElementById('newForumDesc').value.trim();
  if (!title) return alert('Título requerido');
  const res = await fetch('api/forums.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ course_id: courseId, title, description })
  });
  if (!res.ok) return alert('No se pudo crear el foro');
  document.getElementById('newForumTitle').value = '';
  document.getElementById('newForumDesc').value = '';
  closeModal('newForum');
  loadForums();
}

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
