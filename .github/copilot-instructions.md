# Instrucciones rápidas para agentes AI (Copilot)

Este repositorio es una pequeña aplicación PHP para gestión de solicitudes de becas. Aquí hay notas prácticas y patrones que ayudan a comprender y modificar el código sin suposiciones.

**Arquitectura general:**
- **Front-end y rutas:** Páginas PHP en `index.php` y `views/` actúan como vistas server-rendered. Ver [views/login.php](views/login.php) y [views/dashboard.php](views/dashboard.php).
- **Lógica del servidor:** Controladores ligeros en `controllers/` (por ejemplo, [controllers/login_process.php](controllers/login_process.php), [controllers/register_process.php](controllers/register_process.php)).
- **Panel administrador:** Archivos en `admin/` (login, panel, revisión de documentos).
- **BD y conexión:** Conexión MySQL en [config/conexion.php](config/conexion.php). Variable principal usada: `$conexion` y alias `$conn`.

**Flujos y decisiones importantes:**
- Login auto-detecta tipo de usuario por formato del `user`: si es email -> estudiante; si no -> admin. (ver [controllers/login_process.php](controllers/login_process.php)).
- Sesiones: `session_start()` se usa en controladores y vistas que requieren auth. Claves de sesión: `usuario_id`, `usuario_nombre` para estudiantes; `admin_id`, `admin_usuario` para administradores.
- Subida de archivos: `views/subir_documentos.php` gestiona validación, tipos permitidos y mapea tipos a subcarpetas dentro de `assets/uploads/` (ver array `$folders`). Usa rutas absolutas para mover archivos y guarda la ruta relativa en BD.
- Tipos de documento: la tabla `tipo_documento` puede poblarse al vuelo en `subir_documentos.php` si está vacía (valores por defecto definidos ahí).

**Convenciones de codificación del proyecto:**
- Uso consistente de `include`/`include_once` para cargar `config/conexion.php` y obtener `$conn`.
- Se prefieren `prepared statements` para INSERT/SELECT/UPDATE (ver `register_process.php`, `login_process.php`, `subir_documentos.php`). No obstante, hay consultas con interpolación directa en algunas vistas (ej. `dashboard.php` usa `$id_estudiante` interpolado en SQL); ten cuidado y conviértelas a prepared statements al modificar.
- Manejo de errores: la app frecuentemente redirige con parámetros en la URL (`?error=...`) en lugar de excepciones. Respeta ese patrón cuando agregues validaciones.

**Puntos de atención (security / mantenimiento):**
- Revisa lugares donde se usa interpolación SQL (p.ej. `dashboard.php`) y sanitiza si introduces cambios.
- Control de acceso: verifica `isset($_SESSION['...'])` al añadir nuevas páginas protegidas.
- Permisos de archivos: uploads se escriben en `assets/uploads/*`. Asegúrate de mantener las rutas y permisos coherentes en entornos de despliegue.

**Cómo ejecutar / pruebas manuales rápidas:**
- Entorno esperado: servidor PHP + MySQL (base `sistema_becas`). Archivo de conexión en [config/conexion.php](config/conexion.php).
- Para pruebas locales rápidas puedes usar el server incorporado de PHP desde la raíz del proyecto:

  php -S localhost:8000

  Luego abrir `http://localhost:8000/`.

**Archivos clave para cambios comunes:**
- Conexión DB: [config/conexion.php](config/conexion.php)
- Registro/Login: [controllers/register_process.php](controllers/register_process.php), [controllers/login_process.php](controllers/login_process.php)
- Subidas y tipos: [views/subir_documentos.php](views/subir_documentos.php)
- Panel admin y revisión: [admin/revisar_documentos.php](admin/revisar_documentos.php), [admin/actualizar_estado.php](admin/actualizar_estado.php)

Si algo no está documentado aquí o quieres que añada ejemplos de refactor (p.ej. convertir queries interpoladas a prepared statements), dime qué sección prefieres y lo completo.
