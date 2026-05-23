<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') { header('Location: login.html'); exit; }
$user = $_SESSION['user'];
$course_id = $_GET['course_id'] ?? null;
if (!$course_id) { header('Location: dashboard.php'); exit; }
require_once 'api/config.php';
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND teacher_id = ?");
$stmt->execute([$course_id, $user['id']]);
$course = $stmt->fetch();
if (!$course) { header('Location: dashboard.php'); exit; }
$course_name = $course['name'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Curso - Profesor</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/estilos.css">
</head>
<body>

<header class="topbar">
  <div class="topbar-left">
    <div class="logo-mark">M</div>
    <span class="course-title"><?php echo htmlspecialchars($course_name); ?></span>
  </div>
  <div class="topbar-right">
    <span class="role-badge-static">Profesor</span>
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

<main class="page-wrap">
  <div id="screen-course" class="screen active">
    <div class="card">
      <div class="card-header">
        <h2 class="section-title">Presentación del Curso</h2>
        <div class="edit-toolbar visible">
          <button class="btn btn-ghost btn-sm" onclick="editIntro()"><i class="fa-solid fa-pen"></i> Editar</button>
        </div>
      </div>
      <p class="section-text" id="introText"><?php echo htmlspecialchars($course['description'] ?? ''); ?></p>
    </div>
    <div class="card">
      <div class="card-header">
        <h2 class="section-title">Unidades del Curso</h2>
        <div class="edit-toolbar visible">
          <button class="btn btn-accent btn-sm" onclick="showAddUnitModal()"><i class="fa-solid fa-plus"></i> Agregar Unidad</button>
        </div>
      </div>
      <div id="unitsAccordion"></div>
    </div>
  </div>

  <div id="screen-participants" class="screen">
    <div class="card">
      <div class="card-header">
        <h2 class="section-title">Participantes</h2>
        <div class="edit-toolbar visible">
          <button class="btn btn-accent btn-sm" onclick="showEnrollModal()"><i class="fa-solid fa-user-plus"></i> Matricular Estudiante</button>
        </div>
      </div>
      <div class="participants-list" id="participantsList"></div>
    </div>
  </div>

  <div id="screen-grades" class="screen">
    <div class="card">
      <div class="card-header">
        <h2 class="section-title">Calificaciones</h2>
        <div class="edit-toolbar visible">
          <button class="btn btn-primary btn-sm" onclick="saveGrades()"><i class="fa-solid fa-floppy-disk"></i> Guardar Cambios</button>
        </div>
      </div>
      <div style="overflow-x:auto">
        <table class="grades-table" id="gradesTable"></table>
      </div>
    </div>
  </div>
</main>

<!-- MODALES -->
<!-- Editar Introducción -->
<div class="modal-overlay" id="modal-editIntro">
  <div class="modal">
    <div class="modal-header"><span class="modal-title">Editar Presentación</span><button class="modal-close" onclick="closeModal('editIntro')"><i class="fa-solid fa-xmark"></i></button></div>
    <div class="form-group"><label class="form-label">Descripción</label><textarea class="form-textarea" id="editIntroDesc"></textarea></div>
    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button class="btn btn-ghost" onclick="closeModal('editIntro')">Cancelar</button>
      <button class="btn btn-primary" onclick="saveIntro()">Guardar</button>
    </div>
  </div>
</div>

<!-- Agregar/Editar Unidad -->
<div class="modal-overlay" id="modal-unit">
  <div class="modal">
    <div class="modal-header"><span class="modal-title" id="modalUnitTitle">Nueva Unidad</span><button class="modal-close" onclick="closeModal('unit')"><i class="fa-solid fa-xmark"></i></button></div>
    <input type="hidden" id="unitEditId">
    <div class="form-group"><label class="form-label">Título</label><input class="form-input" id="unitTitle"></div>
    <div class="form-group"><label class="form-label">Descripción breve</label><input class="form-input" id="unitDesc"></div>
    <div class="form-group"><label class="form-label">Ícono</label>
      <select class="form-select" id="unitIcon">
        <option value="hw">🔧 Hardware</option><option value="net">🌐 Redes</option><option value="srv">🖥️ Servidores</option><option value="virt">☁️ Virtualización</option><option value="gen">📘 General</option>
      </select>
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button class="btn btn-ghost" onclick="closeModal('unit')">Cancelar</button>
      <button class="btn btn-primary" onclick="saveUnit()">Guardar</button>
    </div>
  </div>
</div>

<!-- Agregar Recurso -->
<div class="modal-overlay" id="modal-resource">
  <div class="modal">
    <div class="modal-header"><span class="modal-title">Nuevo Recurso</span><button class="modal-close" onclick="closeModal('resource')"><i class="fa-solid fa-xmark"></i></button></div>
    <input type="hidden" id="resourceUnitId">
    <div class="form-group"><label class="form-label">Nombre</label><input class="form-input" id="resourceName"></div>
    <div class="form-group"><label class="form-label">Tipo</label><select class="form-select" id="resourceType"><option value="pdf">PDF</option><option value="video">Video</option><option value="doc">Documento</option></select></div>
    <div class="form-group"><label class="form-label">Archivo (opcional)</label><input type="file" class="form-input" id="resourceFile"></div>
    <div class="form-group"><label class="form-label">Meta (tamaño, duración...)</label><input class="form-input" id="resourceMeta" placeholder="Ej: 2.4 MB / 22 min"></div>
    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button class="btn btn-ghost" onclick="closeModal('resource')">Cancelar</button>
      <button class="btn btn-primary" onclick="saveResource()">Guardar</button>
    </div>
  </div>
</div>

<!-- Agregar/Editar Actividad -->
<div class="modal-overlay" id="modal-activity">
  <div class="modal">
    <div class="modal-header"><span class="modal-title" id="modalActivityTitle">Nueva Actividad</span><button class="modal-close" onclick="closeModal('activity')"><i class="fa-solid fa-xmark"></i></button></div>
    <input type="hidden" id="activityUnitId">
    <input type="hidden" id="activityEditId">
    <div class="form-group"><label class="form-label">Título</label><input class="form-input" id="activityTitle"></div>
    <div class="form-group"><label class="form-label">Descripción</label><textarea class="form-textarea" id="activityDesc"></textarea></div>
    <div class="form-group"><label class="form-label">Fecha límite</label><input type="date" class="form-input" id="activityDueDate"></div>
    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button class="btn btn-ghost" onclick="closeModal('activity')">Cancelar</button>
      <button class="btn btn-primary" onclick="saveActivity()">Guardar</button>
    </div>
  </div>
</div>

<!-- Matricular estudiante -->
<div class="modal-overlay" id="modal-enroll">
  <div class="modal">
    <div class="modal-header"><span class="modal-title">Matricular Estudiante</span><button class="modal-close" onclick="closeModal('enroll')"><i class="fa-solid fa-xmark"></i></button></div>
    <div class="form-group"><label class="form-label">Estudiante</label><select class="form-select" id="enrollStudent"></select></div>
    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button class="btn btn-ghost" onclick="closeModal('enroll')">Cancelar</button>
      <button class="btn btn-primary" onclick="enrollStudent()">Matricular</button>
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

function editIntro() {
  document.getElementById('editIntroDesc').value = document.getElementById('introText').textContent;
  openModal('editIntro');
}
async function saveIntro() {
  const desc = document.getElementById('editIntroDesc').value;
  await fetch('api/courses.php', {
    method: 'PUT',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({id: courseId, description: desc})
  });
  document.getElementById('introText').textContent = desc;
  closeModal('editIntro');
}

async function loadUnits() {
  const res = await fetch(`api/units.php?course_id=${courseId}`);
  const units = await res.json();
  const container = document.getElementById('unitsAccordion');
  if (units.length === 0) {
    container.innerHTML = `<p style="text-align:center;color:var(--gray-400);padding:20px;">No hay unidades. <a href="#" onclick="event.preventDefault(); showAddUnitModal()">Crea la primera</a></p>`;
    return;
  }
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
          <div class="edit-toolbar visible" style="gap:4px">
            <button class="btn btn-ghost btn-sm" onclick="event.stopPropagation(); editUnit(${unit.id}, '${unit.title.replace(/'/g, "\\'")}', '${(unit.description||'').replace(/'/g, "\\'")}', '${unit.icon_class}')"><i class="fa-solid fa-pen"></i></button>
            <button class="btn btn-ghost btn-sm" onclick="event.stopPropagation(); deleteUnit(${unit.id})"><i class="fa-solid fa-trash"></i></button>
          </div>
          <i class="fa-solid fa-chevron-down unit-chevron"></i>
        </div>
      </button>
      <div class="unit-acc-body">
        <div class="unit-section">
          <div class="unit-section-title">
            <span><i class="fa-solid fa-paperclip"></i> Recursos</span>
            <div class="edit-toolbar visible"><button class="btn btn-ghost btn-sm" onclick="event.stopPropagation(); showAddResourceModal(${unit.id})"><i class="fa-solid fa-plus"></i> Agregar</button></div>
          </div>
          <div class="resources-container" data-unit-id="${unit.id}">Cargando...</div>
        </div>
        <div class="unit-section">
          <div class="unit-section-title">
            <span><i class="fa-solid fa-pen-to-square"></i> Actividad</span>
            <div class="edit-toolbar visible"><button class="btn btn-ghost btn-sm" onclick="event.stopPropagation(); showAddActivityModal(${unit.id})"><i class="fa-solid fa-plus"></i> Agregar</button></div>
          </div>
          <div class="activity-container" data-unit-id="${unit.id}">Cargando...</div>
        </div>
      </div>
    </div>
  `).join('');
  units.forEach(unit => {
    loadResources(unit.id);
    loadActivity(unit.id);
  });
}

function showAddUnitModal() {
  document.getElementById('modalUnitTitle').textContent = 'Nueva Unidad';
  document.getElementById('unitEditId').value = '';
  document.getElementById('unitTitle').value = '';
  document.getElementById('unitDesc').value = '';
  document.getElementById('unitIcon').value = 'gen';
  openModal('unit');
}
function editUnit(id, title, desc, icon) {
  document.getElementById('modalUnitTitle').textContent = 'Editar Unidad';
  document.getElementById('unitEditId').value = id;
  document.getElementById('unitTitle').value = title;
  document.getElementById('unitDesc').value = desc;
  document.getElementById('unitIcon').value = icon;
  openModal('unit');
}
async function saveUnit() {
  const id = document.getElementById('unitEditId').value;
  const title = document.getElementById('unitTitle').value.trim();
  const description = document.getElementById('unitDesc').value.trim();
  const icon_class = document.getElementById('unitIcon').value;
  if (!title) return alert('El título es obligatorio');
  const method = id ? 'PUT' : 'POST';
  const body = { course_id: courseId, title, description, icon_class, order_index: 0 };
  if (id) body.id = id;
  await fetch('api/units.php', { method, headers: {'Content-Type': 'application/json'}, body: JSON.stringify(body) });
  closeModal('unit');
  loadUnits();
}
async function deleteUnit(id) {
  if (!confirm('¿Eliminar esta unidad y todo su contenido?')) return;
  await fetch(`api/units.php?id=${id}`, { method: 'DELETE' });
  loadUnits();
}

function showAddResourceModal(unitId) {
  document.getElementById('resourceUnitId').value = unitId;
  document.getElementById('resourceName').value = '';
  document.getElementById('resourceFile').value = '';
  document.getElementById('resourceMeta').value = '';
  openModal('resource');
}
async function saveResource() {
  const unitId = document.getElementById('resourceUnitId').value;
  const name = document.getElementById('resourceName').value.trim();
  const type = document.getElementById('resourceType').value;
  const meta = document.getElementById('resourceMeta').value.trim();
  const fileInput = document.getElementById('resourceFile');
  if (!name) return alert('Nombre obligatorio');
  const formData = new FormData();
  formData.append('unit_id', unitId);
  formData.append('name', name);
  formData.append('type', type);
  formData.append('meta', meta);
  if (fileInput.files.length > 0) formData.append('file', fileInput.files[0]);
  await fetch('api/resources.php', { method: 'POST', body: formData });
  closeModal('resource');
  loadUnits();
}
async function deleteResource(id) {
  if (!confirm('¿Eliminar recurso?')) return;
  await fetch(`api/resources.php?id=${id}`, { method: 'DELETE' });
  loadUnits();
}
async function loadResources(unitId) {
  const container = document.querySelector(`.resources-container[data-unit-id="${unitId}"]`);
  const res = await fetch(`api/resources.php?unit_id=${unitId}`);
  const resources = await res.json();
  if (!resources.length) {
    container.innerHTML = '<p style="font-size:13px;color:var(--gray-400)">Sin recursos.</p>';
    return;
  }
  container.innerHTML = resources.map(r => {
    const iconMap = { pdf: ['fa-file-pdf','res-pdf'], video: ['fa-circle-play','res-video'], doc: ['fa-file-lines','res-doc'] };
    const [ico, cls] = iconMap[r.type] || ['fa-file','res-doc'];
    return `<div class="resource-item">
      <div class="res-icon ${cls}"><i class="fa-solid ${ico}"></i></div>
      <div class="res-info"><div class="res-name">${r.name}</div><div class="res-meta">${r.meta||''}</div></div>
      <i class="fa-solid fa-trash" style="color:var(--red);cursor:pointer" onclick="deleteResource(${r.id})"></i>
    </div>`;
  }).join('');
}

function showAddActivityModal(unitId) {
  document.getElementById('modalActivityTitle').textContent = 'Nueva Actividad';
  document.getElementById('activityUnitId').value = unitId;
  document.getElementById('activityEditId').value = '';
  document.getElementById('activityTitle').value = '';
  document.getElementById('activityDesc').value = '';
  document.getElementById('activityDueDate').value = '';
  openModal('activity');
}
async function saveActivity() {
  const unitId = document.getElementById('activityUnitId').value;
  const editId = document.getElementById('activityEditId').value;
  const title = document.getElementById('activityTitle').value.trim();
  const description = document.getElementById('activityDesc').value.trim();
  const due_date = document.getElementById('activityDueDate').value;
  if (!title) return alert('Título obligatorio');
  const method = editId ? 'PUT' : 'POST';
  const body = { unit_id: unitId, title, description, due_date };
  if (editId) body.id = editId;
  await fetch('api/activities.php', { method, headers: {'Content-Type': 'application/json'}, body: JSON.stringify(body) });
  closeModal('activity');
  loadUnits();
}
async function deleteActivity(id) {
  if (!confirm('¿Eliminar actividad?')) return;
  await fetch(`api/activities.php?id=${id}`, { method: 'DELETE' });
  loadUnits();
}
async function loadActivity(unitId) {
  const container = document.querySelector(`.activity-container[data-unit-id="${unitId}"]`);
  const res = await fetch(`api/activities.php?unit_id=${unitId}`);
  const activities = await res.json();
  if (!activities.length) {
    container.innerHTML = '<p style="font-size:14px;color:var(--gray-500)">Sin actividad asignada.</p>';
    return;
  }
  const act = activities[0];
  container.innerHTML = `
    <div class="activity-box">
      <div class="activity-due"><i class="fa-solid fa-clock"></i> ${act.due_date || 'Sin fecha'}</div>
      <p class="section-text">${act.description}</p>
      <div style="margin-top:8px;display:flex;gap:8px">
        <button class="btn btn-ghost btn-sm" onclick="editActivity(${act.id}, '${act.title.replace(/'/g, "\\'")}', '${(act.description||'').replace(/'/g, "\\'")}', '${act.due_date||''}')"><i class="fa-solid fa-pen"></i> Editar</button>
        <button class="btn btn-ghost btn-sm" onclick="deleteActivity(${act.id})"><i class="fa-solid fa-trash"></i> Eliminar</button>
        <button class="btn btn-primary btn-sm" onclick="viewSubmissions(${act.id})"><i class="fa-solid fa-eye"></i> Ver entregas</button>
      </div>
    </div>
  `;
}
function editActivity(id, title, desc, due) {
  document.getElementById('modalActivityTitle').textContent = 'Editar Actividad';
  document.getElementById('activityEditId').value = id;
  document.getElementById('activityTitle').value = title;
  document.getElementById('activityDesc').value = desc;
  document.getElementById('activityDueDate').value = due;
  openModal('activity');
}

async function viewSubmissions(activityId) {
  const res = await fetch(`api/submissions.php?activity_id=${activityId}`);
  const subs = await res.json();
  let html = '<h3>Entregas</h3><div style="max-height:300px;overflow-y:auto">';
  if (!subs.length) html += '<p>No hay entregas.</p>';
  else subs.forEach(s => {
    html += `<div class="message">
      <p class="msg-user">${s.student_name}</p>
      <p class="msg-text">${s.content || 'Sin texto'}</p>
      ${s.file_path ? `<a href="${s.file_path}" target="_blank">Ver archivo</a>` : ''}
      <div style="margin-top:8px"><input type="number" id="grade-${s.id}" placeholder="Nota (0-10)" min="0" max="10" step="0.1" class="grade-input" value="${s.grade || ''}">
      <button class="btn btn-sm btn-primary" onclick="gradeSubmission(${s.id})">Calificar</button></div>
    </div>`;
  });
  html += '</div><button class="btn btn-ghost btn-sm" style="margin-top:10px" onclick="this.closest(\'.modal-overlay\').remove()">Cerrar</button>';
  const modal = document.createElement('div');
  modal.className = 'modal-overlay show';
  modal.innerHTML = `<div class="modal"><div class="modal-header"><span class="modal-title">Entregas</span><button class="modal-close" onclick="this.closest('.modal-overlay').remove()"><i class="fa-solid fa-xmark"></i></button></div>${html}</div>`;
  document.body.appendChild(modal);
  modal.addEventListener('click', e => { if (e.target === modal) modal.remove(); });
}
async function gradeSubmission(submissionId) {
  const input = document.getElementById('grade-' + submissionId);
  const grade = parseFloat(input.value);
  if (isNaN(grade) || grade < 0 || grade > 10) return alert('Nota inválida');
  await fetch('api/grades.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ submission_id: submissionId, grade }) });
  alert('Nota guardada');
}

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
async function showEnrollModal() {
  const res = await fetch('api/users.php?role=student');
  const students = await res.json();
  const select = document.getElementById('enrollStudent');
  select.innerHTML = students.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
  openModal('enroll');
}
async function enrollStudent() {
  const studentId = document.getElementById('enrollStudent').value;
  await fetch('api/enroll.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ course_id: courseId, student_id: studentId }) });
  closeModal('enroll');
  loadParticipants();
}

async function loadGrades() {
  const res = await fetch(`api/grades.php?course_id=${courseId}`);
  const data = await res.json();
  const studentsMap = {};
  const activitiesSet = new Set();
  data.forEach(row => {
    if (!studentsMap[row.student_id]) studentsMap[row.student_id] = { id: row.student_id, name: row.student_name, grades: {} };
    if (row.activity_id) {
      studentsMap[row.student_id].grades[row.activity_id] = row.grade;
      activitiesSet.add(row.activity_id);
    }
  });
  const activitiesArr = Array.from(activitiesSet);
  const table = document.getElementById('gradesTable');
  if (activitiesArr.length === 0) {
    table.innerHTML = '<tr><td colspan="2">No hay actividades con entregas.</td></tr>';
    return;
  }
  let html = '<thead><tr><th>Estudiante</th>';
  activitiesArr.forEach(aId => {
    const act = data.find(d => d.activity_id == aId);
    html += `<th>${act ? act.actividad : ''}</th>`;
  });
  html += '</tr></thead><tbody>';
  Object.entries(studentsMap).forEach(([sId, st]) => {
    html += `<tr><td>${st.name}</td>`;
    activitiesArr.forEach(aId => {
      const grade = st.grades[aId] || '';
      html += `<td><input class="grade-input" type="number" min="0" max="10" step="0.1" value="${grade}" data-student="${sId}" data-activity="${aId}"></td>`;
    });
    html += '</tr>';
  });
  html += '</tbody>';
  table.innerHTML = html;
}
function saveGrades() {
  document.querySelectorAll('.grade-input').forEach(async input => {
    const studentId = input.dataset.student;
    const activityId = input.dataset.activity;
    const grade = input.value;
    if (grade === '') return;
    const res = await fetch(`api/grades.php?course_id=${courseId}`);
    const data = await res.json();
    const sub = data.find(d => d.student_id == studentId && d.activity_id == activityId);
    if (sub && sub.submission_id) {
      await fetch('api/grades.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ submission_id: sub.submission_id, grade: parseFloat(grade) }) });
    }
  });
  alert('Calificaciones guardadas.');
}

function showNewForumModal() { openModal('newForum'); }
async function createForum() {
  const title = document.getElementById('newForumTitle').value.trim();
  const description = document.getElementById('newForumDesc').value.trim();
  if (!title) return alert('Título requerido');
  await fetch('api/forums.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ course_id: courseId, title, description }) });
  closeModal('newForum');
  loadForums();
}
async function deleteForum(id) {
  if (!confirm('¿Eliminar foro?')) return;
  await fetch(`api/forums.php?id=${id}`, { method: 'DELETE' });
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
        <button class="btn btn-ghost btn-sm" style="margin-top:6px" onclick="deleteForum(${f.id})"><i class="fa-solid fa-trash"></i> Eliminar foro</button>
      </div>
    </div>
  `).join('');
  forums.forEach(f => loadForumMessages(f.id));
}
async function loadForumMessages(forumId) {
  const container = document.getElementById('messages-' + forumId);
  if (!container) return;
  const res = await fetch(`api/messages.php?forum_id=${forumId}`);
  const messages = await res.json();
  container.innerHTML = messages.map(m => `
    <div class="message"><p class="msg-user">${m.user_name}</p><p class="msg-text">${m.content}</p></div>
  `).join('');
}

loadUnits();
loadForums();
</script>
</body>
</html>