<?php
// Logout unificado: limpia sesión de admin y/o estudiante y redirige a la página principal
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Eliminar claves de usuario en sesión
unset($_SESSION['admin_id'], $_SESSION['admin_usuario'], $_SESSION['usuario_id'], $_SESSION['usuario_nombre']);

// Opcional: limpiar toda la sesión y destruirla
session_unset();
session_destroy();

// Regenerar id de sesión para mitigar fijación (después de destruir será una nueva sesión si se inicia de nuevo)
// session_regenerate_id(true);

// Redirigir a la página principal
header('Location: index.php');
exit;
?>