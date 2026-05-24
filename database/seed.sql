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
INSERT INTO courses (name, description, teacher_id, card_color, cover_image)
VALUES (
  @course_name,
  '<h2 style="text-align:center;">Bienvenida</h2>

  <p>Bienvenidos al curso de Infraestructura Tecnológica. En este espacio aprenderemos los fundamentos necesarios para comprender cómo funcionan los servicios, redes y servidores que soportan las soluciones tecnológicas actuales. A lo largo del curso trabajaremos tanto conceptos teóricos como actividades prácticas, permitiendo que cada estudiante pueda fortalecer sus habilidades en administración de sistemas, virtualización, redes y servicios web. Además, se utilizarán herramientas reales del entorno profesional, facilitando así una experiencia cercana a lo que se vive en el mundo laboral de TI.</p>

  <h2 style="text-align:center;">Propósito</h2>

  <p>El propósito de este curso es que los estudiantes logren comprender y aplicar los conceptos básicos de infraestructura tecnológica mediante prácticas orientadas a la configuración de sistemas Linux, administración de redes, virtualización, servicios web y bases de datos. Asimismo, se busca fortalecer la capacidad de resolver problemas técnicos, implementar servicios y entender la comunicación entre dispositivos dentro de un entorno informático moderno.</p>

  <img 
    src="https://i.pinimg.com/736x/98/d5/49/98d549f3ff77bc01218df66bd3914eb1.jpg" 
    alt="Imagen Infraestructura Tecnológica" 
    class="course-img course-img-medium course-img-center"
  >',
  @teacher_id,
  '#C7D1FF',
  'assets/course-covers/infraestructura.png'
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

-- ============================================================
-- CURSO: DESARROLLO Y ARQUITECTURA BACKEND
-- Este bloque NO modifica backend ni frontend.
-- Solo carga datos semilla del curso Backend.
-- ============================================================

SET @course_name := 'Desarrollo y Arquitectura Backend';

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
INSERT INTO courses (name, description, teacher_id, card_color, cover_image)
VALUES (
  @course_name,
  '<h2 style="text-align:center;">Bienvenida del Curso</h2>

  <p>Bienvenidos al curso de Desarrollo y Arquitectura Backend. En este espacio aprenderemos los fundamentos necesarios para crear aplicaciones web modernas utilizando Laravel y diferentes herramientas del ecosistema backend. A lo largo del curso trabajaremos conceptos relacionados con rutas, controladores, migraciones, bases de datos, APIs REST, validaciones, arquitectura MVC y despliegue de proyectos. Además, se desarrollarán actividades prácticas que permitirán fortalecer habilidades tanto en programación como en diseño de soluciones tecnológicas reales.</p>

  <h2 style="text-align:center;">Propósito del Curso</h2>

  <p>El propósito de este curso es que los estudiantes comprendan y apliquen los principios básicos del desarrollo backend mediante la construcción de aplicaciones funcionales utilizando Laravel. Asimismo, se busca fortalecer las habilidades en manejo de bases de datos, creación de APIs, arquitectura MVC, validaciones, autenticación y consumo de servicios, permitiendo que los estudiantes desarrollen proyectos escalables y cercanos al entorno profesional del desarrollo web.</p>

  <img 
    src="https://i.pinimg.com/736x/d8/a0/4a/d8a04ab868395674b7694f49d2336adc.jpg" 
    alt="Imagen Desarrollo Backend" 
    class="course-img course-img-medium course-img-center"
  >',
  @teacher_id,
  '#C2FFCB',
  'assets/course-covers/backend.png'
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
  'Módulo 1 — Introducción al Desarrollo Backend y Laravel',
  'En este módulo se estudiarán los conceptos básicos del desarrollo backend y el funcionamiento de Laravel como framework. Además, se aprenderá cómo configurar un proyecto desde cero, instalar dependencias y ejecutar el servidor de desarrollo.',
  'net',
  1,
  '#C2FFCB'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'video', 'Introducción a Laravel desde cero', 'https://www.youtube.com/watch?v=ImtZ5yENzgE', '4 horas y 25 minutos'),
(@unit_id, 'doc', 'Documentación oficial de Laravel', 'https://laravel.com/docs/13.x', 'Documento completo');

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 1', 'Evaluación del módulo 1: Introducción al desarrollo backend y Laravel');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions VALUES
(NULL, @activity_id, '¿Qué es Laravel?', 'Un framework de PHP', 'Un sistema operativo', 'Un servidor web', 'Un motor de base de datos', 'a', 1),
(NULL, @activity_id, '¿Qué comando crea un nuevo proyecto Laravel?', 'laravel new proyecto', 'php new proyecto', 'composer create proyecto', 'php artisan new', 'a', 2),
(NULL, @activity_id, '¿Qué archivo define las dependencias del proyecto?', 'composer.json', 'package.json', '.env', 'config.php', 'a', 3),
(NULL, @activity_id, '¿Qué comando inicia el servidor de desarrollo?', 'php artisan serve', 'npm start', 'php start', 'laravel run', 'a', 4),
(NULL, @activity_id, '¿Qué arquitectura utiliza Laravel?', 'MVC', 'MVVM', 'Clean Architecture', 'Hexagonal', 'a', 5);

-- ============================================================
-- UNIDAD 2
-- ============================================================

INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 2 — Configuración de Entorno y Gestión de Proyectos',
  'En este módulo los estudiantes aprenderán a configurar proyectos Laravel utilizando XAMPP, Composer y Git. Asimismo, se trabajará la conexión con bases de datos mediante el archivo .env y la ejecución de migraciones.',
  'net',
  2,
  '#C2FFCB'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'video', 'Configuración de Laravel con XAMPP', 'https://www.youtube.com/watch?v=_Rsen6614Dg', '6:23 minutos'),
(@unit_id, 'doc', 'Guía de instalación de Composer', 'https://getcomposer.org/doc/', 'Documento técnico');

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 2', 'Evaluación del módulo 2: Configuración de entorno y gestión de proyectos');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions VALUES
(NULL, @activity_id, '¿Qué herramienta gestiona dependencias en Laravel?', 'Composer', 'NPM', 'Git', 'Docker', 'a', 1),
(NULL, @activity_id, '¿Qué archivo contiene la configuración de la base de datos?', '.env', 'config.php', 'database.json', 'settings.ini', 'a', 2),
(NULL, @activity_id, '¿Qué comando ejecuta migraciones?', 'php artisan migrate', 'php artisan db', 'composer migrate', 'php migrate', 'a', 3),
(NULL, @activity_id, '¿Qué herramienta permite clonar repositorios?', 'Git', 'Composer', 'XAMPP', 'PHP', 'a', 4),
(NULL, @activity_id, '¿Qué servicio provee MySQL en XAMPP?', 'MariaDB', 'PostgreSQL', 'SQLite', 'OracleDB', 'a', 5);

-- ============================================================
-- UNIDAD 3
-- ============================================================

INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 3 — Arquitectura MVC en Laravel',
  'Durante este módulo se estudiará el funcionamiento de la arquitectura Modelo Vista Controlador dentro de Laravel. Además, se aprenderá cómo interactúan las rutas, controladores, modelos y vistas dentro de una aplicación web.',
  'net',
  3,
  '#C2FFCB'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'video', 'Arquitectura MVC explicada en Laravel', 'https://www.youtube.com/watch?v=tza73mpt2EM', '14:05 minutos'),
(@unit_id, 'doc', 'Conceptos MVC en Laravel', 'https://laravel.com/docs/13.x/structure', 'Lectura técnica');

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 3', 'Evaluación del módulo 3: Arquitectura MVC en Laravel');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions VALUES
(NULL, @activity_id, '¿Qué representa la M en MVC?', 'Modelo', 'Middleware', 'Module', 'Manager', 'a', 1),
(NULL, @activity_id, '¿Qué archivo define las rutas web?', 'routes/web.php', 'app/routes.php', 'config/routes.php', 'public/routes.php', 'a', 2),
(NULL, @activity_id, '¿Qué comando crea un controlador?', 'php artisan make:controller', 'php artisan controller:create', 'composer make controller', 'php make controller', 'a', 3),
(NULL, @activity_id, '¿Qué archivo contiene las vistas?', 'resources/views', 'app/views', 'public/views', 'config/views', 'a', 4),
(NULL, @activity_id, '¿Qué clase interactúa con la base de datos?', 'Modelo', 'Controlador', 'Vista', 'Middleware', 'a', 5);

-- ============================================================
-- UNIDAD 4
-- ============================================================

INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 4 — Migraciones y Bases de Datos',
  'En este módulo se aprenderá a crear tablas, atributos y relaciones utilizando migraciones en Laravel. Asimismo, se trabajará el manejo de llaves foráneas y conexión entre entidades dentro de bases de datos relacionales.',
  'net',
  4,
  '#C2FFCB'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'video', 'Migraciones en Laravel paso a paso', 'https://www.youtube.com/watch?v=R8B4og-BeCk', '18:53 minutos'),
(@unit_id, 'doc', 'Documentación oficial de migraciones', 'https://laravel.com/docs/migrations', 'Documento completo');

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 4', 'Evaluación del módulo 4: Migraciones y bases de datos');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions VALUES
(NULL, @activity_id, '¿Qué comando crea una migración?', 'php artisan make:migration', 'php artisan migration:create', 'composer make migration', 'php migration new', 'a', 1),
(NULL, @activity_id, '¿Qué método crea una columna en una tabla?', 'Schema::create', 'DB::column', 'Table::add', 'Model::column', 'a', 2),
(NULL, @activity_id, '¿Qué comando revierte la última migración?', 'php artisan migrate:rollback', 'php artisan migrate:undo', 'php artisan rollback', 'php artisan db:rollback', 'a', 3),
(NULL, @activity_id, '¿Qué tipo de relación representa belongsTo?', 'Uno a muchos (inverso)', 'Muchos a muchos', 'Uno a uno', 'Muchos a uno', 'a', 4),
(NULL, @activity_id, '¿Qué archivo configura la conexión a la base de datos?', '.env', 'database.php', 'config.json', 'settings.ini', 'a', 5);

-- ============================================================
-- UNIDAD 5
-- ============================================================

INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 5 — Modelos, Seeders y Factories',
  'En este módulo se estudiará cómo crear modelos, seeders y factories en Laravel. Además, los estudiantes aprenderán a generar datos de prueba y poblar automáticamente las bases de datos para facilitar el desarrollo y las pruebas.',
  'net',
  5,
  '#C2FFCB'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'video', 'Seeders en Laravel', 'https://www.youtube.com/watch?v=zNTF3U2Hsq0', '12:16 minutos'),
(@unit_id, 'doc', 'Eloquent ORM Documentation', 'https://laravel.com/docs/eloquent', 'Documento completo');

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 5', 'Evaluación del módulo 5: Modelos, seeders y factories');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions VALUES
(NULL, @activity_id, '¿Qué comando crea un modelo?', 'php artisan make:model', 'php artisan model:create', 'composer make model', 'php model new', 'a', 1),
(NULL, @activity_id, '¿Qué comando ejecuta los seeders?', 'php artisan db:seed', 'php artisan seed', 'composer seed', 'php seed', 'a', 2),
(NULL, @activity_id, '¿Qué comando crea un factory?', 'php artisan make:factory', 'php artisan factory:create', 'composer make factory', 'php factory new', 'a', 3),
(NULL, @activity_id, '¿Qué clase genera datos falsos?', 'Faker', 'Seeder', 'Factory', 'Model', 'a', 4),
(NULL, @activity_id, '¿Qué método crea múltiples registros con un factory?', 'factory()->count()', 'factory()->many()', 'factory()->repeat()', 'factory()->loop()', 'a', 5);

-- ============================================================
-- UNIDAD 6
-- ============================================================

INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 6 — Formularios y Validaciones Seguras',
  'En este módulo se trabajará la creación de formularios dinámicos y validaciones seguras en Laravel. Asimismo, se estudiará el uso de reglas de validación, mensajes de error y protección mediante tokens CSRF.',
  'net',
  6,
  '#C2FFCB'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'video', 'Validaciones en Laravel', 'https://www.youtube.com/watch?v=hafioSprmGs', '25:33 minutos'),
(@unit_id, 'doc', 'Validaciones oficiales Laravel', 'https://laravel.com/docs/validation', 'Documento técnico');

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 6', 'Evaluación del módulo 6: Formularios y validaciones seguras');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions VALUES
(NULL, @activity_id, '¿Qué token protege formularios en Laravel?', 'CSRF', 'JWT', 'API Key', 'OAuth', 'a', 1),
(NULL, @activity_id, '¿Qué método valida datos en un controlador?', '$request->validate()', 'Validator::run()', 'Form::validate()', 'Input::check()', 'a', 2),
(NULL, @activity_id, '¿Qué regla valida correos?', 'email', 'mail', 'is_email', 'validate_email', 'a', 3),
(NULL, @activity_id, '¿Qué regla obliga un campo a ser obligatorio?', 'required', 'needed', 'must', 'force', 'a', 4),
(NULL, @activity_id, '¿Qué archivo contiene mensajes de validación?', 'lang/es/validation.php', 'config/validation.php', 'resources/validation.php', 'app/validation.php', 'a', 5);

-- ============================================================
-- UNIDAD 7
-- ============================================================

INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 7 — Gestión de Archivos e Imágenes',
  'Durante este módulo se aprenderá a subir imágenes y archivos desde formularios utilizando Laravel. Además, se trabajará el almacenamiento de archivos en el sistema y la configuración de enlaces públicos mediante storage.',
  'net',
  7,
  '#C2FFCB'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'video', 'Subida de imágenes en Laravel', 'https://www.youtube.com/watch?v=SiKPB69lJX0', '5:11 minutos'),
(@unit_id, 'doc', 'File Storage Laravel', 'https://laravel.com/docs/filesystem', 'Documento completo');

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 7', 'Evaluación del módulo 7: Gestión de archivos e imágenes');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions VALUES
(NULL, @activity_id, '¿Qué método guarda archivos en Laravel?', 'store()', 'saveFile()', 'upload()', 'put()', 'a', 1),
(NULL, @activity_id, '¿Qué carpeta almacena archivos públicos?', 'storage/app/public', 'public/files', 'app/public', 'resources/files', 'a', 2),
(NULL, @activity_id, '¿Qué comando crea un enlace simbólico para storage?', 'php artisan storage:link', 'php artisan link:storage', 'php artisan make:storage', 'php artisan storage:create', 'a', 3),
(NULL, @activity_id, '¿Qué método obtiene la ruta pública?', 'asset()', 'path()', 'url()', 'public()', 'a', 4),
(NULL, @activity_id, '¿Qué driver usa Laravel por defecto para storage?', 'local', 's3', 'ftp', 'gcs', 'a', 5);

-- ============================================================
-- UNIDAD 8
-- ============================================================

INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 8 — CRUD Completo en Laravel',
  'En este módulo se desarrollarán operaciones CRUD completas para aplicaciones web. Asimismo, se implementarán funcionalidades de creación, consulta, edición y eliminación de registros utilizando Laravel.',
  'net',
  8,
  '#C2FFCB'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'video', 'CRUD completo en Laravel', 'https://www.youtube.com/watch?v=GrUrw245L48', '1 hora 15 minutos'),
(@unit_id, 'doc', 'Laravel Resource Controllers', 'https://laravel.com/docs/controllers', 'Documento técnico');

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 8', 'Evaluación del módulo 8: CRUD completo en Laravel');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions VALUES
(NULL, @activity_id, '¿Qué método muestra un formulario de creación?', 'create()', 'store()', 'edit()', 'index()', 'a', 1),
(NULL, @activity_id, '¿Qué método guarda un registro?', 'store()', 'save()', 'insert()', 'add()', 'a', 2),
(NULL, @activity_id, '¿Qué método actualiza un registro?', 'update()', 'modify()', 'change()', 'edit()', 'a', 3),
(NULL, @activity_id, '¿Qué método elimina un registro?', 'destroy()', 'delete()', 'remove()', 'drop()', 'a', 4),
(NULL, @activity_id, '¿Qué comando crea un controlador resource?', 'php artisan make:controller NombreController --resource', 'php artisan controller:resource', 'composer make resource', 'php artisan make:resource', 'a', 5);

-- ============================================================
-- UNIDAD 9
-- ============================================================

INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 9 — APIs REST y Microservicios',
  'En este módulo se estudiará la creación de APIs REST utilizando Laravel. Además, se aprenderá cómo funcionan los microservicios, el intercambio de información mediante JSON y el consumo de endpoints desde clientes externos.',
  'net',
  9,
  '#C2FFCB'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'video', 'Crear APIs REST en Laravel', 'https://www.youtube.com/watch?v=YGqCZjdgJJk', '1 hora y 50 minutos'),
(@unit_id, 'doc', 'APIs y JSON en Laravel', 'https://laravel.com/docs/sanctum', 'Documento completo');

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 9', 'Evaluación del módulo 9: APIs REST y microservicios');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions VALUES
(NULL, @activity_id, '¿Qué formato usan las APIs REST?', 'JSON', 'XML', 'HTML', 'TXT', 'a', 1),
(NULL, @activity_id, '¿Qué comando crea un controlador API?', 'php artisan make:controller NombreController --api', 'php artisan api:controller', 'composer make api', 'php artisan make:api', 'a', 2),
(NULL, @activity_id, '¿Qué método HTTP obtiene datos?', 'GET', 'POST', 'PUT', 'DELETE', 'a', 3),
(NULL, @activity_id, '¿Qué paquete maneja tokens en Laravel?', 'Sanctum', 'JWT', 'OAuth', 'Passport', 'a', 4),
(NULL, @activity_id, '¿Qué es un microservicio?', 'Un servicio pequeño e independiente', 'Un servidor físico', 'Un archivo JSON', 'Un controlador', 'a', 5);

-- ============================================================
-- UNIDAD 10
-- ============================================================

INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 10 — Diseño Responsive y Despliegue de Proyectos',
  'En este módulo se trabajará el diseño responsive para aplicaciones web modernas. Asimismo, se estudiarán conceptos básicos de despliegue, integración de tecnologías frontend y publicación de proyectos en entornos reales.',
  'net',
  10,
  '#C2FFCB'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'video', 'Diseño responsive con Flexbox y Grid', 'https://www.youtube.com/watch?v=3YW65K6LcIA', '46 minutos'),
(@unit_id, 'doc', 'Responsive Web Design', 'https://developer.mozilla.org/es/docs/Learn_web_development/Core/CSS_layout/Responsive_Design', 'Lectura completa');

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 10', 'Evaluación del módulo 10: Diseño responsive y despliegue');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions VALUES
(NULL, @activity_id, '¿Qué tecnología permite diseño responsive?', 'CSS Flexbox y Grid', 'PHP', 'MySQL', 'Blade', 'a', 1),
(NULL, @activity_id, '¿Qué comando genera build de frontend?', 'npm run build', 'php artisan build', 'composer build', 'npm build', 'a', 2),
(NULL, @activity_id, '¿Qué servicio permite desplegar proyectos?', 'Vercel', 'MySQL', 'Composer', 'Git', 'a', 3),
(NULL, @activity_id, '¿Qué archivo define dependencias frontend?', 'package.json', 'composer.json', 'webpack.mix.js', 'vite.config.js', 'a', 4),
(NULL, @activity_id, '¿Qué concepto permite adaptar interfaces a pantallas?', 'Responsive Design', 'API Design', 'MVC', 'ORM', 'a', 5);

-- ============================================================
-- FOROS DEL CURSO BACKEND
-- ============================================================

INSERT INTO forums (course_id, title, description, created_by) VALUES
(@course_id, 'Foro General Backend', 'Foro general para presentaciones, avisos, dudas y preguntas abiertas del curso de Desarrollo y Arquitectura Backend.', @teacher_id),
(@course_id, 'Consultas sobre Laravel y Bases de Datos', 'Espacio para resolver dudas relacionadas con rutas, controladores, migraciones, modelos, seeders, bases de datos y validaciones.', @teacher_id),
(@course_id, 'Soporte Técnico del Curso Backend', 'Foro para reportar problemas técnicos relacionados con instalación, configuración del entorno, errores de Laravel, Composer, XAMPP o despliegue.', @teacher_id);


-- ============================================================
-- CURSO: INTELIGENCIA ARTIFICIAL APLICADA AL ANÁLISIS DE DATOS
-- Este bloque NO modifica backend ni frontend.
-- Solo carga datos semilla del curso de IA.
-- ============================================================

SET @course_name := 'Inteligencia Artificial aplicada al analisis de datos';

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
INSERT INTO courses (name, description, teacher_id, card_color, cover_image)
VALUES (
  @course_name,
  '<h2 style="text-align:center;">Bienvenida del curso</h2>

  <p>Bienvenido al curso virtual de Inteligencia Artificial aplicada al análisis de datos. En este espacio aprenderás, paso a paso, cómo preparar datos, analizarlos, limpiarlos y usarlos para construir modelos de inteligencia artificial. La idea no es memorizar fórmulas ni comandos sin sentido, sino comprender qué está pasando con los datos y cómo tomar mejores decisiones antes, durante y después de entrenar un modelo.</p>

  <p>A lo largo del curso trabajarás con ejemplos prácticos en Python, especialmente usando herramientas como pandas, scikit-learn, Google Colab y Streamlit. Cada módulo está pensado para que avances de forma progresiva: primero entenderás los datos, luego los transformarás, después entrenarás modelos y finalmente aprenderás a presentar tus resultados en una aplicación sencilla.</p>

  <h2 style="text-align:center;">Propósito del curso</h2>

  <p>El propósito de este curso es que el estudiante desarrolle las habilidades necesarias para preparar, analizar, transformar y modelar datos mediante técnicas básicas y aplicadas de inteligencia artificial, comprendiendo cuándo usar modelos supervisados, no supervisados, de clasificación, regresión o ensamble.</p>

  <p>Al finalizar el curso, el estudiante estará en capacidad de reconocer variables, etiquetas, datos categóricos y numéricos; realizar análisis exploratorio; limpiar valores faltantes; codificar datos; seleccionar características importantes; entrenar modelos como KNN, SVM, árboles de decisión, Random Forest y XGBoost; evaluar resultados mediante métricas; y construir una aplicación básica para mostrar predicciones.</p>

  <img 
    src="https://i.pinimg.com/736x/4a/14/0b/4a140b5c2d311f593b2a86933d2adedf.jpg" 
    alt="Imagen Inteligencia Artificial aplicada al análisis de datos" 
    class="course-img course-img-medium course-img-center"
  >',
  @teacher_id,
  '#FEE3FF',
  'assets/course-covers/ia-datos.png'
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
  'Módulo 1 — Fundamentos de inteligencia artificial y aprendizaje automático',
  'En este primer módulo el estudiante conocerá las ideas principales que dan inicio al trabajo con inteligencia artificial. Se abordará la diferencia entre modelos supervisados y no supervisados, el papel de las variables de entrada o features, la importancia de la etiqueta o label, y la diferencia entre problemas de clasificación y regresión. También se revisará cómo identificar si un problema busca predecir una categoría, un valor numérico o encontrar grupos dentro de los datos.',
  'hw',
  1,
  '#C7D1FF'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'doc', 'Introducción al aprendizaje automático con scikit-learn', 'https://scikit-learn.org/stable/supervised_learning.html', 'Documento web de consulta'),
(@unit_id, 'doc', 'Machine Learning Crash Course', 'https://developers.google.com/machine-learning/crash-course', 'Curso web organizado por módulos');

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 1', 'Evaluación del módulo 1: Fundamentos de IA y aprendizaje automático');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions VALUES
(NULL, @activity_id, '¿Qué es un modelo supervisado?', 'Un modelo con etiquetas conocidas', 'Un modelo sin etiquetas', 'Un modelo que no usa datos', 'Un modelo que solo usa texto', 'a', 1),
(NULL, @activity_id, '¿Qué es un label?', 'La variable que se desea predecir', 'Una columna irrelevante', 'Un identificador único', 'Un valor faltante', 'a', 2),
(NULL, @activity_id, '¿Qué tipo de problema predice categorías?', 'Clasificación', 'Regresión', 'Clustering', 'Reducción de dimensionalidad', 'a', 3),
(NULL, @activity_id, '¿Qué tipo de modelo busca grupos sin etiqueta?', 'No supervisado', 'Supervisado', 'Regresión', 'Clasificación', 'a', 4),
(NULL, @activity_id, '¿Qué representan los features?', 'Variables de entrada', 'La etiqueta', 'Los valores faltantes', 'Los datos atípicos', 'a', 5);


-- ============================================================
-- UNIDAD 2
-- ============================================================

INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 2 — Análisis exploratorio de datos: hacer que los datos hablen',
  'En este módulo el estudiante aprenderá a revisar un conjunto de datos antes de aplicar cualquier modelo. Se trabajarán conceptos como media, mediana, moda, mínimo, máximo, distribución de datos, tipos de variables y revisión general del dataset con comandos como describe(), info(), columns, unique() y value_counts(). También se explicará cómo usar gráficos como histogramas, barras y cajas de bigotes para reconocer patrones, detectar posibles errores y comprender si los datos están listos para ser usados en inteligencia artificial.',
  'hw',
  2,
  '#FEE3FF'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'doc', 'Primeros pasos con pandas', 'https://pandas.pydata.org/docs/getting_started/index.html', 'Documento web de consulta'),
(@unit_id, 'doc', 'Tutoriales introductorios de pandas', 'https://pandas.pydata.org/docs/getting_started/intro_tutorials/', 'Serie de tutoriales cortos');

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 2', 'Evaluación del módulo 2: Análisis exploratorio de datos');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions VALUES
(NULL, @activity_id, '¿Qué comando muestra estadísticas básicas?', 'describe()', 'info()', 'unique()', 'columns', 'a', 1),
(NULL, @activity_id, '¿Qué gráfico ayuda a ver la distribución?', 'Histograma', 'Mapa de calor', 'Diagrama de red', 'Gráfico de dispersión 3D', 'a', 2),
(NULL, @activity_id, '¿Qué comando muestra tipos de datos?', 'info()', 'describe()', 'value_counts()', 'plot()', 'a', 3),
(NULL, @activity_id, '¿Qué medida representa el valor central?', 'Mediana', 'Máximo', 'Mínimo', 'Rango', 'a', 4),
(NULL, @activity_id, '¿Qué gráfico detecta outliers fácilmente?', 'Caja de bigotes', 'Barras', 'Líneas', 'Pastel', 'a', 5);

-- ============================================================
-- UNIDAD 3
-- ============================================================

INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 3 — Limpieza de datos, valores faltantes y datos atípicos',
  'En este módulo el estudiante aprenderá que un modelo de inteligencia artificial depende mucho de la calidad de los datos que recibe. Se estudiará cómo identificar valores nulos, cuándo eliminar columnas o registros, cómo usar reglas como el porcentaje de datos faltantes, y cómo aplicar métodos de imputación con media, moda, ffill, bfill o herramientas como SimpleImputer. Además, se explicará cómo reconocer valores atípicos mediante mínimos, máximos y cajas de bigotes, tomando decisiones cuidadosas antes de eliminar o transformar información.',
  'hw',
  3,
  '#FEE3FF'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'doc', 'Preprocesamiento de datos en scikit-learn', 'https://scikit-learn.org/stable/modules/preprocessing.html', 'Documento web de consulta'),
(@unit_id, 'doc', 'Guía de usuario de pandas', 'https://pandas.pydata.org/docs/user_guide/index.html', 'Documento web amplio de referencia');

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 3', 'Evaluación del módulo 3: Limpieza de datos y valores faltantes');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions VALUES
(NULL, @activity_id, '¿Qué método rellena valores con la media?', 'SimpleImputer(strategy="mean")', 'fillna("mode")', 'dropna()', 'replace()', 'a', 1),
(NULL, @activity_id, '¿Qué comando elimina filas con nulos?', 'dropna()', 'fillna()', 'replace()', 'remove()', 'a', 2),
(NULL, @activity_id, '¿Qué es un valor atípico?', 'Un valor extremadamente alejado del resto', 'Un valor faltante', 'Un valor duplicado', 'Un valor categórico', 'a', 3),
(NULL, @activity_id, '¿Qué método copia el valor anterior?', 'ffill', 'bfill', 'mean', 'mode', 'a', 4),
(NULL, @activity_id, '¿Qué gráfico ayuda a detectar outliers?', 'Boxplot', 'Pie chart', 'Heatmap', 'Barplot', 'a', 5);

-- ============================================================
-- UNIDAD 4
-- ============================================================

INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 4 — Codificación de variables categóricas y construcción de vistas minables',
  'En este módulo el estudiante aprenderá a transformar datos de texto o categorías en datos numéricos que puedan ser entendidos por los modelos. Se abordarán técnicas como LabelEncoder, OrdinalEncoder, codificación one-hot, variables dummy y codificación por frecuencia. También se explicará la diferencia entre variables nominales, ordinales y binarias, y por qué algunos datos como cédulas, identificadores o nombres no deben tratarse como números estadísticos. Al finalizar, el estudiante comprenderá qué significa construir una vista minable: una versión limpia y preparada del dataset para modelar.',
  'hw',
  4,
  '#FEE3FF'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'doc', 'Codificación y preprocesamiento en scikit-learn', 'https://scikit-learn.org/stable/modules/preprocessing.html', 'Documento web de consulta'),
(@unit_id, 'doc', '10 minutos con pandas', 'https://pandas.pydata.org/docs/user_guide/10min.html', 'Guía corta de inicio');

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 4', 'Evaluación del módulo 4: Codificación de variables categóricas');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions VALUES
(NULL, @activity_id, '¿Qué técnica convierte categorías en columnas binarias?', 'One-hot encoding', 'LabelEncoder', 'OrdinalEncoder', 'Frequency encoding', 'a', 1),
(NULL, @activity_id, '¿Qué técnica asigna números según orden?', 'OrdinalEncoder', 'One-hot', 'Dummy encoding', 'Hashing', 'a', 2),
(NULL, @activity_id, '¿Qué tipo de variable no tiene orden?', 'Nominal', 'Ordinal', 'Binaria', 'Numérica', 'a', 3),
(NULL, @activity_id, '¿Qué método usa pandas para dummies?', 'get_dummies()', 'dummy()', 'encode()', 'categorize()', 'a', 4),
(NULL, @activity_id, '¿Qué dato NO debe tratarse como número?', 'Cédula', 'Edad', 'Ingresos', 'Temperatura', 'a', 5);

-- ============================================================
-- UNIDAD 5
-- ============================================================

INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 5 — Normalización, escalamiento y generación de características',
  'En este módulo el estudiante comprenderá por qué no basta con tener datos limpios: también es necesario que estén en escalas adecuadas. Se trabajarán técnicas como MinMaxScaler y StandardScaler, especialmente cuando las variables tienen rangos muy diferentes. Además, se explicará cómo generar nuevas características a partir de datos existentes, por ejemplo transformar fechas, calcular edades, crear variables derivadas o reducir columnas combinando información útil.',
  'hw',
  5,
  '#FEE3FF'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'doc', 'StandardScaler en scikit-learn', 'https://scikit-learn.org/stable/modules/generated/sklearn.preprocessing.StandardScaler.html', 'Documento técnico de consulta'),
(@unit_id, 'doc', 'Preprocesamiento de datos', 'https://scikit-learn.org/stable/modules/preprocessing_targets.html#', 'Documento web de consulta');

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 5', 'Evaluación del módulo 5: Normalización y escalamiento');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions VALUES
(NULL, @activity_id, '¿Qué técnica escala entre 0 y 1?', 'MinMaxScaler', 'StandardScaler', 'Normalizer', 'RobustScaler', 'a', 1),
(NULL, @activity_id, '¿Qué técnica centra en media 0 y varianza 1?', 'StandardScaler', 'MinMaxScaler', 'Normalizer', 'LogScaler', 'a', 2),
(NULL, @activity_id, '¿Qué es una característica derivada?', 'Una columna creada a partir de otra', 'Un valor faltante', 'Un outlier', 'Una etiqueta', 'a', 3),
(NULL, @activity_id, '¿Qué método transforma fechas en variables útiles?', 'Feature engineering', 'Encoding', 'Scaling', 'Cleaning', 'a', 4),
(NULL, @activity_id, '¿Qué problema resuelve el escalamiento?', 'Diferencias grandes entre rangos', 'Valores duplicados', 'Datos categóricos', 'Errores de tipeo', 'a', 5);

-- ============================================================
-- UNIDAD 6
-- ============================================================

INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 6 — Selección de características y reducción de variables',
  'En este módulo el estudiante aprenderá a elegir las variables más útiles para un modelo. Se abordará el concepto de varianza, la eliminación de características con poca variación, el uso de VarianceThreshold, SelectKBest, Chi-cuadrado y f_classif. También se explicará por qué no siempre tener muchas columnas significa tener un mejor modelo. La intención es que el estudiante aprenda a quedarse con la información más relevante, evitando ruido, columnas repetidas o variables que no aportan valor a la predicción.',
  'hw',
  6,
  '#FEE3FF'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'doc', 'Selección de características en scikit-learn', 'https://scikit-learn.org/stable/modules/feature_selection.html', 'Documento web de consulta'),
(@unit_id, 'doc', 'SelectKBest', 'https://scikit-learn.org/stable/modules/generated/sklearn.feature_selection.SelectKBest.html', 'Documento técnico de referencia');

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 6', 'Evaluación del módulo 6: Selección de características');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions VALUES
(NULL, @activity_id, '¿Qué hace VarianceThreshold?', 'Elimina columnas con baja varianza', 'Escala datos', 'Detecta outliers', 'Codifica categorías', 'a', 1),
(NULL, @activity_id, '¿Qué técnica selecciona las mejores k variables?', 'SelectKBest', 'PCA', 'MinMaxScaler', 'LabelEncoder', 'a', 2),
(NULL, @activity_id, '¿Qué prueba usa SelectKBest para clasificación?', 'Chi-cuadrado', 'ANOVA', 'Pearson', 'Spearman', 'a', 3),
(NULL, @activity_id, '¿Por qué eliminar columnas irrelevantes?', 'Para reducir ruido', 'Para aumentar columnas', 'Para duplicar datos', 'Para evitar escalamiento', 'a', 4),
(NULL, @activity_id, '¿Qué significa alta dimensionalidad?', 'Demasiadas columnas', 'Demasiados registros', 'Muchos nulos', 'Muchos outliers', 'a', 5);

-- ============================================================
-- UNIDAD 7
-- ============================================================

INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 7 — Modelos supervisados: clasificación y regresión',
  'En este módulo el estudiante empezará a construir modelos supervisados. Se estudiará el proceso general: importar librerías, declarar el modelo, entrenarlo con fit, predecir con predict y validar resultados. También se explicará la diferencia entre clasificación y regresión, usando ejemplos sencillos como predecir si una persona compra o no compra, si un paciente tiene o no una condición, o si se desea estimar un valor numérico. El estudiante aprenderá a separar variables de entrada y etiqueta, y a dividir datos en entrenamiento y prueba.',
  'hw',
  7,
  '#FEE3FF'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'doc', 'Aprendizaje supervisado en scikit-learn', 'https://scikit-learn.org/stable/supervised_learning.html', 'Documento web de consulta'),
(@unit_id, 'doc', 'Clasificación en Machine Learning Crash Course', 'https://developers.google.com/machine-learning/crash-course/classification', 'Módulo web de aprendizaje');

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 7', 'Evaluación del módulo 7: Modelos supervisados');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions VALUES
(NULL, @activity_id, '¿Qué método entrena un modelo?', 'fit()', 'train()', 'learn()', 'model()', 'a', 1),
(NULL, @activity_id, '¿Qué método genera predicciones?', 'predict()', 'forecast()', 'estimate()', 'output()', 'a', 2),
(NULL, @activity_id, '¿Qué tipo de problema predice valores numéricos?', 'Regresión', 'Clasificación', 'Clustering', 'Balanceo', 'a', 3),
(NULL, @activity_id, '¿Qué se divide en train y test?', 'El dataset', 'Los modelos', 'Los gráficos', 'Los labels', 'a', 4),
(NULL, @activity_id, '¿Qué representa X en IA?', 'Variables de entrada', 'La etiqueta', 'Los outliers', 'Los valores faltantes', 'a', 5);

-- ============================================================
-- UNIDAD 8
-- ============================================================

INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 8 — Modelos de clasificación: KNN, SVM, Naive Bayes y árboles de decisión',
  'En este módulo el estudiante conocerá varios modelos de clasificación utilizados en inteligencia artificial. Se trabajarán modelos como KNN, SVM, Naive Bayes y árboles de decisión, comprendiendo de manera sencilla cuándo puede ser útil cada uno. Se explicará que KNN trabaja con cercanía entre datos, SVM busca separar grupos mediante fronteras de decisión, Naive Bayes usa probabilidades, y los árboles toman decisiones por ramas. También se abordarán ideas como kernel, hiperparámetros, profundidad del árbol y riesgo de sobreentrenamiento.',
  'hw',
  8,
  '#FEE3FF'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'doc', 'Mapa para escoger estimadores en scikit-learn', 'https://scikit-learn.org/stable/machine_learning_map.html', 'Recurso visual interactivo'),
(@unit_id, 'doc', 'Guía de usuario de scikit-learn', 'https://scikit-learn.org/stable/user_guide.html', 'Documento web completo de referencia');

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 8', 'Evaluación del módulo 8: Modelos de clasificación');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions VALUES
(NULL, @activity_id, '¿Qué modelo usa vecinos cercanos?', 'KNN', 'SVM', 'Naive Bayes', 'Árboles', 'a', 1),
(NULL, @activity_id, '¿Qué modelo usa fronteras de decisión?', 'SVM', 'KNN', 'Naive Bayes', 'Random Forest', 'a', 2),
(NULL, @activity_id, '¿Qué modelo usa probabilidades?', 'Naive Bayes', 'KNN', 'SVM', 'Árboles', 'a', 3),
(NULL, @activity_id, '¿Qué modelo toma decisiones por ramas?', 'Árboles de decisión', 'KNN', 'SVM', 'Naive Bayes', 'a', 4),
(NULL, @activity_id, '¿Qué riesgo tienen los árboles profundos?', 'Sobreentrenamiento', 'Subentrenamiento', 'No convergen', 'No clasifican', 'a', 5);

-- ============================================================
-- UNIDAD 9
-- ============================================================

INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 9 — Modelos no supervisados, clustering y balanceo de datos',
  'En este módulo el estudiante aprenderá qué ocurre cuando no existe una etiqueta definida en los datos. Se estudiarán los modelos no supervisados, especialmente el clustering, como una forma de encontrar grupos o patrones ocultos. También se abordará el balanceo de datos, incluyendo conceptos como under sampling, over sampling, datos sintéticos, SMOTE y muestreo estratificado. El objetivo es que el estudiante comprenda que no todos los problemas consisten en predecir una respuesta conocida; algunos buscan descubrir estructuras dentro de la información.',
  'hw',
  9,
  '#FEE3FF'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'doc', 'Clustering en scikit-learn', 'https://scikit-learn.org/stable/modules/clustering.html', 'Documento web de consulta'),
(@unit_id, 'doc', 'KMeans en scikit-learn', 'https://scikit-learn.org/stable/modules/generated/sklearn.cluster.KMeans.html', 'Documento técnico de referencia');

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 9', 'Evaluación del módulo 9: Modelos no supervisados y balanceo');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions VALUES
(NULL, @activity_id, '¿Qué modelo agrupa datos sin etiqueta?', 'Clustering', 'Regresión', 'Clasificación', 'Árboles', 'a', 1),
(NULL, @activity_id, '¿Qué técnica crea datos sintéticos?', 'SMOTE', 'MinMax', 'KNN', 'SVM', 'a', 2),
(NULL, @activity_id, '¿Qué es under sampling?', 'Reducir datos mayoritarios', 'Aumentar datos minoritarios', 'Eliminar outliers', 'Duplicar registros', 'a', 3),
(NULL, @activity_id, '¿Qué es over sampling?', 'Aumentar datos minoritarios', 'Reducir datos mayoritarios', 'Eliminar columnas', 'Escalar datos', 'a', 4),
(NULL, @activity_id, '¿Qué modelo es no supervisado?', 'KMeans', 'SVM', 'KNN', 'Árboles', 'a', 5);

-- ============================================================
-- UNIDAD 10
-- ============================================================

INSERT INTO units (course_id, title, description, icon_class, order_index, card_color)
VALUES (
  @course_id,
  'Módulo 10 — Evaluación, modelos ensamblados y despliegue con Streamlit',
  'En este último módulo el estudiante integrará lo aprendido para evaluar y presentar modelos de inteligencia artificial. Se revisarán métricas como accuracy, precisión, recall, matriz de confusión, curva ROC y área bajo la curva. También se estudiarán modelos ensamblados como Random Forest y XGBoost, entendiendo cómo combinan varios modelos para mejorar resultados. Finalmente, el estudiante aprenderá a guardar modelos con joblib y construir una aplicación sencilla en Streamlit para que otras personas puedan ingresar datos y obtener una predicción de manera visual e interactiva.',
  'hw',
  10,
  '#FEE3FF'
);

SET @unit_id := LAST_INSERT_ID();

INSERT INTO resources (unit_id, type, name, file_path, meta) VALUES
(@unit_id, 'doc', 'Métricas y evaluación de modelos en scikit-learn', 'https://scikit-learn.org/stable/modules/model_evaluation.html', 'Documento web de consulta'),
(@unit_id, 'doc', 'Despliegue de aplicaciones en Streamlit Community Cloud', 'https://docs.streamlit.io/deploy/streamlit-community-cloud', 'Guía web paso a paso');

INSERT INTO activities (unit_id, activity_type, title, description)
VALUES (@unit_id, 'quiz', 'Examen del Módulo 10', 'Evaluación del módulo 10: Métricas, ensambles y despliegue');

SET @activity_id := LAST_INSERT_ID();

INSERT INTO quiz_questions VALUES
(NULL, @activity_id, '¿Qué métrica mide precisión global?', 'Accuracy', 'Recall', 'AUC', 'RMSE', 'a', 1),
(NULL, @activity_id, '¿Qué modelo es un ensamble?', 'Random Forest', 'KNN', 'SVM', 'Naive Bayes', 'a', 2),
(NULL, @activity_id, '¿Qué archivo guarda modelos en Python?', 'joblib', 'pickle', 'json', 'csv', 'a', 3),
(NULL, @activity_id, '¿Qué curva evalúa clasificación?', 'ROC', 'Histograma', 'Boxplot', 'Scatter', 'a', 4),
(NULL, @activity_id, '¿Qué herramienta permite desplegar apps?', 'Streamlit', 'Excel', 'PowerPoint', 'Photoshop', 'a', 5);

-- ============================================================
-- FOROS DEL CURSO DE IA
-- ============================================================

INSERT INTO forums (course_id, title, description, created_by) VALUES
(@course_id, 'Foro General de Inteligencia Artificial', 'Foro general para presentaciones, avisos, dudas y preguntas abiertas del curso de Inteligencia Artificial aplicada al análisis de datos.', @teacher_id),
(@course_id, 'Consultas sobre datos y modelos', 'Espacio para resolver dudas relacionadas con análisis exploratorio, limpieza de datos, codificación, selección de características, entrenamiento y evaluación de modelos.', @teacher_id),
(@course_id, 'Soporte técnico de Python, Colab y Streamlit', 'Foro para reportar problemas técnicos relacionados con Python, pandas, scikit-learn, Google Colab, Streamlit o ejecución de notebooks.', @teacher_id);