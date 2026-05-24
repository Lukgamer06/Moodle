<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') { header('Location: login.html'); exit; }
$user = $_SESSION['user'];
$course_id = $_GET['course_id'] ?? null;
require_once 'api/config.php';

$course = null;
if ($course_id) {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Mini Moodle</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/estilos.css">
</head>
<body class="course-page">

<header class="topbar">
  <div class="topbar-left">
    <div class="logo-mark">M</div>
    <span class="course-title"><?php echo $course ? htmlspecialchars($course['name']) : 'Panel de Administración'; ?></span>
  </div>
  <div class="topbar-right">
    <span class="role-badge-static">Administrador</span>
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
    <?php if ($course): ?>
    <i id="openForumBtn" class="fa-solid fa-comments forum-icon" title="Foros"></i>
    <?php endif; ?>
  </div>
</header>

<?php if ($course_id && $course): ?>
<!-- ========== MODO CURSO ========== -->
<nav class="nav-area">
  <button class="nav-btn active" onclick="showScreen('course')"><i class="fa-solid fa-book"></i> Curso</button>
  <button class="nav-btn" onclick="showScreen('participants')"><i class="fa-solid fa-users"></i> Participantes</button>
  <button class="nav-btn" onclick="showScreen('grades')"><i class="fa-solid fa-graduation-cap"></i> Calificaciones</button>
</nav>

<div class="course-back-area">
  <button class="course-back-btn" onclick="window.location.href='dashboard.php'">
    <i class="fa-solid fa-arrow-left"></i>
    <span>Regresar</span>
  </button>
</div>

<main class="page-wrap">
  <div class="admin-notice visible"><i class="fa-solid fa-shield-halved"></i> Modo Administrador — Puedes editar todo el contenido.</div>
  
  <div id="screen-course" class="screen active">
    <div class="card customizable-card" id="courseIntroCard" style="background:<?php echo htmlspecialchars($course['card_color'] ?? '#FFFFFF'); ?>">
      <div class="card-header">
        <h2 class="section-title">Presentación del Curso</h2>
        <div class="edit-toolbar visible">
          <button class="btn btn-ghost btn-sm" onclick="editIntro()">
            <i class="fa-solid fa-pen"></i> Editar
          </button>
        </div>
      </div>

      <div class="section-text html-content" id="introText">
        <?php echo $course['description'] ?? ''; ?>
      </div>
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

  <!-- Foros -->
  <aside id="forumDrawer" class="drawer">
    <div class="drawer-header">
      <h2 class="drawer-title">Foros del Curso</h2>
      <i id="closeForumBtn" class="fa-solid fa-xmark close-icon"></i>
    </div>
    <div class="forum-list" id="forumList"></div>
    <button class="add-forum-btn" id="addForumBtn" onclick="showNewForumModal()"><i class="fa-solid fa-plus"></i> Nuevo Foro</button>
  </aside>
  <div id="overlay" class="overlay"></div>

 <!-- MODALES-->
<!-- Editar Introducción / Personalizar Curso -->
<div class="modal-overlay" id="modal-editIntro">
  <div class="modal modal-lg">
    <div class="modal-header">
      <span class="modal-title">Personalizar Curso</span>
      <button class="modal-close" onclick="closeModal('editIntro')">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div class="form-group">
      <label class="form-label">Presentación del curso</label>

      <div class="editor-toolbar">
        <button type="button" onmousedown="event.preventDefault()" onclick="formatEditor('bold')">
          <b>B</b>
        </button>

        <button type="button" onmousedown="event.preventDefault()" onclick="formatEditor('italic')">
          <i>I</i>
        </button>

        <button type="button" onmousedown="event.preventDefault()" onclick="formatEditor('underline')">
          <u>U</u>
        </button>

        <button type="button" onmousedown="event.preventDefault()" onclick="formatEditor('insertUnorderedList')">
          <i class="fa-solid fa-list-ul"></i>
        </button>

        <button type="button" onmousedown="event.preventDefault()" onclick="formatEditor('justifyLeft')">
          <i class="fa-solid fa-align-left"></i>
        </button>

        <button type="button" onmousedown="event.preventDefault()" onclick="formatEditor('justifyCenter')">
          <i class="fa-solid fa-align-center"></i>
        </button>

        <button type="button" onmousedown="event.preventDefault()" onclick="insertImageUrl('introEditor')">
          <i class="fa-solid fa-image"></i> Imagen URL
        </button>

        <button type="button" onmousedown="event.preventDefault()" onclick="resizeSelectedImage('small')">
          Imagen pequeña
        </button>

        <button type="button" onmousedown="event.preventDefault()" onclick="resizeSelectedImage('medium')">
          Imagen mediana
        </button>

        <button type="button" onmousedown="event.preventDefault()" onclick="resizeSelectedImage('large')">
          Imagen grande
        </button>

        <button type="button" onmousedown="event.preventDefault()" onclick="centerSelectedImage()">
          <i class="fa-solid fa-align-center"></i> Centrar imagen
        </button>

        <button type="button" onmousedown="event.preventDefault()" onclick="deleteSelectedImage()">
          <i class="fa-solid fa-trash"></i> Borrar imagen
        </button>
      </div>

      <div id="introEditor" class="html-editor" contenteditable="true"></div>
    </div>

    <div class="form-group">
      <label class="form-label">Color de la caja de presentación</label>

      <input 
        type="hidden" 
        id="courseCardColor" 
        value="<?php echo htmlspecialchars($course['card_color'] ?? '#FFFFFF'); ?>"
      >

      <div class="color-palette">
        <button type="button" class="color-dot" style="background:#FFF9C7" onclick="selectCourseColor('#FFF9C7')" title="Amarillo suave"></button>
        <button type="button" class="color-dot" style="background:#C7D1FF" onclick="selectCourseColor('#C7D1FF')" title="Azul suave"></button>
        <button type="button" class="color-dot" style="background:#FFF8E8" onclick="selectCourseColor('#FFF8E8')" title="Crema"></button>
        <button type="button" class="color-dot" style="background:#C2FFCB" onclick="selectCourseColor('#C2FFCB')" title="Verde suave"></button>
        <button type="button" class="color-dot" style="background:#FEE3FF" onclick="selectCourseColor('#FEE3FF')" title="Rosado suave"></button>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Imagen de portada para el dashboard</label>

      <input 
        type="file" 
        id="courseCoverInput" 
        class="form-input" 
        accept="image/png"
      >

      <small style="display:block;margin-top:6px;color:#64748b;">
        Solo formato PNG. Esta imagen aparecerá en la tarjeta del curso en el dashboard.
      </small>

      <div style="display:flex;gap:10px;margin-top:10px;flex-wrap:wrap;">
        <button type="button" class="btn btn-ghost btn-sm" onclick="uploadCourseCover()">
          <i class="fa-solid fa-upload"></i> Subir/Reemplazar portada
        </button>

        <button type="button" class="btn btn-ghost btn-sm" style="color:var(--red)" onclick="deleteCourseCover()">
          <i class="fa-solid fa-trash"></i> Eliminar portada
        </button>
      </div>
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button class="btn btn-ghost" onclick="closeModal('editIntro')">Cancelar</button>
      <button class="btn btn-primary" onclick="saveIntro()">Guardar presentación</button>
    </div>
  </div>
</div>

  <!-- Agregar/Editar Unidad -->
  <div class="modal-overlay" id="modal-unit">
    <div class="modal">
      <div class="modal-header"><span class="modal-title" id="modalUnitTitle">Nueva Unidad</span><button class="modal-close" onclick="closeModal('unit')"><i class="fa-solid fa-xmark"></i></button></div>
      <input type="hidden" id="unitEditId">
      <div class="form-group"><label class="form-label">Título</label><input class="form-input" id="unitTitle"></div>
      <div class="form-group">
        <label class="form-label">Descripción breve</label>

        <div class="editor-toolbar">
          <button type="button" onclick="formatEditor('bold')"><b>B</b></button>
          <button type="button" onclick="formatEditor('italic')"><i>I</i></button>
          <button type="button" onclick="formatEditor('underline')"><u>U</u></button>
          <button type="button" onclick="formatEditor('insertUnorderedList')">
            <i class="fa-solid fa-list-ul"></i>
          </button>
          <button type="button" onclick="insertImageUrl('unitDesc')">
            <i class="fa-solid fa-image"></i> Imagen URL
          </button>
        </div>

        <div id="unitDesc" class="html-editor small-editor" contenteditable="true"></div>
      </div>

      <div class="form-group">
        <label class="form-label">Color de la unidad</label>
        <input type="hidden" id="unitCardColor" value="#FFFFFF">

        <div class="color-palette">
          <button type="button" class="color-dot" style="background:#FFF9C7" onclick="selectUnitColor('#FFF9C7')"></button>
          <button type="button" class="color-dot" style="background:#C7D1FF" onclick="selectUnitColor('#C7D1FF')"></button>
          <button type="button" class="color-dot" style="background:#FFF8E8" onclick="selectUnitColor('#FFF8E8')"></button>
          <button type="button" class="color-dot" style="background:#C2FFCB" onclick="selectUnitColor('#C2FFCB')"></button>
          <button type="button" class="color-dot" style="background:#FEE3FF" onclick="selectUnitColor('#FEE3FF')"></button>
        </div>
      </div>
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
      <div class="form-group"><label class="form-label">Tipo</label><select class="form-select" id="resourceType" onchange="toggleResourceFields()"><option value="pdf">PDF</option><option value="video">Video</option><option value="doc">Documento</option></select></div>
      <div class="form-group" id="resourceFileGroup"><label class="form-label">Archivo</label><input type="file" class="form-input" id="resourceFile" accept=".pdf,.doc,.docx"></div>
      <div class="form-group" id="resourceVideoGroup" style="display:none"><label class="form-label">Enlace de YouTube</label><input class="form-input" id="resourceYoutubeUrl" placeholder="https://www.youtube.com/watch?v=..."></div>
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
      <div class="form-group"><label class="form-label">Tipo</label><select class="form-select" id="activityType" onchange="toggleQuestionEditor()"><option value="activity">Actividad</option><option value="quiz">Quiz</option><option value="evaluation">Evaluación</option></select></div>
      <div class="form-group"><label class="form-label">Título</label><input class="form-input" id="activityTitle"></div>
      <div class="form-group"><label class="form-label">Descripción</label><textarea class="form-textarea" id="activityDesc"></textarea></div>
      <div class="form-group"><label class="form-label">Fecha límite</label><input type="date" class="form-input" id="activityDueDate"></div>
      <div id="questionEditor" style="display:none">
        <div class="form-group"><label class="form-label">Preguntas</label><div id="questionsList"></div></div>
        <button class="btn btn-ghost btn-sm" onclick="addQuestionRow()"><i class="fa-solid fa-plus"></i> Agregar Pregunta</button>
      </div>
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
      <div class="form-group"><label class="form-label">Buscar estudiante</label><input class="form-input" id="studentSearch" placeholder="Filtrar por nombre o correo" oninput="filterStudents()"></div>
      <div class="form-group"><label class="form-label">Estudiante</label><select class="form-select" id="enrollStudentSelect"></select></div>
      <div class="form-group"><label class="form-label">Matrícula masiva por correos</label><textarea class="form-textarea" id="bulkEnrollEmails" placeholder="correo1@dominio.com&#10;correo2@dominio.com"></textarea></div>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button class="btn btn-ghost" onclick="closeModal('enroll')">Cancelar</button>
        <button class="btn btn-primary" onclick="enrollStudentToCourse()">Matricular</button>
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

</main>

<script src="js/app.js"></script>
<script>
const courseId = <?php echo json_encode($course_id !== null ? intval($course_id) : null); ?>;
const userId = <?php echo json_encode($user['id']); ?>;

function escapeAttr(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}

function jsStringArg(value) {
  const escaped = String(value ?? '')
    .replace(/\\/g, '\\\\')
    .replace(/'/g, "\\'")
    .replace(/\r/g, '\\r')
    .replace(/\n/g, '\\n');
  return escapeAttr(`'${escaped}'`);
}

// ── INTRODUCCIÓN ──
let currentCourseCardColor = <?php echo json_encode($course['card_color'] ?? '#FFFFFF'); ?>;

function formatEditor(command) {
  document.execCommand(command, false, null);
}

let selectedEditorImage = null;

document.addEventListener('click', function (e) {
  const clickedImage = e.target.closest('.html-editor img');

  if (!clickedImage) return;

  if (selectedEditorImage) {
    selectedEditorImage.classList.remove('selected-editor-img');
  }

  selectedEditorImage = clickedImage;
  selectedEditorImage.classList.add('selected-editor-img');
});

function cleanImageUrl(value) {
  value = String(value || '').trim();

  const match = value.match(/src=["']([^"']+)["']/i);
  if (match && match[1]) {
    return match[1];
  }

  return value;
}

function insertImageUrl(editorId) {
  let url = prompt('Pega SOLO la URL de la imagen. No pegues código HTML.');
  if (!url) return;

  url = cleanImageUrl(url);

  if (!url.startsWith('http://') && !url.startsWith('https://')) {
    alert('Debes pegar una URL válida que empiece por http:// o https://');
    return;
  }

  const editor = document.getElementById(editorId);
  if (!editor) return;

  const alt = prompt('Texto alternativo de la imagen:', 'Imagen del curso') || 'Imagen del curso';

  const img = document.createElement('img');
  img.src = url;
  img.alt = alt;
  img.className = 'course-img course-img-medium course-img-center';

  const spaceAfter = document.createElement('p');
  spaceAfter.innerHTML = '<br>';

  editor.focus();

  const selection = window.getSelection();

  if (selection && selection.rangeCount > 0 && editor.contains(selection.anchorNode)) {
    const range = selection.getRangeAt(0);

    range.deleteContents();
    range.insertNode(spaceAfter);
    range.insertNode(img);

    const newRange = document.createRange();
    newRange.setStart(spaceAfter, 0);
    newRange.collapse(true);

    selection.removeAllRanges();
    selection.addRange(newRange);
  } else {
    editor.appendChild(img);
    editor.appendChild(spaceAfter);

    const newRange = document.createRange();
    newRange.setStart(spaceAfter, 0);
    newRange.collapse(true);

    selection.removeAllRanges();
    selection.addRange(newRange);
  }

  if (selectedEditorImage) {
    selectedEditorImage.classList.remove('selected-editor-img');
  }

  selectedEditorImage = img;
  selectedEditorImage.classList.add('selected-editor-img');
}

function getSelectedImage() {
  if (selectedEditorImage && document.body.contains(selectedEditorImage)) {
    return selectedEditorImage;
  }

  alert('Primero haz clic sobre la imagen dentro del editor. Debe quedar marcada con un borde azul.');
  return null;
}

function resizeSelectedImage(size) {
  const img = getSelectedImage();
  if (!img) return;

  img.classList.remove('course-img-small', 'course-img-medium', 'course-img-large');

  if (size === 'small') {
    img.classList.add('course-img-small');
  } else if (size === 'large') {
    img.classList.add('course-img-large');
  } else {
    img.classList.add('course-img-medium');
  }

  img.classList.add('course-img', 'course-img-center');
}

function centerSelectedImage() {
  const img = getSelectedImage();
  if (!img) return;

  img.classList.add('course-img-center');
}

function deleteSelectedImage() {
  const img = getSelectedImage();
  if (!img) return;

  img.remove();
  selectedEditorImage = null;
}

function selectCourseColor(color) {
  currentCourseCardColor = color;

  const inputColor = document.getElementById('courseCardColor');
  if (inputColor) inputColor.value = color;

  const introCard = document.getElementById('courseIntroCard');
  if (introCard) introCard.style.background = color;

  document.querySelectorAll('#modal-editIntro .color-dot').forEach(dot => {
    dot.classList.remove('selected');
  });

  const selectedDot = Array.from(document.querySelectorAll('#modal-editIntro .color-dot'))
    .find(dot => dot.getAttribute('onclick')?.includes(color));

  if (selectedDot) selectedDot.classList.add('selected');
}

function editIntro() {
  const editor = document.getElementById('introEditor');
  const introText = document.getElementById('introText');

  editor.innerHTML = introText.innerHTML;

  const colorInput = document.getElementById('courseCardColor');
  if (colorInput) colorInput.value = currentCourseCardColor || '#FFFFFF';

  openModal('editIntro');
}

async function saveIntro() {
  const desc = document.getElementById('introEditor').innerHTML;
  const cardColor = document.getElementById('courseCardColor').value || '#FFFFFF';

  await fetch('api/courses.php', {
    method: 'PUT',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
      id: courseId,
      description: desc,
      card_color: cardColor
    })
  });

  document.getElementById('introText').innerHTML = desc;
  document.getElementById('courseIntroCard').style.background = cardColor;
  currentCourseCardColor = cardColor;

  closeModal('editIntro');
}

// ── UNIDADES ──
async function loadUnits() {
  const res = await fetch(`api/units.php?course_id=${courseId}`);
  const units = await res.json();
  const container = document.getElementById('unitsAccordion');
  if (units.length === 0) {
    container.innerHTML = `<p style="text-align:center;color:var(--gray-400);padding:20px;">No hay unidades todavía. <a href="#" onclick="event.preventDefault(); showAddUnitModal()">Crea la primera</a></p>`;
    return;
  }
  container.innerHTML = units.map(unit => `
    <div class="unit-acc-item customizable-card" style="background:${unit.card_color || '#FFFFFF'}">
      <div class="unit-acc-header" 
           style="background:${unit.card_color || '#FFFFFF'}"
           role="button" 
           tabindex="0" 
           onclick="toggleUnit(this)" 
           onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleUnit(this)}">
        <div class="unit-acc-left">
          <div class="unit-icon-sm ${unit.icon_class}"><i class="fa-solid fa-book"></i></div>
          <div>
            <div class="unit-acc-title">${escapeAttr(unit.title)}</div>
            <div class="unit-acc-desc html-content">${unit.description || ''}</div>
          </div>
        </div>
        <div class="unit-acc-right">
          <div class="edit-toolbar visible" style="gap:4px">
            <button class="btn btn-ghost btn-sm" onclick="event.stopPropagation(); editUnit(${unit.id}, ${jsStringArg(unit.title)}, ${jsStringArg(unit.description || '')}, ${jsStringArg(unit.icon_class)}, ${jsStringArg(unit.card_color || '#FFFFFF')})"><i class="fa-solid fa-pen"></i></button>
            <button class="btn btn-ghost btn-sm" onclick="event.stopPropagation(); deleteUnit(${unit.id})"><i class="fa-solid fa-trash"></i></button>
          </div>
          <i class="fa-solid fa-chevron-down unit-chevron"></i>
        </div>
      </div>
      <div class="unit-acc-body" style="background:${unit.card_color || '#FFFFFF'}">
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

let currentUnitCardColor = '#FFFFFF';

function selectUnitColor(color) {
  currentUnitCardColor = color;

  const input = document.getElementById('unitCardColor');
  if (input) input.value = color;

  document.querySelectorAll('#modal-unit .color-dot').forEach(dot => {
    dot.classList.remove('selected');
  });

  const selectedDot = Array.from(document.querySelectorAll('#modal-unit .color-dot'))
    .find(dot => dot.getAttribute('onclick')?.includes(color));

  if (selectedDot) selectedDot.classList.add('selected');

  const modal = document.querySelector('#modal-unit .modal');
  if (modal) modal.style.border = `3px solid ${color}`;
}

function showAddUnitModal() {
  document.getElementById('modalUnitTitle').textContent = 'Nueva Unidad';
  document.getElementById('unitEditId').value = '';
  document.getElementById('unitTitle').value = '';
  document.getElementById('unitDesc').innerHTML = '';
  document.getElementById('unitIcon').value = 'gen';

  const unitColor = document.getElementById('unitCardColor');
  if (unitColor) unitColor.value = '#FFFFFF';

  currentUnitCardColor = '#FFFFFF';

  document.querySelectorAll('#modal-unit .color-dot').forEach(dot => {
    dot.classList.remove('selected');
  });

  const modal = document.querySelector('#modal-unit .modal');
  if (modal) modal.style.border = '';

  openModal('unit');
}

function editUnit(id, title, desc, icon, cardColor = '#FFFFFF') {
  document.getElementById('modalUnitTitle').textContent = 'Editar Unidad';
  document.getElementById('unitEditId').value = id;
  document.getElementById('unitTitle').value = title;
  document.getElementById('unitDesc').innerHTML = desc;
  document.getElementById('unitIcon').value = icon;
  document.getElementById('unitCardColor').value = cardColor;

  currentUnitCardColor = cardColor;

  document.querySelectorAll('#modal-unit .color-dot').forEach(dot => {
    dot.classList.remove('selected');
  });

  const selectedDot = Array.from(document.querySelectorAll('#modal-unit .color-dot'))
    .find(dot => dot.getAttribute('onclick')?.includes(cardColor));

  if (selectedDot) selectedDot.classList.add('selected');

  const modal = document.querySelector('#modal-unit .modal');
  if (modal) modal.style.border = `3px solid ${cardColor}`;

  openModal('unit');
}

async function saveUnit() {
  const id = document.getElementById('unitEditId').value;
  const title = document.getElementById('unitTitle').value.trim();
  const description = document.getElementById('unitDesc').innerHTML.trim();
  const icon_class = document.getElementById('unitIcon').value;
  const card_color = document.getElementById('unitCardColor').value || '#FFFFFF';

  if (!title) return alert('El título es obligatorio');

  const method = id ? 'PUT' : 'POST';
  const body = {
    course_id: courseId,
    title,
    description,
    icon_class,
    order_index: 0,
    card_color
  };

  if (id) body.id = id;

  const res = await fetch('api/units.php', {
    method,
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(body)
  });

  if (!res.ok) {
    let data = {};
    try {
      data = await res.json();
    } catch (e) {}
    alert(data.error || 'No se pudo guardar la unidad.');
    return;
  }

  closeModal('unit');
  loadUnits();
}

async function deleteUnit(id) {
  if (!confirm('¿Eliminar esta unidad y todo su contenido?')) return;
  await fetch(`api/units.php?id=${id}`, { method: 'DELETE' });
  loadUnits();
}

// ── RECURSOS ──
function showAddResourceModal(unitId) {
  document.getElementById('resourceUnitId').value = unitId;
  document.getElementById('resourceName').value = '';
  document.getElementById('resourceType').value = 'pdf';
  document.getElementById('resourceFile').value = '';
  document.getElementById('resourceYoutubeUrl').value = '';
  document.getElementById('resourceMeta').value = '';
  toggleResourceFields();
  openModal('resource');
}
function toggleResourceFields() {
  const type = document.getElementById('resourceType').value;
  document.getElementById('resourceFileGroup').style.display = type === 'video' ? 'none' : 'block';
  document.getElementById('resourceVideoGroup').style.display = type === 'video' ? 'block' : 'none';
  document.getElementById('resourceFile').accept = type === 'pdf' ? '.pdf' : '.pdf,.doc,.docx';
}
async function saveResource() {
  const unitId = document.getElementById('resourceUnitId').value;
  const name = document.getElementById('resourceName').value.trim();
  const type = document.getElementById('resourceType').value;
  const meta = document.getElementById('resourceMeta').value.trim();
  const fileInput = document.getElementById('resourceFile');
  const youtubeUrl = document.getElementById('resourceYoutubeUrl').value.trim();
  if (!name) return alert('Nombre obligatorio');
  if (type === 'video' && !youtubeUrl) return alert('Enlace de YouTube obligatorio');
  if (type === 'pdf' && fileInput.files.length === 0) return alert('Carga un PDF');
  const formData = new FormData();
  formData.append('unit_id', unitId);
  formData.append('name', name);
  formData.append('type', type);
  formData.append('meta', meta);
  formData.append('youtube_url', youtubeUrl);
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
      <div class="res-info"><div class="res-name">${r.file_path ? `<a href="${escapeAttr(r.file_path)}" target="_blank">${escapeAttr(r.name)}</a>` : escapeAttr(r.name)}</div><div class="res-meta">${escapeAttr(r.meta||'')}</div></div>
      <i class="fa-solid fa-trash" style="color:var(--red);cursor:pointer" onclick="deleteResource(${r.id})"></i>
    </div>`;
  }).join('');
}

async function uploadCourseCover() {
  const input = document.getElementById('courseCoverInput');

  if (!input || !input.files || input.files.length === 0) {
    alert('Primero selecciona una imagen PNG.');
    return;
  }

  const file = input.files[0];

  if (file.type !== 'image/png') {
    alert('La imagen de portada debe estar en formato PNG.');
    return;
  }

  const formData = new FormData();
  formData.append('course_id', courseId);
  formData.append('cover', file);

  const res = await fetch('api/course_cover.php', {
    method: 'POST',
    body: formData
  });

  let data = {};
  try {
    data = await res.json();
  } catch (e) {}

  if (!res.ok) {
    alert(data.error || 'No se pudo subir la imagen de portada.');
    return;
  }

  input.value = '';
  alert('Imagen de portada actualizada correctamente.');
}

async function deleteCourseCover() {
  if (!confirm('¿Eliminar la imagen de portada de este curso?')) return;

  const res = await fetch(`api/course_cover.php?course_id=${courseId}`, {
    method: 'DELETE'
  });

  let data = {};
  try {
    data = await res.json();
  } catch (e) {}

  if (!res.ok) {
    alert(data.error || 'No se pudo eliminar la imagen de portada.');
    return;
  }

  alert('Imagen de portada eliminada correctamente.');
}

// ── ACTIVIDADES ──
function activityLabel(type) {
  return ({ activity:'Actividad', quiz:'Quiz', evaluation:'Evaluación' })[type] || 'Actividad';
}
function toggleQuestionEditor() {
  const type = document.getElementById('activityType').value;
  const editor = document.getElementById('questionEditor');
  editor.style.display = type === 'activity' ? 'none' : 'block';
  if (type !== 'activity' && document.querySelectorAll('#questionsList .question-row').length === 0) addQuestionRow();
}
function addQuestionRow(data = {}) {
  const list = document.getElementById('questionsList');
  const row = document.createElement('div');
  row.className = 'question-row';
  row.style.marginBottom = '12px';
  row.innerHTML = `
    <input class="form-input q-question" placeholder="Pregunta" value="${escapeAttr(data.question || '')}" style="margin-bottom:6px">
    <input class="form-input q-a" placeholder="Respuesta A" value="${escapeAttr(data.option_a || '')}" style="margin-bottom:6px">
    <input class="form-input q-b" placeholder="Respuesta B" value="${escapeAttr(data.option_b || '')}" style="margin-bottom:6px">
    <input class="form-input q-c" placeholder="Respuesta C" value="${escapeAttr(data.option_c || '')}" style="margin-bottom:6px">
    <input class="form-input q-d" placeholder="Respuesta D" value="${escapeAttr(data.option_d || '')}" style="margin-bottom:6px">
    <select class="form-select q-correct"><option value="a">Correcta: A</option><option value="b">Correcta: B</option><option value="c">Correcta: C</option><option value="d">Correcta: D</option></select>
    <button class="btn btn-ghost btn-sm" style="margin-top:6px" onclick="this.closest('.question-row').remove()">Eliminar pregunta</button>
  `;
  list.appendChild(row);
  row.querySelector('.q-correct').value = data.correct_option || 'a';
}
function collectQuestions() {
  return Array.from(document.querySelectorAll('#questionsList .question-row')).map(row => ({
    question: row.querySelector('.q-question').value.trim(),
    option_a: row.querySelector('.q-a').value.trim(),
    option_b: row.querySelector('.q-b').value.trim(),
    option_c: row.querySelector('.q-c').value.trim(),
    option_d: row.querySelector('.q-d').value.trim(),
    correct_option: row.querySelector('.q-correct').value
  })).filter(q => q.question);
}

function showAddActivityModal(unitId) {
  document.getElementById('modalActivityTitle').textContent = 'Nueva Actividad';
  document.getElementById('activityUnitId').value = unitId;
  document.getElementById('activityEditId').value = '';
  document.getElementById('activityType').value = 'activity';
  document.getElementById('activityTitle').value = '';
  document.getElementById('activityDesc').value = '';
  document.getElementById('activityDueDate').value = '';
  document.getElementById('questionsList').innerHTML = '';
  toggleQuestionEditor();
  openModal('activity');
}
async function saveActivity() {
  const unitId = document.getElementById('activityUnitId').value;
  const editId = document.getElementById('activityEditId').value;
  const activity_type = document.getElementById('activityType').value;
  const title = document.getElementById('activityTitle').value.trim();
  const description = document.getElementById('activityDesc').value.trim();
  const due_date = document.getElementById('activityDueDate').value;
  if (!title) return alert('Título obligatorio');
  const questions = activity_type === 'activity' ? [] : collectQuestions();
  if (activity_type !== 'activity' && questions.length === 0) return alert('Agrega al menos una pregunta');
  const method = editId ? 'PUT' : 'POST';
  const body = { unit_id: unitId, activity_type, title, description, due_date, questions };
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
  const res = await fetch(`api/activities.php?unit_id=${unitId}&include_questions=1`);
  const activities = await res.json();
  if (!activities.length) {
    container.innerHTML = '<p style="font-size:14px;color:var(--gray-500)">Sin actividad asignada.</p>';
    return;
  }
  container.innerHTML = activities.map(act => `
    <div class="activity-box">
      <div class="activity-due"><i class="fa-solid fa-list-check"></i> ${activityLabel(act.activity_type)}</div>
      <div class="activity-due"><i class="fa-solid fa-clock"></i> ${act.due_date || 'Sin fecha'}</div>
      <p class="section-text">${escapeAttr(act.description)}</p>
      <div style="margin-top:8px;display:flex;gap:8px">
        <button class="btn btn-ghost btn-sm" onclick="editActivity(${act.id}, ${jsStringArg(act.title)}, ${jsStringArg(act.description || '')}, ${jsStringArg(act.due_date || '')}, ${jsStringArg(act.activity_type || 'activity')}, ${escapeAttr(JSON.stringify(act.questions || []))})"><i class="fa-solid fa-pen"></i> Editar</button>
        <button class="btn btn-ghost btn-sm" onclick="deleteActivity(${act.id})"><i class="fa-solid fa-trash"></i> Eliminar</button>
        <button class="btn btn-primary btn-sm" onclick="viewSubmissions(${act.id})"><i class="fa-solid fa-eye"></i> Ver entregas</button>
      </div>
    </div>
  `).join('');
}
function editActivity(id, title, desc, due, type = 'activity', questions = []) {
  document.getElementById('modalActivityTitle').textContent = 'Editar Actividad';
  document.getElementById('activityEditId').value = id;
  document.getElementById('activityType').value = type;
  document.getElementById('activityTitle').value = title;
  document.getElementById('activityDesc').value = desc;
  document.getElementById('activityDueDate').value = due;
  document.getElementById('questionsList').innerHTML = '';
  questions.forEach(q => addQuestionRow(q));
  toggleQuestionEditor();
  openModal('activity');
}

// ── ENTREGAS ──
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
let allStudents = [];
function renderStudentOptions(students) {
  document.getElementById('enrollStudentSelect').innerHTML = students.map(s => `<option value="${s.id}">${escapeAttr(s.name)} (${escapeAttr(s.email)})</option>`).join('');
}
function filterStudents() {
  const term = document.getElementById('studentSearch').value.trim().toLowerCase();
  renderStudentOptions(allStudents.filter(s => s.name.toLowerCase().includes(term) || s.email.toLowerCase().includes(term)));
}
async function showEnrollModal() {
  const res = await fetch('api/users.php?role=student');
  allStudents = await res.json();
  document.getElementById('studentSearch').value = '';
  document.getElementById('bulkEnrollEmails').value = '';
  renderStudentOptions(allStudents);
  openModal('enroll');
}
async function enrollStudentToCourse() {
  const bulkEmails = document.getElementById('bulkEnrollEmails').value.trim();
  if (bulkEmails) {
    const res = await fetch('api/enroll.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ course_id: courseId, emails: bulkEmails }) });
    const data = await res.json();
    if (!res.ok) return alert(data.error || 'Error al matricular');
    alert(`Matriculados: ${data.added.length}. Ya estaban: ${data.already.length}. No encontrados: ${data.missing.length}.`);
    closeModal('enroll');
    loadParticipants();
    return;
  }
  const studentId = document.getElementById('enrollStudentSelect').value;
  await fetch('api/enroll.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ course_id: courseId, student_id: studentId }) });
  closeModal('enroll');
  loadParticipants();
}

// ── CALIFICACIONES ──
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

// ── FOROS ──
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

// Iniciar
loadUnits();
loadForums();
</script>

<?php else: ?>
<!-- ========== PANEL GENERAL ========== -->
<main class="page-wrap">
  <div class="admin-notice visible"><i class="fa-solid fa-shield-halved"></i> Panel de Administración</div>
  
  <div class="card">
    <div class="card-header">
      <h2 class="section-title">Todos los Cursos</h2>
      <button class="btn btn-accent" onclick="openCreateCourseModal()"><i class="fa-solid fa-plus"></i> Crear Curso</button>
    </div>
    <div class="course-grid" id="allCoursesGrid"></div>
  </div>
  
  <div class="card">
    <div class="card-header"><h2 class="section-title">Usuarios</h2></div>
    <div class="participants-list" id="usersList"></div>
  </div>
</main>

<!-- Modal crear curso -->
<div class="modal-overlay" id="modal-createCourse">
  <div class="modal">
    <div class="modal-header"><span class="modal-title">Nuevo Curso</span><button class="modal-close" onclick="closeModal('createCourse')"><i class="fa-solid fa-xmark"></i></button></div>
    <div class="form-group"><label class="form-label">Nombre</label><input class="form-input" id="newCourseName"></div>
    <div class="form-group"><label class="form-label">Descripción</label><textarea class="form-textarea" id="newCourseDesc"></textarea></div>
    <div class="form-group"><label class="form-label">Profesor</label><select class="form-select" id="newCourseTeacher"></select></div>
    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button class="btn btn-ghost" onclick="closeModal('createCourse')">Cancelar</button>
      <button class="btn btn-primary" onclick="createCourse()">Crear</button>
    </div>
  </div>
</div>

<script src="js/app.js"></script>
<script>
function escapeAttr(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}

async function loadAllCourses() {
  const res = await fetch('api/courses.php');
  const courses = await res.json();
  const grid = document.getElementById('allCoursesGrid');

  grid.innerHTML = courses.map(c => `
    <a class="course-card dashboard-course-card" href="admin.php?course_id=${c.id}">
      <div class="course-card-cover-wrap">
        ${
          c.cover_image
            ? `<img src="${escapeAttr(c.cover_image)}" alt="${escapeAttr(c.name)}" class="course-card-cover">`
            : `<div class="course-card-cover-placeholder">
                <i class="fa-solid fa-graduation-cap"></i>
              </div>`
        }
      </div>

      <h3 class="course-card-title">${escapeAttr(c.name)}</h3>
    </a>
  `).join('');
}

async function loadUsers() {
  const res = await fetch('api/users.php');
  const users = await res.json();
  document.getElementById('usersList').innerHTML = users.map(u => `
    <div class="participant-row">
      <span class="p-name">${u.name} (${u.email})</span>
      <select class="p-role-select" onchange="changeUserRole(${u.id}, this.value)">
        <option value="student" ${u.role==='student'?'selected':''}>Estudiante</option>
        <option value="teacher" ${u.role==='teacher'?'selected':''}>Profesor</option>
        <option value="admin" ${u.role==='admin'?'selected':''}>Admin</option>
      </select>
    </div>
  `).join('');
}

async function changeUserRole(id, role) {
  await fetch('api/users.php', {
    method: 'PUT',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({id, role})
  });
}

async function openCreateCourseModal() {
  const res = await fetch('api/users.php?role=teacher');
  const teachers = await res.json();
  document.getElementById('newCourseTeacher').innerHTML = teachers.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
  document.getElementById('newCourseName').value = '';
  document.getElementById('newCourseDesc').value = '';
  openModal('createCourse');
}

async function createCourse() {
  const name = document.getElementById('newCourseName').value.trim();
  const description = document.getElementById('newCourseDesc').value.trim();
  const teacher_id = document.getElementById('newCourseTeacher').value;
  if (!name) return alert('Nombre requerido');
  await fetch('api/courses.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({name, description, teacher_id})
  });
  closeModal('createCourse');
  loadAllCourses();
}

loadAllCourses();
loadUsers();
</script>
<?php endif; ?>
</body>
</html>
