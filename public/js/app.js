// ── NAVEGACIÓN ──
function showScreen(name) {
  document.querySelectorAll('.screen').forEach(s => s.classList.remove('active'));
  const screen = document.getElementById('screen-' + name);
  if (screen) screen.classList.add('active');
  document.querySelectorAll('.nav-btn').forEach((b, i) => {
    const screens = ['course', 'participants', 'grades'];
    b.classList.toggle('active', screens[i] === name);
  });
  if (name === 'participants' && typeof loadParticipants === 'function') loadParticipants();
  if (name === 'grades' && typeof loadGrades === 'function') loadGrades();
}

// ── ACORDEÓN DE UNIDADES ──
function toggleUnit(btn) {
  const body = btn.nextElementSibling;
  const isOpen = body.style.display === 'block';
  document.querySelectorAll('.unit-acc-body').forEach(b => b.style.display = 'none');
  document.querySelectorAll('.unit-acc-header').forEach(h => h.classList.remove('open'));
  if (!isOpen) {
    body.style.display = 'block';
    btn.classList.add('open');
  }
}

// ── MODALES ──
function openModal(id) {
  const modal = document.getElementById('modal-' + id);
  if (modal) modal.classList.add('show');
}
function closeModal(id) {
  const modal = document.getElementById('modal-' + id);
  if (modal) modal.classList.remove('show');
}
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('show');
  }
});

// ── DROPDOWN DE USUARIO ──
function setupUserDropdown() {
  const userDropdown = document.querySelector('.user-dropdown');
  const dropdownMenu = document.getElementById('dropdownMenu');
  if (userDropdown && dropdownMenu) {
    userDropdown.addEventListener('click', (e) => {
      e.stopPropagation();
      dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
    });
    document.addEventListener('click', () => {
      dropdownMenu.style.display = 'none';
    });
  }
}

// ── FOROS (DRAWER) ──
function setupForumDrawer(openBtnId, closeBtnId, drawerId, overlayId) {
  const openBtn = document.getElementById(openBtnId);
  const closeBtn = document.getElementById(closeBtnId);
  const drawer = document.getElementById(drawerId);
  const overlay = document.getElementById(overlayId);
  if (openBtn) openBtn.onclick = () => { drawer.classList.add('open'); overlay.classList.add('show'); };
  if (closeBtn) closeBtn.onclick = () => { drawer.classList.remove('open'); overlay.classList.remove('show'); };
  if (overlay) overlay.onclick = () => { drawer.classList.remove('open'); overlay.classList.remove('show'); };
}

// Gestión de temas (topics) - 3 niveles: Foros → Temas → Mensajes
let currentForum = null;
let currentTopic = null;

function toggleForum(btn) {
  const body = btn.nextElementSibling;
  const isOpen = body.style.display === 'block';
  document.querySelectorAll('.forum-body').forEach(b => b.style.display = 'none');
  document.querySelectorAll('.forum-header').forEach(h => h.classList.remove('open'));
  if (!isOpen) {
    const forumId = body.dataset.forumId;
    body.style.display = 'block';
    btn.classList.add('open');
    currentForum = forumId;
    currentTopic = null;
    loadTopics(forumId);
  }
}

async function loadTopics(forumId) {
  const container = document.querySelector(`.forum-body[data-forum-id="${forumId}"] .topics-list`);
  if (!container) return;
  
  try {
    const res = await fetch(`api/topics.php?forum_id=${forumId}`);
    if (!res.ok) throw new Error('Error cargando temas');
    const topics = await res.json();
    
    if (topics.length === 0) {
      container.innerHTML = '<p style="font-size:12px;color:var(--gray-400);text-align:center;padding:12px;">Sin temas aún</p>';
      return;
    }
    
    container.innerHTML = topics.map(t => `
      <div class="topic-item" onclick="selectTopic(this, ${t.id}, ${forumId})">
        <div class="topic-title">${escapeHtml(t.title)}</div>
        <div class="topic-meta">por ${t.created_by_name} • ${new Date(t.created_at).toLocaleDateString('es-ES')}</div>
      </div>
    `).join('');
  } catch (err) {
    console.error(err);
    container.innerHTML = '<p style="font-size:12px;color:var(--red)">Error cargando temas</p>';
  }
}

async function selectTopic(el, topicId, forumId) {
  document.querySelectorAll('.topic-item').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  currentTopic = topicId;
  
  const body = document.querySelector(`.forum-body[data-forum-id="${forumId}"]`);
  if (!body) return;
  
  loadTopicMessages(topicId, body);
}

async function loadTopicMessages(topicId, container) {
  const messagesContainer = container.querySelector('.messages-container');
  if (!messagesContainer) return;
  
  try {
    const res = await fetch(`api/messages.php?topic_id=${topicId}`);
    if (!res.ok) throw new Error('Error cargando mensajes');
    const messages = await res.json();
    
    messagesContainer.innerHTML = messages.map(m => `
      <div class="message">
        <p class="msg-user">${escapeHtml(m.user_name)}</p>
        <p class="msg-text">${escapeHtml(m.content)}</p>
      </div>
    `).join('');
    
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
  } catch (err) {
    console.error(err);
    messagesContainer.innerHTML = '<p style="font-size:12px;color:var(--red)">Error cargando mensajes</p>';
  }
}

async function sendMsg(btn) {
  if (!currentTopic) {
    alert('Selecciona un tema primero');
    return;
  }
  
  const ta = btn.previousElementSibling;
  const content = ta.value.trim();
  if (!content) return;
  
  try {
    const res = await fetch('api/messages.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ forum_id: currentForum, topic_id: currentTopic, content })
    });
    
    if (!res.ok) throw new Error('Error enviando mensaje');
    
    ta.value = '';
    const body = btn.closest('.forum-body');
    loadTopicMessages(currentTopic, body);
  } catch (err) {
    console.error(err);
    alert('Error al enviar mensaje');
  }
}

function showNewForumTopicModal() {
  if (!currentForum) {
    alert('Selecciona un foro primero');
    return;
  }
  openModal('newForumTopic');
}

async function createForumTopic() {
  if (!currentForum) return;
  
  const title = document.getElementById('newForumTopicTitle').value.trim();
  const description = document.getElementById('newForumTopicDesc').value.trim();
  
  if (!title) {
    alert('El título es requerido');
    return;
  }
  
  try {
    const res = await fetch('api/topics.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ forum_id: currentForum, title, description })
    });
    
    if (!res.ok) throw new Error('Error creando tema');
    
    document.getElementById('newForumTopicTitle').value = '';
    document.getElementById('newForumTopicDesc').value = '';
    closeModal('newForumTopic');
    loadTopics(currentForum);
  } catch (err) {
    console.error(err);
    alert('Error al crear tema');
  }
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}


// ── CERRAR SESIÓN ──
function logout() {
  fetch('api/logout.php').then(() => {
    window.location.href = 'login.html';
  });
}

// ── PERFIL ──
function startEdit() {
  document.getElementById('viewMode').style.display = 'none';
  document.getElementById('editMode').style.display = 'grid';
  document.getElementById('editBtn').style.display = 'none';
  document.getElementById('saveBtn').style.display = 'inline-flex';
  document.getElementById('cancelBtn').style.display = 'inline-flex';
}
function cancelEdit() {
  document.getElementById('viewMode').style.display = 'grid';
  document.getElementById('editMode').style.display = 'none';
  document.getElementById('editBtn').style.display = 'inline-flex';
  document.getElementById('saveBtn').style.display = 'none';
  document.getElementById('cancelBtn').style.display = 'none';
}
async function saveProfile() {
  const name = document.getElementById('inp-name').value.trim();
  const email = document.getElementById('inp-email').value.trim();
  if (!name) { showToast('El nombre no puede estar vacío'); return; }
  const res = await fetch('api/users.php', {
    method: 'PUT',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({name, email})
  });
  if (res.ok) {
    const data = await res.json();
    document.getElementById('view-name').textContent = name;
    document.getElementById('view-email').textContent = email;
    document.getElementById('displayName').textContent = name;
    const initials = name.split(' ').map(w=>w[0]).slice(0,2).join('');
    document.getElementById('profileAvatar').textContent = initials;
    document.getElementById('topAvatar').textContent = name[0].toUpperCase();
    document.getElementById('topName').textContent = name.split(' ')[0];
    cancelEdit();
    showToast('Perfil actualizado correctamente.');
  } else {
    const err = await res.json();
    showToast(err.error || 'Error al actualizar');
  }
}
async function changePwd() {
  const cur = document.getElementById('pwd-current').value;
  const nw = document.getElementById('pwd-new').value;
  const cf = document.getElementById('pwd-confirm').value;
  if (!cur || !nw || !cf) { showToast('Completa todos los campos'); return; }
  if (nw !== cf) { showToast('Las contraseñas no coinciden'); return; }
  const res = await fetch('api/users.php', {
    method: 'PUT',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({password: nw, current_password: cur})
  });
  if (res.ok) {
    document.getElementById('pwd-current').value = '';
    document.getElementById('pwd-new').value = '';
    document.getElementById('pwd-confirm').value = '';
    showToast('Contraseña actualizada.');
  } else {
    const err = await res.json();
    showToast(err.error || 'Error al cambiar contraseña');
  }
}
function showToast(msg) {
  const t = document.getElementById('toast');
  if (!t) return;
  document.getElementById('toastMsg').textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3000);
}

// Inicializar al cargar
document.addEventListener('DOMContentLoaded', () => {
  setupUserDropdown();
  setupForumDrawer('openForumBtn', 'closeForumBtn', 'forumDrawer', 'overlay');
});