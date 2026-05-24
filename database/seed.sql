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

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 1', 'Evaluación del módulo 1: Introducción a la Infraestructura Tecnológica');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions (activity_id, question, option_a, option_b, option_c, option_d, correct_option, order_index) VALUES
(@activity_id, '¿Cuál de las siguientes opciones describe mejor qué es un hipervisor tipo 1?', 'Un software que se ejecuta sobre un sistema operativo anfitrión', 'Un hipervisor que se ejecuta directamente sobre el hardware', 'Un sistema operativo especializado en redes', 'Un gestor de paquetes para Linux', 'b', 1),
(@activity_id, 'VirtualBox es un hipervisor tipo 1.', 'Verdadero', 'Falso', 'No aplica', 'No aplica', 'b', 2),
(@activity_id, '¿Cuál de las siguientes es una ventaja de usar máquinas virtuales?', 'Permiten ejecutar múltiples sistemas en un mismo hardware', 'Aumentan el consumo eléctrico', 'Eliminan la necesidad de sistemas operativos', 'Solo funcionan en Windows', 'a', 3),
(@activity_id, '¿Cuál es el propósito principal de instalar Fedora Server en VirtualBox?', 'Aprender a programar en Python', 'Simular un entorno de servidor real', 'Crear redes inalámbricas', 'Configurar servicios de Windows', 'b', 4),
(@activity_id, '¿Cuál es uno de los pasos esenciales para crear una máquina virtual en VirtualBox?', 'Configurar la memoria RAM y CPU', 'Instalar drivers de impresora', 'Crear un usuario en Linux', 'Configurar un servidor web', 'a', 5);

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

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 2', 'Evaluación del módulo 2: Comandos básicos y sistema de archivos en Linux');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions (activity_id, question, option_a, option_b, option_c, option_d, correct_option, order_index) VALUES
(@activity_id, '¿Qué comando permite cambiar de directorio?', 'ls', 'cd', 'mkdir', 'pwd', 'b', 1),
(@activity_id, '¿Qué comando crea un archivo vacío?', 'touch', 'nano', 'mkdir', 'cat', 'a', 2),
(@activity_id, 'El comando rm -r elimina directorios de forma recursiva.', 'Verdadero', 'Falso', 'No aplica', 'No aplica', 'a', 3),
(@activity_id, '¿Cuál es la diferencia entre apt y dnf?', 'apt es para Debian y dnf para Fedora', 'dnf es para Debian y apt para Fedora', 'Ambos son iguales', 'Ninguno gestiona paquetes', 'a', 4),
(@activity_id, '¿Qué comando crea un directorio llamado proyecto?', 'mkdir proyecto', 'touch proyecto', 'cd proyecto', 'nano proyecto', 'a', 5);

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

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 3', 'Evaluación del módulo 3: Usuarios, grupos y permisos en Linux');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions VALUES
(NULL, @activity_id, '¿Qué comando se utiliza para crear un usuario en Linux?', 'addgroup', 'useradd', 'passwd', 'chmod', 'b', 1),
(NULL, @activity_id, 'chmod 755 archivo da permisos completos al propietario.', 'Verdadero', 'Falso', 'No aplica', 'No aplica', 'a', 2),
(NULL, @activity_id, '¿Qué es un grupo primario?', 'Grupo principal asignado al usuario', 'Grupo temporal', 'Grupo de red', 'Grupo sin permisos', 'a', 3),
(NULL, @activity_id, '¿Qué comando cambia la contraseña de un usuario?', 'passwd', 'chown', 'sudo', 'groupmod', 'a', 4),
(NULL, @activity_id, '¿Qué comando añade un usuario al grupo sudo?', 'usermod -aG sudo usuario', 'groupadd sudo usuario', 'sudoadd usuario', 'adduser sudo usuario', 'a', 5);

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

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 4', 'Evaluación del módulo 4: Empaquetamiento, compresión y scripting');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions VALUES
(NULL, @activity_id, '¿Qué comando crea un archivo tar comprimido?', 'tar -xvf', 'tar -cvf', 'tar -czvf', 'zip -u', 'c', 1),
(NULL, @activity_id, 'El comando zip archivo.zip carpeta/ comprime una carpeta completa.', 'Verdadero', 'Falso', 'No aplica', 'No aplica', 'a', 2),
(NULL, @activity_id, '¿Qué es un script en Bash?', 'Un archivo ejecutable con comandos', 'Un archivo de texto sin comandos', 'Un archivo binario', 'Un archivo de red', 'a', 3),
(NULL, @activity_id, '¿Qué comando hace ejecutable un script?', 'chmod 777', 'chmod +x', 'chmod exec', 'chmod run', 'b', 4),
(NULL, @activity_id, '¿Qué comando muestra la fecha actual en Bash?', 'date', 'time', 'now', 'clock', 'a', 5);

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

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 5', 'Evaluación del módulo 5: Redes y direccionamiento IPv4');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions VALUES
(NULL, @activity_id, '¿Cuál de las siguientes es una dirección IP válida?', '256.10.5.1', '192.168.1.10', '999.0.0.1', '10.300.1.2', 'b', 1),
(NULL, @activity_id, 'La máscara 255.255.255.0 permite 254 hosts utilizables.', 'Verdadero', 'Falso', 'No aplica', 'No aplica', 'a', 2),
(NULL, @activity_id, '¿Qué es una topología de red?', 'La forma en que se organizan los dispositivos', 'Un tipo de cable', 'Un protocolo', 'Un firewall', 'a', 3),
(NULL, @activity_id, '¿Cuál es la función de una máscara de red?', 'Asignar DNS', 'Determinar red y host', 'Aumentar velocidad', 'Crear VLANs', 'b', 4),
(NULL, @activity_id, '¿Cuántos hosts permite una red /27?', '32', '30', '64', '62', 'b', 5);

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

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 6', 'Evaluación del módulo 6: Configuración de routers y Cisco IOS');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions VALUES
(NULL, @activity_id, '¿Qué comando entra al modo privilegiado en Cisco IOS?', 'enable', 'config', 'show run', 'interface', 'a', 1),
(NULL, @activity_id, 'show ip interface brief muestra el estado de las interfaces.', 'Verdadero', 'Falso', 'No aplica', 'No aplica', 'a', 2),
(NULL, @activity_id, '¿Qué es un gateway?', 'El punto de salida de la red', 'Un switch', 'Un firewall', 'Un DNS', 'a', 3),
(NULL, @activity_id, '¿Qué parámetros requiere ip address?', 'IP y máscara', 'IP y DNS', 'IP y gateway', 'IP y hostname', 'a', 4),
(NULL, @activity_id, '¿Qué comando activa una interfaz?', 'no shutdown', 'shutdown', 'activate', 'enable int', 'a', 5);

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

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 7', 'Evaluación del módulo 7: SSH, DNS y conectividad');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions VALUES
(NULL, @activity_id, '¿Qué puerto usa SSH por defecto?', '20', '21', '22', '80', 'c', 1),
(NULL, @activity_id, 'El comando ping verifica conectividad.', 'Verdadero', 'Falso', 'No aplica', 'No aplica', 'a', 2),
(NULL, @activity_id, '¿Qué es un servidor DNS?', 'Un sistema que resuelve nombres de dominio', 'Un firewall', 'Un router', 'Un proxy', 'a', 3),
(NULL, @activity_id, '¿Qué comando permite conectarse por SSH?', 'ssh usuario@ip', 'connect usuario ip', 'login usuario ip', 'ssh ip usuario', 'a', 4),
(NULL, @activity_id, '¿Qué comando verifica resolución DNS?', 'nslookup', 'ping -dns', 'dnscheck', 'hostping', 'a', 5);

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

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 8', 'Evaluación del módulo 8: Apache, Nginx y PHP');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions VALUES
(NULL, @activity_id, '¿Cuál es el archivo principal de configuración de Apache?', 'nginx.conf', 'httpd.conf', 'apache.ini', 'server.conf', 'b', 1),
(NULL, @activity_id, 'Nginx funciona como servidor web y proxy inverso.', 'Verdadero', 'Falso', 'No aplica', 'No aplica', 'a', 2),
(NULL, @activity_id, '¿Qué diferencia clave hay entre Apache y Nginx?', 'Apache usa procesos, Nginx usa eventos', 'Nginx solo funciona en Windows', 'Apache no soporta PHP', 'Ninguno soporta HTTPS', 'a', 3),
(NULL, @activity_id, '¿Qué comando instala Apache en Fedora?', 'sudo dnf install apache', 'sudo dnf install httpd', 'sudo dnf install nginx', 'sudo dnf install web', 'b', 4),
(NULL, @activity_id, '¿Qué archivo define la configuración principal de Nginx?', 'nginx.conf', 'httpd.conf', 'server.ini', 'main.conf', 'a', 5);

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

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 9', 'Evaluación del módulo 9: PostgreSQL y SQL básico');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions (activity_id, question, option_a, option_b, option_c, option_d, correct_option, order_index) VALUES
(@activity_id, '¿Qué comando inicia la consola de PostgreSQL?', 'mysql', 'psql', 'pgadmin', 'sqlstart', 'b', 1),
(@activity_id, 'En PostgreSQL un usuario y un rol pueden ser lo mismo.', 'Verdadero', 'Falso', 'No aplica', 'No aplica', 'a', 2),
(@activity_id, '¿Qué es una tabla?', 'Un conjunto de datos organizados en filas y columnas', 'Un archivo de texto', 'Un script', 'Un índice', 'a', 3),
(@activity_id, '¿Qué comando crea una base de datos?', 'CREATE USER test;', 'CREATE TABLE test;', 'CREATE DATABASE test;', 'NEW DATABASE test;', 'c', 4),
(@activity_id, '¿Qué comando crea un usuario en PostgreSQL?', 'CREATE USER nombre WITH PASSWORD ''123'';', 'ADD USER nombre;', 'NEW USER nombre;', 'useradd nombre;', 'a', 5);

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

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 10', 'Evaluación del módulo 10: SCP, respaldos y automatización');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions (activity_id, question, option_a, option_b, option_c, option_d, correct_option, order_index) VALUES
(@activity_id, '¿Qué comando transfiere archivos con SCP?', 'scp archivo usuario@ip:/ruta', 'copy archivo usuario@ip', 'ssh archivo usuario', 'send archivo ip', 'a', 1),
(@activity_id, 'SCP utiliza SSH para transferir archivos.', 'Verdadero', 'Falso', 'No aplica', 'No aplica', 'a', 2),
(@activity_id, '¿Qué es un respaldo incremental?', 'Copia solo cambios desde el último respaldo', 'Copia todo siempre', 'Copia solo archivos grandes', 'Copia solo archivos nuevos', 'a', 3),
(@activity_id, '¿Qué parámetro comprime en tar.gz?', 'z', 'x', 'c', 'g', 'a', 4),
(@activity_id, '¿Qué herramienta permite programar respaldos diarios?', 'cron', 'backup', 'daily', 'schedule', 'a', 5);

-- ============================================================
-- FOROS DEL CURSO DE INFRAESTRUCTURA
-- ============================================================

INSERT INTO forums (course_id, title, description, created_by) VALUES
(@course_id, 'Foro General', 'Foro general para presentaciones, avisos, dudas y preguntas abiertas del curso.', @teacher_id),
(@course_id, 'Consultas sobre Laboratorios', 'Espacio para resolver dudas relacionadas con las prácticas de Linux, redes, servidores y bases de datos.', @teacher_id),
(@course_id, 'Soporte Técnico del Curso', 'Foro para reportar problemas técnicos relacionados con máquinas virtuales, comandos, servicios o configuración del entorno.', @teacher_id);
