SET NAMES utf8mb4;

-- Usuarios de ejemplo (contraseña: password)
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
(1, 'pdf', 'Introducción a Hardware', 'uploads/infra-hardware-intro.pdf', 'PDF · Guía base'),
(1, 'video', 'Arquitectura de Computadoras', 'https://www.youtube.com/watch?v=AkFi90lZmXA', 'Video · Arquitectura'),
(1, 'doc', 'Checklist de Componentes', '', 'Documento · Práctica'),
(2, 'video', 'Redes Básicas - Introducción', 'https://www.youtube.com/watch?v=3QhU9jd03a0', 'Video · Redes'),
(2, 'pdf', 'Guía de Subnetting', 'uploads/infra-subnetting.pdf', 'PDF · Ejercicios'),
(3, 'pdf', 'Administración de Servidores', 'uploads/infra-servidores.pdf', 'PDF · Laboratorio'),
(3, 'video', 'Servidores Linux para Principiantes', 'https://www.youtube.com/watch?v=ROjZy1WbCIA', 'Video · Linux'),
(4, 'pdf', 'Virtualización y Contenedores', 'uploads/infra-virtualizacion.pdf', 'PDF · Resumen'),
(4, 'video', 'Introducción a Docker', 'https://www.youtube.com/watch?v=CV_Uf3Dq-EU', 'Video · Contenedores');

-- Actividades
INSERT INTO activities (id, unit_id, activity_type, title, description, due_date) VALUES
(1, 1, 'activity', 'Identificar componentes', 'Identificar y describir los componentes físicos de un equipo real.', '2026-06-10'),
(2, 2, 'activity', 'Informe de red', 'Realizar un informe sobre la arquitectura de red de una empresa.', '2026-06-15'),
(3, 1, 'quiz', 'Quiz de Hardware', 'Selecciona la respuesta correcta para cada concepto de hardware.', '2026-06-12'),
(4, 4, 'evaluation', 'Evaluación de Virtualización', 'Evaluación corta sobre máquinas virtuales, hipervisores y contenedores.', '2026-06-20');

INSERT INTO quiz_questions (activity_id, question, option_a, option_b, option_c, option_d, correct_option, order_index) VALUES
(3, '¿Qué componente ejecuta instrucciones principales del sistema?', 'CPU', 'Fuente de poder', 'Disco duro', 'Monitor', 'a', 1),
(3, '¿Qué memoria pierde su contenido al apagar el equipo?', 'SSD', 'RAM', 'ROM', 'Blu-ray', 'b', 2),
(4, '¿Qué software permite ejecutar varias máquinas virtuales sobre un host?', 'Compilador', 'Hipervisor', 'Firewall', 'Balanceador', 'b', 1),
(4, '¿Qué tecnología empaqueta aplicaciones con sus dependencias?', 'RAID', 'VLAN', 'Contenedores', 'BIOS', 'c', 2);

-- Foros
INSERT INTO forums (course_id, title, description, created_by) VALUES
(1, 'Foro General', 'Foro general para presentaciones, avisos y preguntas abiertas.', 2),
(1, 'Consultas Unidad 1', 'Preguntas sobre la Unidad 1 — Hardware.', 2);

-- Mensajes de ejemplo
INSERT INTO forum_messages (forum_id, user_id, content) VALUES
(1, 4, 'Hola a todos, bienvenidos al curso.'),
(1, 5, 'Tengo una duda sobre la unidad 1.'),
(2, 3, '¿Qué entra en la actividad de esta semana?');
