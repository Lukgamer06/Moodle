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
    <style>
        :root {
            --blue: #1e3a8a; --blue-mid: #2d52b8; --blue-light: #dbeafe;
            --accent: #6366f1; --accent-light: #e0e7ff;
            --green: #059669; --green-light: #d1fae5;
            --amber: #d97706; --amber-light: #fef3c7;
            --red: #dc2626; --red-light: #fee2e2;
            --purple: #7c3aed; --purple-light: #ede9fe;
            --gray-50: #f8fafc; --gray-100: #f1f5f9; --gray-200: #e2e8f0;
            --gray-300: #cbd5e1; --gray-400: #94a3b8; --gray-500: #64748b;
            --gray-700: #334155; --gray-900: #0f172a;
            --radius: 12px; --radius-lg: 18px;
            --shadow: 0 1px 3px rgba(0,0,0,0.08), 0 4px 16px rgba(0,0,0,0.06);
            --shadow-hover: 0 4px 12px rgba(0,0,0,0.1), 0 8px 24px rgba(0,0,0,0.08);
        }
        /* Estilos idénticos a los anteriores del dashboard... (se omiten por brevedad, se incluyen completos) */
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'DM Sans',sans-serif; background:var(--gray-100); color:var(--gray-900); min-height:100vh; }
        .topbar { width:100%; height:64px; background:var(--blue); display:flex; align-items:center; justify-content:space-between; padding:0 28px; position:fixed; top:0; z-index:1000; box-shadow:0 2px 12px rgba(0,0,0,0.2); }
        .topbar-left { display:flex; align-items:center; gap:14px; }
        .logo-mark { width:38px; height:38px; border-radius:10px; background:rgba(255,255,255,0.15); display:flex; align-items:center; justify-content:center; font-family:'DM Serif Display',serif; color:white; font-size:20px; }
        .app-title { font-size:17px; font-weight:600; color:white; }
        .topbar-right { display:flex; align-items:center; gap:16px; }
        .user-info { display:flex; align-items:center; gap:10px; color:white; font-size:14px; }
        .avatar { width:34px; height:34px; border-radius:50%; background:var(--accent); display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:600; color:white; }
        .role-badge { font-size:11px; font-weight:600; padding:3px 10px; border-radius:20px; background:rgba(255,255,255,0.2); color:white; }
        .btn-logout { background:rgba(255,255,255,0.12); border:none; color:white; padding:6px 14px; border-radius:8px; cursor:pointer; font-size:13px; font-family:'DM Sans',sans-serif; transition:0.2s; display:flex; align-items:center; gap:6px; }
        .btn-logout:hover { background:rgba(255,255,255,0.25); }
        .page-wrap { padding-top:104px; padding-bottom:60px; max-width:940px; margin:0 auto; padding-left:20px; padding-right:20px; }
        .card { background:white; border-radius:var(--radius-lg); box-shadow:var(--shadow); padding:28px; margin-bottom:20px; border:1px solid var(--gray-200); }
        .card-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
        .section-title { font-family:'DM Serif Display',serif; font-size:22px; color:var(--gray-900); }
        .greeting { font-size:16px; color:var(--gray-500); margin-bottom:4px; }
        .user-name { font-family:'DM Serif Display',serif; font-size:26px; color:var(--gray-900); }
        .course-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:16px; margin-top:8px; }
        .course-card { border:1px solid var(--gray-200); border-radius:var(--radius); padding:20px; background:var(--gray-50); cursor:pointer; transition:0.2s; text-decoration:none; color:inherit; display:block; }
        .course-card:hover { border-color:var(--accent); background:var(--accent-light); transform:translateY(-2px); box-shadow:var(--shadow-hover); }
        .course-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; margin-bottom:12px; }
        .course-icon.generic { background:var(--gray-200); color:var(--gray-600); }
        .course-name { font-size:16px; font-weight:600; color:var(--gray-900); margin-bottom:4px; }
        .course-desc { font-size:13px; color:var(--gray-500); line-height:1.4; }
        .course-teacher { font-size:12px; color:var(--gray-500); margin-top:8px; display:flex; align-items:center; gap:4px; }
        .btn { padding:9px 16px; border-radius:10px; border:none; font-family:'DM Sans',sans-serif; font-size:14px; font-weight:500; cursor:pointer; transition:all 0.2s; display:inline-flex; align-items:center; gap:7px; }
        .btn-primary { background:var(--blue); color:white; }
        .btn-primary:hover { background:var(--blue-mid); transform:translateY(-1px); }
        .btn-accent  { background:var(--accent); color:white; }
        .btn-accent:hover { background:#4f46e5; transform:translateY(-1px); }
        .btn-ghost   { background:var(--gray-100); color:var(--gray-700); border:1px solid var(--gray-200); }
        .btn-ghost:hover { background:var(--gray-200); }
        .modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:2000; display:none; align-items:center; justify-content:center; }
        .modal-overlay.show { display:flex; }
        .modal { background:white; border-radius:var(--radius-lg); padding:28px; width:90%; max-width:500px; box-shadow:0 20px 60px rgba(0,0,0,0.2); }
        .modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .modal-title { font-family:'DM Serif Display',serif; font-size:20px; }
        .modal-close { background:none; border:none; font-size:20px; cursor:pointer; color:var(--gray-500); }
        .form-group { margin-bottom:16px; }
        .form-label { font-size:13px; font-weight:500; color:var(--gray-700); margin-bottom:6px; display:block; }
        .form-input,.form-textarea,.form-select { width:100%; padding:10px 14px; border-radius:10px; border:1px solid var(--gray-300); font-family:'DM Sans',sans-serif; font-size:14px; background:var(--gray-50); }
        .form-input:focus,.form-textarea:focus,.form-select:focus { outline:none; border-color:var(--accent); background:white; box-shadow:0 0 0 3px rgba(99,102,241,0.1); }
        .form-textarea { resize:vertical; min-height:80px; }
    </style>
</head>
<body>
<header class="topbar">
    <div class="topbar-left">
        <div class="logo-mark">M</div>
        <span class="app-title">Mini Moodle</span>
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
            <div id="adminActions" style="display:<?php echo $user['role']==='admin'?'block':'none'; ?>">
                <button class="btn btn-accent" onclick="openCreateCourseModal()"><i class="fa-solid fa-plus"></i> Crear Curso</button>
            </div>
        </div>
        <div class="course-grid" id="courseGrid"></div>
    </div>
</main>

<!-- Modal crear curso (admin) -->
<div class="modal-overlay" id="modalCreateCourse">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Nuevo Curso</span>
            <button class="modal-close" onclick="closeModal('modalCreateCourse')"><i class="fa-solid fa-xmark"></i></button>
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
            <button class="btn btn-ghost" onclick="closeModal('modalCreateCourse')">Cancelar</button>
            <button class="btn btn-primary" onclick="createCourse()"><i class="fa-solid fa-plus"></i> Crear</button>
        </div>
    </div>
</div>

<script>
const currentUser = <?php echo json_encode($user); ?>;
const roleMap = { student:'Estudiante', teacher:'Profesor', admin:'Administrador' };

// Topbar
document.getElementById('topbarRight').innerHTML = `
    <div class="user-info">
        <div class="avatar">${currentUser.name.charAt(0).toUpperCase()}</div>
        <span>${currentUser.name.split(' ')[0]}</span>
        <span class="role-badge">${roleMap[currentUser.role]}</span>
    </div>
    <button class="btn-logout" onclick="logout()"><i class="fa-solid fa-right-from-bracket"></i> Salir</button>
`;

// Cargar cursos
async function loadCourses() {
    const res = await fetch('api/courses.php');
    if (!res.ok) return;
    const courses = await res.json();
    const grid = document.getElementById('courseGrid');
    if (courses.length === 0) {
        grid.innerHTML = '<div class="empty-state" style="text-align:center;padding:40px;color:var(--gray-400)"><i class="fa-solid fa-book-open" style="font-size:40px"></i><p>No hay cursos</p></div>';
        return;
    }
    grid.innerHTML = courses.map(course => {
        const target = currentUser.role === 'teacher' ? 'profesor.php' : (currentUser.role === 'admin' ? 'admin.php' : 'estudiante.php');
        return `<a class="course-card" href="${target}?course_id=${course.id}">
            <div class="course-icon generic"><i class="fa-solid fa-graduation-cap"></i></div>
            <div class="course-name">${course.name}</div>
            <div class="course-desc">${course.description || ''}</div>
            <div class="course-teacher"><i class="fa-solid fa-user-tie"></i> ${course.teacher_name || 'Sin profesor'}</div>
        </a>`;
    }).join('');
}

// Modal crear curso
async function openCreateCourseModal() {
    // Cargar profesores
    const res = await fetch('api/courses.php?teachers=1'); // endpoint que devuelva profesores (o usar users.php)
    const teachers = await res.json();
    const select = document.getElementById('newCourseTeacher');
    select.innerHTML = teachers.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
    document.getElementById('modalCreateCourse').classList.add('show');
}
function closeModal(id) { document.getElementById(id).classList.remove('show'); }

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
        closeModal('modalCreateCourse');
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