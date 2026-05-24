# Forum Chat Feature - Documentación

## Descripción General

Se ha implementado un sistema completo de foros tipo chat con tres niveles de navegación:
1. **Foros** - Contenedores principales de discusiones
2. **Temas** - Discusiones específicas dentro de un foro
3. **Mensajes** - Chat individual dentro de cada tema

## Características Principales

### 1. Interfaz de Usuario
- **Icono en TopBar**: Icono de chat (🗨️) en la barra superior que abre/cierra el drawer de foros
- **Drawer Lateral**: Panel deslizable desde la **izquierda** con animación suave (cubic-bezier)
- **Navegación Jerárquica**: Estructura Foro → Tema → Mensajes
- **Estado Activo**: Los temas activos se resaltan en azul para fácil identificación

### 2. Permisos por Rol
- **Estudiante**: Ver foros, crear temas, enviar mensajes
- **Profesor**: Crear foros, crear/eliminar temas, enviar mensajes, eliminar foros
- **Administrador**: Acceso completo a foros y temas

### 3. Base de Datos

#### Tablas Nuevas/Modificadas:
- `forum_topics` - Almacena temas/discusiones
  - id, forum_id, title, description, created_by, created_at, updated_at
- `forum_messages` - Modificada para soportar topics
  - Ahora incluye `topic_id` para asociar mensajes con temas

### 4. APIs REST

#### `/api/topics.php`
- **GET** `?forum_id={id}` - Obtener temas de un foro
- **POST** - Crear nuevo tema
  ```json
  { "forum_id": 1, "title": "Tema", "description": "Descripción" }
  ```
- **DELETE** `?topic_id={id}` - Eliminar tema (solo creador/admin)

#### `/api/messages.php` (Actualizada)
- **GET** `?topic_id={id}` - Obtener mensajes de un tema
- **GET** `?forum_id={id}` - Obtener mensajes generales (sin tema)
- **POST** - Crear mensaje
  ```json
  { "forum_id": 1, "topic_id": 1, "content": "Mensaje" }
  ```

### 5. Componentes Frontend

#### CSS (`css/estilos.css`)
- `.drawer` - Anima desde `-410px` (izquierda) a `0`
- `.topics-list` - Contenedor de temas
- `.topic-item` - Estilo individual de tema con indicador activo
- `.add-topic-btn` - Botón para crear nuevo tema
- `.messages-container` - Chat con scroll personalizado

#### JavaScript (`js/app.js`)
- `toggleForum(btn)` - Alterna foro y carga temas
- `selectTopic(el, topicId, forumId)` - Selecciona tema y carga mensajes
- `loadTopics(forumId)` - Obtiene temas de un foro
- `loadTopicMessages(topicId, container)` - Obtiene mensajes de un tema
- `sendMsg(btn)` - Envía mensaje al tema actual
- `showNewForumTopicModal()` - Abre modal para crear tema
- `createForumTopic()` - Crea nuevo tema

#### Páginas Actualizadas
- `estudiante.php` - Nuevo modal para crear tema
- `profesor.php` - Nuevo modal para crear tema
- `admin.php` - Nuevo modal para crear tema

### 6. Flujo de Usuario

#### Crear y Participar en un Foro

1. **Abrir Foros**: Click en icono 🗨️ en topbar
2. **Seleccionar Foro**: Click en foro para expandir y ver temas
3. **Crear Tema**: Click en "Nuevo Tema"
   - Ingresa título y descripción (opcional)
   - Confirma para crear
4. **Participar en Tema**: 
   - Selecciona tema para ver mensajes
   - Escribe mensaje en textarea
   - Click en botón enviar (✈️)
5. **Ver Conversación**: Los mensajes aparecen con usuario y contenido

### 7. Configuración de Seguridad

- **Validación de Acceso**: Verifica que el usuario tenga acceso al curso
- **Permisos por Rol**: Limita acciones según el rol del usuario
- **Sanitización HTML**: Escapa contenido HTML para evitar XSS

### 8. Archivos Modificados/Creados

```
├── database/schema.sql                 (✓ Actualizado)
├── public/
│   ├── api/
│   │   ├── topics.php                 (✓ Nuevo)
│   │   ├── messages.php               (✓ Actualizado)
│   │   └── forums.php                 (sin cambios)
│   ├── js/
│   │   └── app.js                     (✓ Actualizado)
│   ├── css/
│   │   └── estilos.css                (✓ Actualizado)
│   ├── estudiante.php                 (✓ Actualizado)
│   ├── profesor.php                   (✓ Actualizado)
│   ├── admin.php                      (✓ Actualizado)
│   └── test_forum_feature.php         (✓ Nuevo - Script de prueba)
```

### 9. Características Técnicas

- **Drawer Lateral Animado**: Usa `cubic-bezier(0.25, 0.46, 0.45, 0.94)` para transición suave
- **Scrollbars Personalizados**: webkit-scrollbar con estilo acorde al tema
- **Responsive**: Funciona en desktop y mobile
- **Accesibilidad**: Estructura HTML semántica, contraste adecuado

### 10. Próximas Mejoras (Opcionales)

- [ ] Notificaciones en tiempo real
- [ ] Indicador de usuarios conectados
- [ ] Búsqueda en foros y temas
- [ ] Reacciones con emojis
- [ ] Editar/Eliminar mensajes
- [ ] Soporte para archivos adjuntos
- [ ] Citas en mensajes

## Testing

Ejecutar `test_forum_feature.php` en el navegador para verificar:
- Existencia de tablas de BD
- Estructura de columnas
- Archivos de API
- Componentes frontend

## Contacto/Soporte

Para problemas o mejoras, verificar:
1. Logs en browser console (F12)
2. Respuestas de API en Network tab
3. Estado de autenticación en sesión
