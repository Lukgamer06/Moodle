-- Usuarios de ejemplo (contraseña: password123)
INSERT INTO users (name, email, password_hash, role) VALUES
('Admin Vera', 'admin@minimoodle.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Prof. Herrera', 'profesor@minimoodle.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Lucas Martínez', 'lucas@minimoodle.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('Ana Gómez', 'ana@minimoodle.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('Pedro Ruiz', 'pedro@minimoodle.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student');

-- Curso demo
INSERT INTO courses (id, name, description, teacher_id) VALUES
(1, 'Infraestructura Tecnológica', 'Hardware, redes, servidores, virtualización y componentes de TI moderna.', 2);

-- Matricular estudiantes
INSERT INTO enrollments (user_id, course_id) VALUES (3,1), (4,1), (5,1);

-- Unidades
INSERT INTO units (id, course_id, title, description, icon_class, order_index) VALUES
(1, 1, 'Unidad 1: Hardware', 'Componentes físicos, arquitectura y periféricos', 'hw', 1),
(2, 1, 'Unidad 2: Redes', 'Topologías, protocolos TCP/IP y dispositivos', 'net', 2),
(3, 1, 'Unidad 3: Servidores', 'Instalación, administración y mantenimiento', 'srv', 3),
(4, 1, 'Unidad 4: Virtualización', 'Hipervisores, VMs, contenedores y cloud', 'virt', 4);

-- Recursos
INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(1, 'pdf', 'Introducción a Hardware', 'uploads/sample.pdf', 'PDF · 2.4 MB'),
(1, 'video', 'Arquitectura de Computadoras', '', 'Video · 22 min'),
(2, 'video', 'Redes Básicas — Introducción', '', 'Video · 18 min'),
(2, 'doc', 'Guía de Subnetting', '', 'DOC · 890 KB');

-- Actividades
INSERT INTO activities (unit_id, title, description, due_date) VALUES
(1, 'Identificar componentes', 'Identificar y describir los componentes físicos de un equipo real.', '2025-04-10'),
(2, 'Informe de red', 'Realizar un informe sobre la arquitectura de red de una empresa.', '2025-06-15');

-- Foros
INSERT INTO forums (course_id, title, description, created_by) VALUES
(1, 'Foro General', 'Foro general para presentaciones, avisos y preguntas abiertas.', 2),
(1, 'Consultas Unidad 1', 'Preguntas sobre la Unidad 1 — Hardware.', 2);

-- Mensajes de ejemplo
INSERT INTO forum_messages (forum_id, user_id, content) VALUES
(1, 4, 'Hola a todos, bienvenidos al curso.'),
(1, 5, 'Tengo una duda sobre la unidad 1.'),
(2, 3, '¿Qué entra en la actividad de esta semana?');