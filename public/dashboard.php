<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: login.html'); exit; }
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Mini Moodle</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="dashboard-page">
<header class="topbar">
    <div class="topbar-left">
        <div class="logo-mark">M</div>
        <span class="app-title">Moodle</span>
    </div>
    <div class="topbar-right" id="topbarRight"></div>
</header>

<main class="page-wrap">
    <div class="card" style="background:linear-gradient(135deg,#1e3a8a,#4f46e5);color:white;border:none;">
        <div class="greeting" style="color:rgba(255,255,255,0.7)">Bienvenido de vuelta,</div>
        <div class="user-name" style="color:white;"><?php echo htmlspecialchars($user['name']); ?></div>
        <div style="margin-top:8px;font-size:13px;opacity:0.8;">Rol: <?php echo htmlspecialchars(ucfirst($user['role'])); ?></div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="section-title">Mis Cursos</h2>
            <?php if ($user['role'] === 'admin'): ?>
            <div>
                <button class="btn btn-accent" onclick="openCreateCourseModal()"><i class="fa-solid fa-plus"></i> Crear Curso</button>
            </div>
            <?php endif; ?>
        </div>
        <div class="course-grid" id="courseGrid"></div>
    </div>
</main>

<!-- Modal crear curso (admin) -->
<?php if ($user['role'] === 'admin'): ?>
<div class="modal-overlay" id="modal-createCourse">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Nuevo Curso</span>
            <button class="modal-close" onclick="closeModal('createCourse')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="form-group">
            <label class="form-label">Nombre del curso</label>
            <input class="form-input" id="newCourseName" placeholder="Ej: Programación Web">
        </div>
        <div class="form-group">
            <label class="form-label">Descripción breve</label>
            <textarea class="form-textarea" id="newCourseDesc" placeholder="Describe el curso..."></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Profesor asignado</label>
            <select class="form-select" id="newCourseTeacher"></select>
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end">
            <button class="btn btn-ghost" onclick="closeModal('createCourse')">Cancelar</button>
            <button class="btn btn-primary" onclick="createCourse()"><i class="fa-solid fa-plus"></i> Crear</button>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="js/app.js"></script>
<script>
const currentUser = <?php echo json_encode($user); ?>;
const roleMap = { student:'Estudiante', teacher:'Profesor', admin:'Administrador' };

// Topbar con dropdown
document.getElementById('topbarRight').innerHTML = `
    <div class="user-dropdown">
        <div class="username">
            <div class="avatar">${currentUser.name.charAt(0).toUpperCase()}</div>
            <span>${currentUser.name.split(' ')[0]}</span>
            <i class="fa-solid fa-caret-down" style="font-size:12px;opacity:.7"></i>
        </div>
        <div class="dropdown-menu" id="dropdownMenu">
            <button onclick="window.location.href='perfil.php'"><i class="fa-solid fa-user"></i> Mi Perfil</button>
            <button onclick="logout()" style="color:var(--red)"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</button>
        </div>
    </div>
`;

function escapeAttr(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

// Cargar cursos
async function loadCourses() {
    const res = await fetch('api/courses.php');
    if (!res.ok) return;

    const courses = await res.json();
    const grid = document.getElementById('courseGrid');

    if (courses.length === 0) {
        grid.innerHTML = `
            <div class="empty-state" style="text-align:center;padding:40px;color:var(--gray-400)">
                <i class="fa-solid fa-book-open" style="font-size:40px"></i>
                <p>No hay cursos</p>
            </div>
        `;
        return;
    }

    grid.innerHTML = courses.map(course => {
        const target = currentUser.role === 'teacher'
            ? 'profesor.php'
            : (currentUser.role === 'admin' ? 'admin.php' : 'estudiante.php');

        return `
            <a class="course-card dashboard-course-card" href="${target}?course_id=${course.id}">
                <div class="course-card-cover-wrap">
                    ${
                        course.cover_image
                            ? `<img src="${escapeAttr(course.cover_image)}" alt="${escapeAttr(course.name)}" class="course-card-cover">`
                            : `<div class="course-card-cover-placeholder">
                                <i class="fa-solid fa-graduation-cap"></i>
                              </div>`
                    }
                </div>

                <h3 class="course-card-title">${escapeAttr(course.name)}</h3>
            </a>
        `;
    }).join('');
}

// Modal crear curso (admin)
async function openCreateCourseModal() {
    const res = await fetch('api/users.php?role=teacher');
    const teachers = await res.json();
    const select = document.getElementById('newCourseTeacher');
    select.innerHTML = teachers.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
    document.getElementById('newCourseName').value = '';
    document.getElementById('newCourseDesc').value = '';
    openModal('createCourse');
}

async function createCourse() {
    const name = document.getElementById('newCourseName').value.trim();
    const description = document.getElementById('newCourseDesc').value.trim();
    const teacher_id = document.getElementById('newCourseTeacher').value;
    if (!name) return alert('Nombre obligatorio');
    const res = await fetch('api/courses.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({name, description, teacher_id})
    });
    if (res.ok) {
        closeModal('createCourse');
        loadCourses();
    } else {
        alert('Error al crear curso');
    }
}

function logout() {
    fetch('api/logout.php').then(() => {
        window.location.href = 'login.html';
    });
}

// Iniciar
loadCourses();
</script>
</body>
</html>