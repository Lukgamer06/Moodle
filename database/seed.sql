SET NAMES utf8mb4;

-- ============================================================
-- USUARIOS DE EJEMPLO
-- Contraseña para todos: password
-- ============================================================

INSERT INTO users (name, email, nrc, password_hash, role) VALUES
('Admin Vera', 'admin@minimoodle.local', 'U00010000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Prof. Herrera', 'profesor@minimoodle.local', 'U00010001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Lucas Martínez', 'lucas@minimoodle.local', 'U00010002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('Ana Gómez', 'ana@minimoodle.local', 'U00010003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('Pedro Ruiz', 'pedro@minimoodle.local', 'U00010004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student')
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  nrc = VALUES(nrc),
  role = VALUES(role);

-- ============================================================
-- CURSO: INFRAESTRUCTURA TECNOLÓGICA
-- Este bloque NO modifica backend ni frontend.
-- Solo limpia cursos anteriores con el mismo nombre y vuelve a cargar datos semilla.
-- ============================================================

SET @course_name := 'Infraestructura Tecnológica';

-- Limpiar datos anteriores del mismo curso para evitar duplicados.
-- Se hace en orden para respetar llaves foráneas.
DELETE g FROM grades g
JOIN submissions s ON g.submission_id = s.id
JOIN activities a ON s.activity_id = a.id
JOIN units u ON a.unit_id = u.id
JOIN courses c ON u.course_id = c.id
WHERE c.name = @course_name;

DELETE s FROM submissions s
JOIN activities a ON s.activity_id = a.id
JOIN units u ON a.unit_id = u.id
JOIN courses c ON u.course_id = c.id
WHERE c.name = @course_name;

DELETE q FROM quiz_questions q
JOIN activities a ON q.activity_id = a.id
JOIN units u ON a.unit_id = u.id
JOIN courses c ON u.course_id = c.id
WHERE c.name = @course_name;

DELETE a FROM activities a
JOIN units u ON a.unit_id = u.id
JOIN courses c ON u.course_id = c.id
WHERE c.name = @course_name;

DELETE r FROM resources r
JOIN units u ON r.unit_id = u.id
JOIN courses c ON u.course_id = c.id
WHERE c.name = @course_name;

DELETE m FROM forum_messages m
JOIN forums f ON m.forum_id = f.id
JOIN courses c ON f.course_id = c.id
WHERE c.name = @course_name;

DELETE f FROM forums f
JOIN courses c ON f.course_id = c.id
WHERE c.name = @course_name;

DELETE e FROM enrollments e
JOIN courses c ON e.course_id = c.id
WHERE c.name = @course_name;

DELETE u FROM units u
JOIN courses c ON u.course_id = c.id
WHERE c.name = @course_name;

DELETE FROM courses
WHERE name = @course_name;

-- Profesor del curso
SET @teacher_id := (
  SELECT id
  FROM users
  WHERE email = 'profesor@minimoodle.local'
  LIMIT 1
);

-- Crear curso
INSERT INTO courses (name, description, teacher_id, card_color)
VALUES (
  @course_name,
  '<h2>Bienvenida</h2>
  <p>Bienvenidos al curso de Infraestructura Tecnológica. En este espacio aprenderemos los fundamentos necesarios para comprender cómo funcionan los servicios, redes y servidores que soportan las soluciones tecnológicas actuales. A lo largo del curso trabajaremos tanto conceptos teóricos como actividades prácticas, permitiendo que cada estudiante pueda fortalecer sus habilidades en administración de sistemas, virtualización, redes y servicios web.</p>
  <h2>Propósito</h2>
  <p>El propósito de este curso es que los estudiantes logren comprender y aplicar los conceptos básicos de infraestructura tecnológica mediante prácticas orientadas a la configuración de sistemas Linux, administración de redes, virtualización, servicios web y bases de datos. Asimismo, se busca fortalecer la capacidad de resolver problemas técnicos, implementar servicios y entender la comunicación entre dispositivos dentro de un entorno informático moderno.</p>',
  @teacher_id,
  '#C7D1FF'
);

SET @course_id := LAST_INSERT_ID();

-- Matricular estudiantes existentes
INSERT INTO enrollments (user_id, course_id)
SELECT id, @course_id
FROM users
WHERE email IN (
  'lucas@minimoodle.local',
  'ana@minimoodle.local',
  'pedro@minimoodle.local'
);

-- ============================================================
-- UNIDAD 1
-- ============================================================
INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 1 — Introducción a la Infraestructura Tecnológica',
  'En este módulo se abordarán los conceptos fundamentales relacionados con la infraestructura tecnológica y los entornos informáticos. Además, se estudiará qué es la virtualización, los tipos de hipervisores y la importancia de las máquinas virtuales dentro de los entornos modernos de TI. También se realizará la instalación y configuración inicial de Fedora Server utilizando VirtualBox.',
  'virt',
  1,
  '#C7D1FF'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'video', 'Instalación de VirtualBox y configuración de máquinas virtuales', 'https://www.youtube.com/watch?v=rJ9_hREHMPA', '14 minutos'),
(@unit_id, 'pdf', 'Manual oficial de VirtualBox', 'https://www.virtualbox.org/manual/UserManual.html', 'Documento completo');

-- ============================================================
-- UNIDAD 2
-- ============================================================
INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 2 — Sistema de Archivos y Comandos Básicos en Linux',
  'En este módulo los estudiantes aprenderán a interactuar con el sistema operativo Linux mediante comandos esenciales. Asimismo, se trabajará la navegación entre directorios, creación y administración de archivos, instalación de paquetes y uso de editores de texto como Nano.',
  'virt',
  2,
  '#C7D1FF'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'video', 'Comandos básicos de Linux para principiantes', 'https://www.youtube.com/watch?v=L906Kti3gzE', '35 minutos'),
(@unit_id, 'pdf', 'Linux Command Line Guide', 'https://linuxcommand.org/tlcl.php', 'Libro completo');

-- ============================================================
-- UNIDAD 3
-- ============================================================
INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 3 — Administración de Usuarios, Grupos y Permisos',
  'Durante este módulo se estudiará la administración de usuarios y grupos dentro de Linux. Además, se realizarán prácticas relacionadas con creación de usuarios, asignación de contraseñas, modificación de permisos y control de acceso a archivos y directorios.',
  'virt',
  3,
  '#C7D1FF'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'video', 'Gestión de usuarios y permisos en Linux', 'https://www.youtube.com/watch?v=QDTdK9gQJH4', '16:46 minutos'),
(@unit_id, 'pdf', 'Linux Permissions Guide', 'https://www.redhat.com/en/blog/linux-file-permissions-explained', 'Lectura corta');

-- ============================================================
-- UNIDAD 4
-- ============================================================
INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 4 — Empaquetamiento, Compresión y Scripts en Linux',
  'En este módulo se aprenderá a empaquetar, comprimir y descomprimir archivos utilizando herramientas como tar y zip. De igual manera, los estudiantes crearán scripts básicos en Bash para automatizar tareas dentro del sistema operativo.',
  'virt',
  4,
  '#C7D1FF'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'video', 'Bash scripting para principiantes', 'https://www.youtube.com/watch?v=0tIZhTAuNuU', '5:25 minutos'),
(@unit_id, 'doc', 'Manual de comandos TAR y ZIP', 'https://www.gnu.org/software/tar/manual/tar.html', 'Documento técnico');

-- ============================================================
-- UNIDAD 5
-- ============================================================
INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 5 — Fundamentos de Redes y Direccionamiento IP',
  'En este módulo se estudiarán los conceptos básicos de redes de computadores, tipos de redes, topologías y medios de comunicación. Asimismo, se trabajará el direccionamiento IPv4, máscaras de red y conectividad entre dispositivos.',
  'virt',
  5,
  '#C7D1FF'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'video', 'Fundamentos de redes e IPv4', 'https://www.youtube.com/watch?v=SHbBso63X38', '17:41 minutos'),
(@unit_id, 'doc', 'Introducción a Redes Cisco', 'https://www.cisco.com/c/es_mx/solutions/small-business/resource-center/networking/networking-basics.html', 'Archivo introductorio');

-- ============================================================
-- UNIDAD 6
-- ============================================================
INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 6 — Configuración de Dispositivos de Red',
  'En este módulo los estudiantes configurarán routers y enlaces de red utilizando comandos básicos de Cisco IOS. Además, aprenderán a configurar interfaces, gateways y configuraciones iniciales en dispositivos de red.',
  'virt',
  6,
  '#C7D1FF'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'video', 'Configuración básica de routers Cisco', 'https://www.youtube.com/watch?v=EmyjMG4nN_4', '8:53 minutos'),
(@unit_id, 'doc', 'Cisco IOS Fundamentals', 'https://www.cisco.com/c/en/us/td/docs/ios-xml/ios/fundamentals/configuration/xe-16/fundamentals-xe-16-book.html', 'Documento técnico');

-- ============================================================
-- UNIDAD 7
-- ============================================================
INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 7 — Servicios de Red y Conectividad',
  'En este módulo se trabajará la configuración y validación de servicios de conectividad como SSH y DNS. Asimismo, se estudiará el uso de herramientas para comprobar comunicación entre equipos y solucionar problemas básicos de red.',
  'virt',
  7,
  '#C7D1FF'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'video', 'Configuración de SSH en Linux', 'https://www.youtube.com/watch?v=6OxMXOYznNk', '4:42 minutos'),
(@unit_id, 'doc', 'DNS y conectividad en Linux', 'https://www.redhat.com/en/blog/dns-domain-name-servers', 'Lectura técnica');

-- ============================================================
-- UNIDAD 8
-- ============================================================
INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 8 — Servidores Web con Apache y Nginx',
  'Durante este módulo se instalarán y configurarán servidores web Apache y Nginx. Además, los estudiantes aprenderán a desplegar páginas web utilizando PHP y comprenderán las diferencias entre ambos servicios.',
  'virt',
  8,
  '#C7D1FF'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'video', 'Instalación de Apache y PHP en Linux', 'https://www.youtube.com/watch?v=1j3MKZ9NlVY', '6:09 minutos'),
(@unit_id, 'doc', 'Introducción a Nginx', 'https://nginx.org/en/docs/', 'Documentación completa');

-- ============================================================
-- UNIDAD 9
-- ============================================================
INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 9 — Bases de Datos con PostgreSQL',
  'En este módulo se abordará la instalación y configuración de PostgreSQL. Asimismo, se trabajará la creación de bases de datos, usuarios y tablas, además de consultas SQL y conexión con PHP.',
  'virt',
  9,
  '#C7D1FF'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'video', 'PostgreSQL para principiantes', 'https://www.youtube.com/watch?v=prbF4O0d-7M', '15:16 minutos'),
(@unit_id, 'doc', 'Documentación oficial PostgreSQL', 'https://www.postgresql.org/docs/', 'Documentación completa');

-- ============================================================
-- UNIDAD 10
-- ============================================================
INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 10 — Transferencia de Archivos y Respaldos',
  'En este módulo se realizarán prácticas de transferencia de archivos entre sistemas Windows y Linux utilizando SCP. Además, se trabajará la creación de respaldos, compresión de proyectos y automatización de copias de seguridad mediante scripts.',
  'virt',
  10,
  '#C7D1FF'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'video', 'Uso de SCP en Linux', 'https://www.youtube.com/watch?v=Oyf4dUq-LXs', '3:53 minutos'),
(@unit_id, 'video', 'Backups automáticos con Bash', 'https://www.youtube.com/watch?v=8ga0xhZuG6k', '23:38 minutos');

-- ============================================================
-- FOROS DEL CURSO DE INFRAESTRUCTURA
-- ============================================================

INSERT INTO forums (course_id, title, description, created_by) VALUES
(@course_id, 'Foro General', 'Foro general para presentaciones, avisos, dudas y preguntas abiertas del curso.', @teacher_id),
(@course_id, 'Consultas sobre Laboratorios', 'Espacio para resolver dudas relacionadas con las prácticas de Linux, redes, servidores y bases de datos.', @teacher_id),
(@course_id, 'Soporte Técnico del Curso', 'Foro para reportar problemas técnicos relacionados con máquinas virtuales, comandos, servicios o configuración del entorno.', @teacher_id);
